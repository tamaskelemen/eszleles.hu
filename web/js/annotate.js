$(document).ready(function() {
    let img = $("#img-container img")[0];

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

    viewtag(img.id);

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
            // let displayedImage = $('#' + tag.image_id)[0];
            anno.addAnnotation(parsedAnnotation);




        });
    }
});