lightGallery(document.getElementById('lightgallery'));

var imageVersions = document.getElementById('image-versions');
if (imageVersions) {
    lightGallery(imageVersions, {selector: '.image-version'});
}

// Open the image in full screen when it is clicked / tapped.
// Annotorious covers the image with an svg layer where tags can be selected (and drawn by the owner), so a press
// only counts if it wasn't a drag and had nothing to do with a tag.
var imgContainer = document.getElementById('img-container');
if (imgContainer) {
    var maxMovement = 8;
    var pressStart = null;

    var isTagSelected = function () {
        return document.querySelector('.r6o-editor, .a9s-annotation.selected') !== null;
    };

    var isImage = function (target) {
        return target.closest('.img-view, .a9s-annotationlayer') !== null
            && target.closest('.a9s-annotation, .a9s-selection, .a9s-handle') === null;
    };

    var openOnPress = function (event, x, y) {
        var start = pressStart;
        pressStart = null;

        if (start === null || !isImage(event.target) || isTagSelected()) {
            return;
        }

        if (Math.abs(x - start.x) > maxMovement || Math.abs(y - start.y) > maxMovement) {
            return;
        }

        event.preventDefault();
        document.querySelector('#lightgallery [data-src]').click();
    };

    // Runs before Annotorious: pressing the image while a tag is selected should only deselect the tag.
    imgContainer.addEventListener('pointerdown', function (event) {
        // a second finger means pinch
        pressStart = event.isPrimary && !isTagSelected() ? {x: event.clientX, y: event.clientY} : null;
    }, true);

    imgContainer.addEventListener('click', function (event) {
        openOnPress(event, event.clientX, event.clientY);
    });

    // In edit mode Annotorious prevents the default of touch events, so there is no click on touchscreens.
    imgContainer.addEventListener('touchend', function (event) {
        var touch = event.changedTouches[0];
        openOnPress(event, touch.clientX, touch.clientY);
    });
}
