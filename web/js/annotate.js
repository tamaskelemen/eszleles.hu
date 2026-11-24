$(document).ready(function() {
    let img = $("#img-container img")[0];
    let taglist = $('#taglist');
    let anno = Annotorious.init({
        image: img.id,
        locale: 'auto',
        readOnly: true,
        widgets: [
            {
                widget: 'TAG',
                // vocabulary: ['Asd', 'Building', 'Waterbody']
            }
        ],
    });

    // Annotorious prevents the default of every touch on the image, which blocks scrolling and zooming the page.
    // There is nothing to draw here, so keep the touches from it: it works with the mouse events the browser
    // sends after a tap.
    ['touchstart', 'touchmove', 'touchend', 'touchcancel'].forEach(function (type) {
        $('#img-container')[0].addEventListener(type, function (event) {
            if (event.target.closest('.a9s-annotationlayer')) {
                event.stopPropagation();
            }
        }, {capture: true, passive: true});
    });

    viewtag(img.id);

    // mouseover the taglist
    taglist.on('mouseover', 'span', function () {
        json = $(this).attr("data-json");
        json = JSON.parse(json);
        anno.addAnnotation(json);
    }).on('mouseout', 'span', function () {
        if (!anno.getSelected()) {
            anno.removeAnnotation(json.id);
        }
    });

    taglist.on('click', 'span', function () {
        if (anno.getSelected()) {
            anno.cancelSelected();
            anno.removeAnnotation(JSON.parse($(this).attr('data-json')).id);
        }
        anno.selectAnnotation(JSON.parse($(this).attr('data-json')));

    });

    function viewtag(pic_id) {

        let data = {
            "image_id": pic_id
        }
        // get the tag list with action remove and tag boxes and place it on the image.
        $.post("/tag/list", data, function (data) {
            generateList(data)
            // $('#tagbox').html(data.boxes);
            // generateBoxes(data.boxes);
        }, "json");
    }

    function generateList(data) {
        $.each(data, function (key, tag) {

            let parsedAnnotation = JSON.parse(tag.annotation)
            let tags = parsedAnnotation.body;

            let names = "";

            $.each(tags, function (index, tag) {
                if (names === "") {
                    names = tag.value;
                } else {
                    names = names + " / " + tag.value;
                }
            })
            $('#taglist').append(
                "<span class='tag-item' data-json='"
                + tag.annotation
                +  "'>"
                + names
                + "</span>"
            );

        });
    }
});