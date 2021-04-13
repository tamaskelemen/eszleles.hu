$(document).ready(function() {
    let img = $("#img-container img")[0];

    let anno = Annotorious.init({
        image: img.id,
        locale: 'auto',
        // readOnly: true,
        widgets: [
            {
                widget: 'TAG',
                // vocabulary: ['Asd', 'Building', 'Waterbody']
            }
        ],
    });

    viewtag(img.id);

    anno.on('createAnnotation', function(annotation) {
        console.log('created', annotation.id);
        $.ajax({
            type: "POST",
            url: "/tag/create-json",
            data: {
                'image_id': img.id,
                'annotation_id': annotation.id,
                'data': annotation
            },
            cache: true,
            success: function (data) {
                console.log('saved', annotation)
            }
        });
    });

    anno.on('deleteAnnotation', function (annotation){
        console.log(annotation.id)
        $.ajax({
            type: "POST",
            url: "/tag/delete",
            data: {"annotation_id": annotation.id},
            success: function (data) {
                // var img = $('#img-container').find('img');
                // var id = $(img).attr('id');
                // //get tags if present
                // viewtag(id);
            }
        });
    });

    anno.on('updateAnnotation', function (annotation, previous) {
        console.log('updated', previous, 'with', annotation);
        $.ajax({
            type: "POST",
            url: "/tag/update",
            data: {
                "annotation_id": annotation.id,
                'annotation': annotation,
            },
            success: function (data) {
                var img = $('#img-container').find('img');
                var id = $(img).attr('id');
                //get tags if present
                viewtag(id);
            }
        });
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
            // let displayedImage = $('#' + tag.image_id)[0];
            anno.addAnnotation(parsedAnnotation);




        });
    }
});