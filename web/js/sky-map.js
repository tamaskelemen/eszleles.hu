(function skyMap($, d3) {
    const root = document.getElementById('skymap');

    if (!root) {
        return;
    }

    const canvas = root.querySelector('canvas');
    const context = canvas.getContext('2d');
    const stage = root.querySelector('.skymap__stage');
    const tooltip = root.querySelector('.skymap__tooltip');
    const panel = root.querySelector('.skymap__panel');
    const message = root.querySelector('.skymap__message');

    const constellationNames = JSON.parse(root.dataset.constellations);
    const gaps = JSON.parse(root.dataset.gaps);
    const horizon = Number(root.dataset.horizon);
    // the map of a profile, there are no observers to compare and no lists under the map
    const isSingleObserver = 'singleObserver' in root.dataset;

    const MAX_ZOOM = 24;
    // the edge of the map runs between Virgo and Hercules, where the fewest objects are cut apart
    const HOME = {ra: 40, dec: 0, zoom: 1};
    const HEAT_RADIUS = 4; // degrees
    const HEAT_RADIUS_LIMIT = 36; // pixels
    const LABEL_ZOOM = 5;
    const URL_PARAM = 'objektum';
    const HIT_RADIUS = {mouse: 10, touch: 22};
    const DEGREE = Math.PI / 180;
    const FONT = 'Inter, Helvetica, Arial, sans-serif';

    // one hue from dark to light, the surface of the map is dark
    const ramp = d3.piecewise(d3.interpolateRgb, ['#5c2013', '#a83a24', '#ff6a4d', '#ffab96', '#ffe9e2']);
    const heatColors = buildHeatColors();

    const layers = {constellations: true, milkyWay: true, grid: false, gaps: false};
    const view = Object.assign({}, HOME);
    const heatCanvas = document.createElement('canvas');
    const heatContext = heatCanvas.getContext('2d', {willReadFrequently: true});
    const projection = d3.geoProjection(mollweide()).reflectX(true).precision(.5);
    const path = d3.geoPath(projection, context);

    let metric = 'observations';
    let background = {stars: [], lines: {}, constellations: [], milkyWay: []};
    let objects = [];
    let maxValue = 1;
    let size = {width: 0, height: 0};
    let selected = null;
    let hovered = null;
    let frame = null;
    let animation = null;
    let lastTransform = d3.zoomIdentity;
    let lastPanelRequest = 0;

    /* ---------- projection ---------- */

    function mollweide() {
        function forward(lambda, phi) {
            let theta = phi;

            if (Math.abs(phi) < Math.PI / 2 - 1e-6) {
                for (let i = 0; i < 12; i++) {
                    const delta = (2 * theta + Math.sin(2 * theta) - Math.PI * Math.sin(phi)) / (2 + 2 * Math.cos(2 * theta));
                    theta -= delta;
                    if (Math.abs(delta) < 1e-7) {
                        break;
                    }
                }
            }

            return [2 * Math.SQRT2 / Math.PI * lambda * Math.cos(theta), Math.SQRT2 * Math.sin(theta)];
        }

        forward.invert = function (x, y) {
            const theta = Math.asin(Math.max(-1, Math.min(1, y / Math.SQRT2)));

            return [
                Math.PI * x / (2 * Math.SQRT2 * Math.cos(theta)),
                Math.asin(Math.max(-1, Math.min(1, (2 * theta + Math.sin(2 * theta)) / Math.PI))),
            ];
        };

        return forward;
    }

    function baseScale() {
        // the oval is 4 * sqrt(2) wide and half as high
        return Math.min((size.width - 16) / (4 * Math.SQRT2), (size.height - 16) / (2 * Math.SQRT2));
    }

    function applyView() {
        view.zoom = Math.max(1, Math.min(MAX_ZOOM, view.zoom));

        // the whole sky is always shown upright, the poles can be reached by zooming in
        const maxDec = Math.min(90, 120 * (1 - 1 / view.zoom));
        view.dec = Math.max(-maxDec, Math.min(maxDec, view.dec));
        view.ra = ((view.ra % 360) + 360) % 360;

        projection
            .scale(baseScale() * view.zoom)
            .translate([size.width / 2, size.height / 2])
            .rotate([-view.ra, -view.dec]);
    }

    function pixelsPerDegree() {
        return projection.scale() * DEGREE;
    }

    /** @return {number[]|null} null outside of the sky */
    function invert(point) {
        const coordinates = projection.invert(point);
        const back = coordinates && projection(coordinates);

        return back && Math.hypot(back[0] - point[0], back[1] - point[1]) < 1 ? coordinates : null;
    }

    function projectVisible(ra, dec) {
        const point = projection([ra, dec]);

        return point[0] > -20 && point[0] < size.width + 20 && point[1] > -20 && point[1] < size.height + 20 ? point : null;
    }

    /* ---------- drawing ---------- */

    function value(object) {
        return object[metric];
    }

    /** 0-1, logarithmic, otherwise a few popular objects would hide everything else */
    function intensity(object) {
        return maxValue > 1 ? Math.log(value(object)) / Math.log(maxValue) : 0;
    }

    function buildHeatColors() {
        const colors = new Uint8ClampedArray(256 * 4);

        for (let i = 1; i < 256; i++) {
            const color = d3.rgb(ramp(i / 255));
            colors.set([color.r, color.g, color.b, Math.min(1, i / 70) * 215], i * 4);
        }

        return colors;
    }

    function redraw() {
        if (frame === null) {
            frame = requestAnimationFrame(function () {
                frame = null;
                draw();
            });
        }
    }

    function draw() {
        const ratio = window.devicePixelRatio || 1;

        applyView();
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, size.width, size.height);

        context.save();
        context.beginPath();
        path({type: 'Sphere'});
        context.fillStyle = '#060a0f';
        context.fill();
        context.clip();

        if (layers.milkyWay) {
            drawMilkyWay();
        }
        drawGrid();
        drawHorizon();
        drawHeat();
        if (layers.constellations) {
            drawConstellationLines();
        }
        drawStars();
        if (layers.gaps) {
            drawGaps();
        }
        drawObjects();
        context.restore();

        context.beginPath();
        path({type: 'Sphere'});
        context.strokeStyle = '#4a5b61';
        context.lineWidth = 1;
        context.stroke();

        const taken = [];

        if (layers.constellations) {
            drawConstellationNames(taken);
        }
        drawObjectLabels(taken);
    }

    function drawMilkyWay() {
        context.fillStyle = 'rgba(150, 178, 200, .075)';
        background.milkyWay.forEach(function (level) {
            context.beginPath();
            level.forEach(shape => path(shape));
            context.fill('evenodd');
        });
    }

    function drawGrid() {
        const step = view.zoom > 6 ? [5, 5] : [30, 30];

        context.beginPath();
        path(d3.geoGraticule().step(step).extentMajor([[-180, -90], [180, 90]])());
        context.strokeStyle = layers.grid ? 'rgba(130, 155, 165, .3)' : 'rgba(130, 155, 165, .1)';
        context.lineWidth = 1;
        context.stroke();

        if (!layers.grid) {
            return;
        }

        context.font = '11px ' + FONT;
        context.fillStyle = '#9aa9ac';
        context.textAlign = 'center';
        context.textBaseline = 'middle';

        const ra = Math.round(view.ra / 30) * 30;
        const dec = view.zoom > 2 ? Math.round(view.dec / step[1]) * step[1] : 0;

        // they would be on each other at the poles
        for (let hour = 0; hour < 24 && Math.abs(dec) < 70; hour += 2) {
            text(hour + 'h', hour * 15, dec);
        }
        for (let degree = -60; degree <= 60; degree += 30) {
            // there is the label of the horizon
            if (degree !== dec && (degree !== -60 || view.zoom > 2)) {
                text((degree > 0 ? '+' : '') + String(degree).replace('-', '−') + '°', ra, degree);
            }
        }
    }

    function text(label, ra, dec) {
        const point = projectVisible(ra, dec);

        if (point) {
            context.fillText(label, point[0], point[1]);
        }
    }

    function drawHorizon() {
        // what never rises in Hungary
        context.beginPath();
        path(d3.geoCircle().center([0, -90]).radius(90 + horizon).precision(2)());
        context.fillStyle = 'rgba(154, 169, 172, .1)';
        context.fill();
        context.setLineDash([4, 4]);
        context.strokeStyle = 'rgba(154, 169, 172, .55)';
        context.lineWidth = 1;
        context.stroke();
        context.setLineDash([]);

        if (size.width > 600) {
            context.font = '11px ' + FONT;
            context.fillStyle = '#9aa9ac';
            context.textAlign = 'center';
            context.textBaseline = 'middle';
            text('Magyarországról nem látszik', view.ra, horizon - 16 / Math.sqrt(view.zoom));
        }
    }

    function drawHeat() {
        // close up the dots tell more
        const alpha = Math.max(0, Math.min(1, 1.15 - view.zoom / 14));
        const scale = .5;
        const width = Math.ceil(size.width * scale);
        const height = Math.ceil(size.height * scale);
        const radius = Math.max(5, Math.min(HEAT_RADIUS_LIMIT, HEAT_RADIUS * pixelsPerDegree())) * scale;

        if (alpha === 0 || width === 0 || height === 0) {
            return;
        }

        heatCanvas.width = width;
        heatCanvas.height = height;

        objects.forEach(function (object) {
            const point = projection([object.ra, object.dec]);
            const x = point[0] * scale;
            const y = point[1] * scale;

            if (x < -radius || y < -radius || x > width + radius || y > height + radius) {
                return;
            }

            const strength = .16 + .5 * intensity(object);
            const gradient = heatContext.createRadialGradient(x, y, 0, x, y, radius);
            gradient.addColorStop(0, `rgba(0, 0, 0, ${strength})`);
            gradient.addColorStop(.5, `rgba(0, 0, 0, ${strength * .4})`);
            gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
            heatContext.fillStyle = gradient;
            heatContext.fillRect(x - radius, y - radius, radius * 2, radius * 2);
        });

        const image = heatContext.getImageData(0, 0, width, height);
        const data = image.data;

        for (let i = 0; i < data.length; i += 4) {
            const color = data[i + 3] * 4;

            if (color > 0) {
                data[i] = heatColors[color];
                data[i + 1] = heatColors[color + 1];
                data[i + 2] = heatColors[color + 2];
                data[i + 3] = heatColors[color + 3];
            }
        }

        heatContext.putImageData(image, 0, 0);
        context.globalAlpha = alpha;
        context.drawImage(heatCanvas, 0, 0, size.width, size.height);
        context.globalAlpha = 1;
    }

    function drawConstellationLines() {
        context.beginPath();
        path(background.lines);
        context.strokeStyle = 'rgba(140, 170, 185, .38)';
        context.lineWidth = 1;
        context.stroke();
    }

    function drawStars() {
        const limit = 4.2 + Math.log2(view.zoom) * .9;
        const scale = Math.min(1.8, .75 + size.width / 3000 + view.zoom / 14) * (size.width < 600 ? .6 : 1);

        context.fillStyle = '#dfe8ee';

        for (const [ra, dec, magnitude] of background.stars) {
            // they are in order
            if (magnitude > limit) {
                break;
            }

            const point = projectVisible(ra, dec);

            if (point) {
                context.globalAlpha = Math.max(.3, Math.min(1, 1 - (magnitude - 1) / 7));
                context.beginPath();
                context.arc(point[0], point[1], Math.max(.45, (4.6 - magnitude * .72) * .5 * scale), 0, 2 * Math.PI);
                context.fill();
            }
        }

        context.globalAlpha = 1;
    }

    function drawGaps() {
        context.strokeStyle = '#dfe6e6';
        context.lineWidth = 1.25;
        context.font = '600 13px ' + FONT;
        context.fillStyle = '#fff';
        context.textAlign = 'center';
        context.textBaseline = 'middle';

        gaps.forEach(function (gap, index) {
            context.beginPath();
            path(d3.geoCircle().center([gap.ra, gap.dec]).radius(gap.radius).precision(3)());
            context.setLineDash([5, 4]);
            context.stroke();
            context.setLineDash([]);
            text(index + 1, gap.ra, gap.dec);
        });
    }

    function radiusOf(object) {
        return (1.1 + 1.9 * intensity(object)) * Math.min(3, .8 + view.zoom / 5) * (size.width < 600 ? .7 : 1);
    }

    function drawObjects() {
        // the most observed is the first, it has to be on the top
        for (let i = objects.length - 1; i >= 0; i--) {
            const object = objects[i];
            const point = projectVisible(object.ra, object.dec);

            object.point = point;

            if (!point) {
                continue;
            }

            const radius = radiusOf(object);

            context.beginPath();
            context.arc(point[0], point[1], radius, 0, 2 * Math.PI);
            context.fillStyle = ramp(.5 + .5 * intensity(object));
            context.fill();

            if (view.zoom > 2) {
                context.strokeStyle = '#060a0f';
                context.lineWidth = 1;
                context.stroke();
            }
        }

        [selected, hovered].forEach(function (object) {
            if (object && object.point) {
                context.beginPath();
                context.arc(object.point[0], object.point[1], radiusOf(object) + 4, 0, 2 * Math.PI);
                context.strokeStyle = '#fff';
                context.lineWidth = 2;
                context.stroke();
            }
        });
    }

    function drawConstellationNames(taken) {
        const maxRank = view.zoom < 1.6 ? (size.width < 700 ? 0 : 1) : view.zoom < 3 ? 2 : 3;

        context.font = (view.zoom > 2 ? 13 : 11) + 'px ' + FONT;
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.lineWidth = 3;
        context.strokeStyle = 'rgba(6, 10, 15, .75)';
        context.fillStyle = 'rgba(190, 208, 216, .85)';

        background.constellations.forEach(function ([id, ra, dec, rank]) {
            const point = rank <= maxRank && projectVisible(ra, dec);

            if (point) {
                context.strokeText(constellationNames[id], point[0], point[1]);
                context.fillText(constellationNames[id], point[0], point[1]);

                const half = context.measureText(constellationNames[id]).width / 2;
                taken.push([point[0] - half, point[1] - 8, point[0] + half, point[1] + 8]);
            }
        });
    }

    function shortLabel(object) {
        return object.label.split(/ – | \(/)[0];
    }

    function drawObjectLabels(taken) {
        if (view.zoom < LABEL_ZOOM) {
            return;
        }

        context.font = '12px ' + FONT;
        context.textAlign = 'left';
        context.textBaseline = 'middle';
        context.lineWidth = 3;
        context.strokeStyle = 'rgba(6, 10, 15, .85)';
        context.fillStyle = '#fff';

        objects.forEach(function (object) {
            if (!object.point) {
                return;
            }

            const label = shortLabel(object);
            const x = object.point[0] + radiusOf(object) + 4;
            const y = object.point[1];
            const box = [x, y - 8, x + context.measureText(label).width + 4, y + 8];

            if (taken.some(other => box[0] < other[2] && box[2] > other[0] && box[1] < other[3] && box[3] > other[1])) {
                return;
            }

            taken.push(box);
            context.strokeText(label, x, y);
            context.fillText(label, x, y);
        });
    }

    /* ---------- moving ---------- */

    const zoom = d3.zoom()
        .scaleExtent([1, MAX_ZOOM])
        .filter(function (event) {
            if (event.type === 'wheel') {
                // the page has to be scrollable over the map
                return event.ctrlKey || event.metaKey || document.activeElement === canvas || isFullscreen();
            }

            if (event.touches) {
                return event.touches.length > 1 || isFullscreen();
            }

            return !event.button;
        })
        .on('start', stopAnimation)
        .on('zoom', function (event) {
            const transform = event.transform;

            if (!event.sourceEvent) {
                return;
            }

            if (transform.k === lastTransform.k) {
                const degrees = 1 / pixelsPerDegree();
                view.ra += (transform.x - lastTransform.x) * degrees / .9 / Math.max(.2, Math.cos(view.dec * DEGREE));
                view.dec += (transform.y - lastTransform.y) * degrees / 1.11;
            } else {
                zoomAround(pointerOf(event.sourceEvent), transform);
            }

            lastTransform = transform;
            hideTooltip();
            redraw();
        });

    function pointerOf(event) {
        if (event.touches && event.touches.length) {
            const points = d3.pointers(event, canvas);
            return [d3.mean(points, point => point[0]), d3.mean(points, point => point[1])];
        }

        return d3.pointer(event, canvas);
    }

    /** Keeps the same part of the sky under the pointer. */
    function zoomAround(pointer, transform) {
        const before = invert(lastTransform.apply(transform.invert(pointer)));

        view.zoom = transform.k;
        applyView();

        // the projection is not linear, a step only gets closer
        for (let i = 0; i < 5; i++) {
            const after = invert(pointer);

            if (!before || !after) {
                return;
            }

            view.ra += ((before[0] - after[0] + 540) % 360) - 180;
            view.dec += before[1] - after[1];
            applyView();
        }
    }

    function stopAnimation() {
        if (animation) {
            animation.stop();
            animation = null;
        }
    }

    function moveTo(target) {
        const from = Object.assign({}, view);
        const to = Object.assign({}, view, target);
        const duration = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 700;
        // the shorter way around
        const turn = ((to.ra - from.ra + 540) % 360 + 360) % 360 - 180;

        stopAnimation();
        // the gestures continue from here
        lastTransform = d3.zoomIdentity.scale(Math.max(1, Math.min(MAX_ZOOM, to.zoom)));
        canvas.__zoom = lastTransform;

        animation = d3.timer(function (elapsed) {
            const t = duration ? d3.easeCubicInOut(Math.min(1, elapsed / duration)) : 1;

            view.ra = from.ra + turn * t;
            view.dec = from.dec + (to.dec - from.dec) * t;
            view.zoom = from.zoom * Math.pow(to.zoom / from.zoom, t);
            redraw();

            if (t === 1) {
                stopAnimation();
            }
        });
    }

    function zoomBy(factor) {
        moveTo({zoom: Math.max(1, Math.min(MAX_ZOOM, view.zoom * factor))});
    }

    function isFullscreen() {
        return root.classList.contains('skymap--fullscreen');
    }

    function toggleFullscreen(isOn) {
        const button = root.querySelector('[data-action="fullscreen"]');

        root.classList.toggle('skymap--fullscreen', isOn);
        document.body.classList.toggle('skymap-is-fullscreen', isOn);
        button.setAttribute('aria-pressed', isOn);
        button.title = isOn ? 'Kilépés a teljes képernyőből' : 'Teljes képernyő';
        button.setAttribute('aria-label', button.title);
        resize();
    }

    const actions = {
        in: () => zoomBy(2),
        out: () => zoomBy(.5),
        reset: () => moveTo(HOME),
        fullscreen: () => toggleFullscreen(!isFullscreen()),
    };

    const keys = {
        '+': actions.in,
        '-': actions.out,
        '0': actions.reset,
        ArrowLeft: () => moveTo({ra: view.ra + 30 / view.zoom}),
        ArrowRight: () => moveTo({ra: view.ra - 30 / view.zoom}),
        ArrowUp: () => moveTo({dec: view.dec + 20 / view.zoom}),
        ArrowDown: () => moveTo({dec: view.dec - 20 / view.zoom}),
    };

    /* ---------- objects ---------- */

    function findAt(point, radius) {
        let nearest = null;
        let distance = radius;

        objects.forEach(function (object) {
            const d = object.point ? Math.hypot(object.point[0] - point[0], object.point[1] - point[1]) : Infinity;

            // the first is the most observed
            if (d < distance) {
                nearest = object;
                distance = d;
            }
        });

        return nearest;
    }

    function counts(object) {
        return isSingleObserver ? `${object.observations} észlelés` : `${object.observations} észlelés, ${object.observers} észlelő`;
    }

    function description(object) {
        return `${object.type}, ${constellationNames[object.constellation]}`;
    }

    function hideTooltip() {
        tooltip.hidden = true;
        if (hovered) {
            hovered = null;
            redraw();
        }
    }

    function showTooltip(object, point) {
        if (object !== hovered) {
            hovered = object;
            redraw();
        }

        $(tooltip).empty().append($('<b>').text(object.label), $('<span>').text(description(object)), $('<span>').text(counts(object)));
        tooltip.hidden = false;

        const x = Math.min(point[0] + 14, size.width - tooltip.offsetWidth - 4);
        const y = point[1] + 16 + tooltip.offsetHeight > size.height ? point[1] - tooltip.offsetHeight - 10 : point[1] + 16;
        tooltip.style.transform = `translate(${Math.max(4, x)}px, ${Math.max(4, y)}px)`;
    }

    /** The address always points to the selected object, so it can be shared. */
    function updateUrl() {
        const url = new URL(window.location);

        if (selected) {
            url.searchParams.set(URL_PARAM, selected.id);
        } else {
            url.searchParams.delete(URL_PARAM);
        }

        if (url.href !== window.location.href) {
            window.history.replaceState(window.history.state, '', url);
        }
    }

    function openFromUrl() {
        const id = Number(new URL(window.location).searchParams.get(URL_PARAM));
        const object = objects.find(item => item.id === id);

        if (object) {
            focusObject(object);
        }
    }

    function closePanel() {
        lastPanelRequest++;
        selected = null;
        updateUrl();
        panel.hidden = true;
        redraw();
    }

    function openPanel(object) {
        const request = ++lastPanelRequest;
        const list = $('<ul class="skymap__observations">').append('<li>Betöltés…</li>');

        selected = object;
        updateUrl();
        hideTooltip();
        redraw();

        $(panel).empty().append(
            $('<button type="button" class="skymap__close" aria-label="Bezárás">&times;</button>').on('click', closePanel),
            $('<h3>').text(object.label),
            $('<p>').text(description(object)),
            $('<p>').text(counts(object)),
            list
        );
        panel.hidden = false;
        panel.scrollTop = 0;

        $.getJSON(root.dataset.objectUrl.replace(/0(?=\?|$)/, object.id))
            .done(function (observations) {
                if (request !== lastPanelRequest) {
                    return;
                }

                list.empty();
                observations.forEach(function (observation) {
                    $('<li>').append(
                        $('<a>').attr('href', observation.url).append(
                            $('<img alt="" width="72" height="48" loading="lazy">').attr('src', observation.image),
                            $('<span>').append($('<b>').text(observation.title), $('<small>').text(`${observation.observer}, ${observation.date}`))
                        )
                    ).appendTo(list);
                });
            })
            .fail(function () {
                if (request === lastPanelRequest) {
                    list.empty().append('<li>Az észleléseket nem sikerült betölteni.</li>');
                }
            });
    }

    function focusObject(object) {
        root.scrollIntoView({behavior: 'smooth', block: 'start'});
        moveTo({ra: object.ra, dec: object.dec, zoom: Math.max(view.zoom, 6)});
        openPanel(object);
    }

    /* ---------- lists ---------- */

    function sorted() {
        return objects.slice().sort((a, b) => value(b) - value(a) || b.observations - a.observations);
    }

    function objectButton(object) {
        return $('<button type="button" class="skymap-link">').text(object.label).on('click', () => focusObject(object));
    }

    function renderTop() {
        const list = $('.skymap-top').empty();

        sorted().slice(0, 10).forEach(function (object) {
            $('<li>').append(
                objectButton(object),
                $('<span class="skymap-top__value">').text(value(object)),
                $('<span class="skymap-top__bar" aria-hidden="true">').append($('<span>').css('width', 100 * value(object) / maxValue + '%'))
            ).appendTo(list);
        });

        if (objects.length === 0) {
            list.append('<li>Még nincs azonosított objektum.</li>');
        }
    }

    function renderTable() {
        const details = document.querySelector('.skymap-table');

        if (!details.open) {
            return;
        }

        const body = $('<tbody>');

        sorted().forEach(function (object) {
            $('<tr>').append(
                $('<td>').append(objectButton(object)),
                $('<td>').text(object.type),
                $('<td>').text(constellationNames[object.constellation]),
                $('<td class="text-right">').text(object.observations),
                $('<td class="text-right">').text(object.observers)
            ).appendTo(body);
        });

        $(details).find('.table-responsive').empty().append(
            $('<table class="table">').append(
                '<thead><tr><th scope="col">Objektum</th><th scope="col">Típus</th><th scope="col">Csillagkép</th>'
                + '<th scope="col" class="text-right">Észlelés</th><th scope="col" class="text-right">Észlelő</th></tr></thead>',
                body
            )
        );
    }

    function updateMetric() {
        maxValue = d3.max(objects, value) || 1;
        objects.sort((a, b) => value(b) - value(a));
        $('[data-legend-max]').text(objects.length ? `(legfeljebb ${maxValue} ${metric === 'observers' ? 'észlelő' : 'észlelés'} objektumonként)` : '');
        if (!isSingleObserver) {
            renderTop();
            renderTable();
        }
        redraw();
    }

    /* ---------- loading ---------- */

    /**
     * The edges of the Milky Way go around the whole sky, so it is not obvious which side of a ring is the inside of it.
     * Every ring is drawn with its smaller side and they are filled by the even-odd rule.
     */
    function buildMilkyWay(polygons) {
        const pole = [192.86, 27.13]; // galactic, never in the Milky Way
        const rings = d3.merge(polygons).map(function (ring) {
            const shape = {type: 'Polygon', coordinates: [ring]};

            return d3.geoArea(shape) > 2 * Math.PI ? {type: 'Polygon', coordinates: [ring.slice().reverse()]} : shape;
        });
        const isInverted = rings.filter(shape => d3.geoContains(shape, pole)).length % 2 === 1;

        return isInverted ? rings.concat({type: 'Sphere'}) : rings;
    }

    function setBackground(data) {
        background = {
            stars: data.stars,
            lines: {type: 'MultiLineString', coordinates: data.lines},
            constellations: data.constellations,
            milkyWay: data.milkyWay.map(buildMilkyWay),
        };
        redraw();
    }

    function resize() {
        const ratio = window.devicePixelRatio || 1;

        size = {width: canvas.clientWidth, height: canvas.clientHeight};
        canvas.width = size.width * ratio;
        canvas.height = size.height * ratio;
        redraw();
    }

    d3.select(canvas).call(zoom).on('dblclick.zoom', null);

    $(canvas)
        .on('mousemove', function (event) {
            const point = d3.pointer(event.originalEvent, canvas);
            const object = event.buttons ? null : findAt(point, HIT_RADIUS.mouse);

            canvas.style.cursor = object ? 'pointer' : '';
            if (object) {
                showTooltip(object, point);
            } else {
                hideTooltip();
            }
        })
        .on('mouseleave', function () {
            hideTooltip();
            // scrolling the page must not zoom the map the next time
            canvas.blur();
        })
        .on('click', function (event) {
            const isTouch = event.originalEvent.pointerType === 'touch';
            const object = findAt(d3.pointer(event.originalEvent, canvas), isTouch ? HIT_RADIUS.touch : HIT_RADIUS.mouse);

            if (object) {
                openPanel(object);
            } else {
                closePanel();
            }
        })
        .on('keydown', function (event) {
            if (keys[event.key] && !event.ctrlKey && !event.metaKey) {
                event.preventDefault();
                keys[event.key]();
            }
        });

    $(root).on('click', '[data-action]', function () {
        actions[this.dataset.action]();
    });

    $(root).on('change', '[data-layer]', function () {
        layers[this.dataset.layer] = this.checked;
        redraw();
    });

    $(root).on('change', '[name="skymap-metric"]', function () {
        metric = this.value;
        updateMetric();
    });

    $('.skymap-gaps').on('click', '[data-gap]', function () {
        const gap = gaps[this.dataset.gap];

        layers.gaps = true;
        root.querySelector('[data-layer="gaps"]').checked = true;
        root.scrollIntoView({behavior: 'smooth', block: 'start'});
        closePanel();
        moveTo({ra: gap.ra, dec: gap.dec, zoom: 3});
    });

    $('.skymap-table').on('toggle', renderTable);

    $(document).on('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        if (!panel.hidden) {
            closePanel();
        } else if (isFullscreen()) {
            toggleFullscreen(false);
        }
    });

    new ResizeObserver(resize).observe(stage);
    // the boxes keep their state on reload in some browsers
    $(root).find('[data-layer]').each((index, input) => layers[input.dataset.layer] = input.checked);
    if (!isSingleObserver) {
        metric = root.querySelector('[name="skymap-metric"]:checked').value;
    }

    Promise.all([d3.json(root.dataset.backgroundUrl), d3.json(root.dataset.objectsUrl)])
        .then(function ([backgroundData, objectData]) {
            message.hidden = true;
            objects = objectData;
            setBackground(backgroundData);
            updateMetric();
            openFromUrl();
        })
        .catch(function () {
            message.textContent = 'A térképet nem sikerült betölteni.';
        });
})(jQuery, window.d3);
