$(document).ready(function() {
    // mouseover the taglist
    $('#taglist').on( 'mouseover', 'span', function( ) {
        id = $(this).attr("id");
        $('#view_' + id).css({ opacity: 1.0 });
    }).on( 'mouseout', 'span', function( ) {
        $('#view_' + id).css({ opacity: 0.0 });
    });

    // mouseover the tagboxes that is already there but opacity is 0.
    $( '#tagbox' ).on( 'mouseover', '.tagview', function( ) {
        var pos = $( this ).position();
        $(this).css({ opacity: 1.0 }); // div appears when opacity is set to 1.
    }).on( 'mouseout', '.tagview', function( ) {
        $(this).css({ opacity: 0.0 }); // hide the div by setting opacity to 0.
    });


    // load the tags for the image when page loads.
    var img = $('#img-container').find( 'img' );
    var id = $( img ).attr( 'id' );

    viewtag( id ); // view all tags available on page load

    function viewtag( pic_id ) {
        let data = {
            "image_id": pic_id
        }
        // get the tag list with action remove and tag boxes and place it on the image.
        $.post( "/tag/list" ,  data, function( data ) {
            $('#taglist').html("");
            $('#tagbox').html("");
            generateList(data);
            // $('#tagbox').html(data.boxes);
            // generateBoxes(data.boxes);
        }, "json");

    }

    function generateList(data) {
        $.each(data, function(key, tag) {
                // $('#taglist ol').append("<li id='" + tag.id + "'>" + tag.name + "<a class='btn btn-info remove'>x</a></li>");
                $('#taglist').append(
                    "<span class='tag-item remove' id='" + tag.id + "'>" + tag.name + "</span>"
                );
                $('#tagbox').append(
                  '<div class="tagview" style="left:'
                    + tag.coord_x
                    + 'px;top:'
                    + tag.coord_y + 'px;" id="view_' + tag.id + '">' +
                    '<div class="square"></div>' +
                    '<div class="person" style="left:'
                    + tag.coord_x
                    + 'px;top:'
                    + tag.coord_y
                    + 'px;">' +tag.name + '</div>'
                );

            }
        );


    }
});
