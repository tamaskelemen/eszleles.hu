$(document).ready(function() {
    var counter = 0;
    var mouseX = 0;
    var mouseY = 0;
    var imgid = 0;
    var str = '';

    $("#img-container img").click(function (e) { // make sure the image is click

        var imgcontainer = $(this).parent(); // get the div to append the tagging list

        //imgid = $(this).attr( "id" ); if you need to fetch tags by specific images

        mouseX = (e.pageX - $(imgcontainer).offset().left) - 50; // x and y axis
        mouseY = (e.pageY - $(imgcontainer).offset().top) - 50;
        $('#tagit').remove(); // remove any tagit div first
        $(imgcontainer).append(
            '<div id="tagit">' +
            '<div class="box"></div>' +
            '<div class="name">' +
            '<div class="tag-text">Jelölés:</div>' +
            '<input type="text" autocomplete="off" name="txtname" class="form-control-sm mb-1 search" id="tagname" />' +
            '<div id="result"></div>' +
            '<input type="button"  class="btn btn-primary btn-sm mr-1" name="btnsave" value="Mentés" id="btnsave" />' +
            '<input type="button" class="btn btn-info btn-sm" name="btncancel" value="Mégse" id="btncancel" />' +
            '</div>' +
            '</div>'
        );
        $('#tagit').css({top: mouseY, left: mouseX});

        $('#tagname').focus();
    });

// autocomplete

// check for input and send an ajax request with the characters typed.

// $( ".observe-view" ).on( 'keyup', '#tagname', function() {
//     str = $(this).val();
//
//     if( str != '' ) {
//         $.post(
//             "/tag/search",
//             {"search": str },
//             function( data ) {
//                 $( "#result" ).html( data).show(); //show the query results in div.
//             }
//         );
//     }

// });

// hide the result div when clicked away from the input.
    $(document).click(function (e) {
        var $clicked = $(e.target);
        if (!$clicked.hasClass("search")) {
            $('#result').fadeOut();
        }
    });

// show the result div when clicked on the input.
    $('.observe-view').on('click', '#tagname', function () {
        if ($.trim($("#result").text()) != '') {
            $('#result').fadeIn();
        }
    });

// fill the input with the value selected from the auto search result.
    $(".observe-view").on('click', '.ntag', function (e) {
        var txt = $(this).text();
        $('#tagname').val(txt);
    });

// Save button click - save tags
    $(document).on('click', '#tagit #btnsave', function () {
        name = $('#tagname').val();
        var img = $('#img-container').find('img');
        var id = $(img).attr('id');
        $.ajax({
            type: "POST",
            url: "/tag/create",
            data: {
                "image_id": id,
                "name": name,
                "coord_x": mouseX,
                "coord_y": mouseY
            },
            cache: true,
            success: function (data) {
                viewtag(id);
                $('#tagit').fadeOut();
            }
        });
    });

// Cancel the tag box.
    $(document).on('click', '#tagit #btncancel', function () {
        $('#tagit').fadeOut();
    });

// mouseover the taglist
    $('#taglist').on('mouseover', 'span', function () {
        id = $(this).attr("id");
        $('#view_' + id).css({opacity: 1.0});
    }).on('mouseout', 'span', function () {
        $('#view_' + id).css({opacity: 0.0});
    });

// mouseover the tagboxes that is already there but opacity is 0.
    $('#tagbox').on('mouseover', '.tagview', function () {
        var pos = $(this).position();
        $(this).css({opacity: 1.0}); // div appears when opacity is set to 1.
    }).on('mouseout', '.tagview', function () {
        $(this).css({opacity: 0.0}); // hide the div by setting opacity to 0.
    });

// Remove tags.
    $('#taglist ').on('click', '.remove', function () {
        id = $(this).attr("id");
        // Remove the tag
        $.ajax({
            type: "POST",
            url: "/tag/delete",
            data: {"id": id},
            success: function (data) {
                var img = $('#img-container').find('img');
                var id = $(img).attr('id');
                //get tags if present
                viewtag(id);
            }
        });
    });

// load the tags for the image when page loads.
    var img = $('#img-container').find('img');
    var id = $(img).attr('id');

    viewtag(id); // view all tags available on page load

    function viewtag(pic_id) {
        let data = {
            "image_id": pic_id
        }
        // get the tag list with action remove and tag boxes and place it on the image.
        $.post("/tag/list", data, function (data) {
            $('#taglist').html("");
            $('#tagbox').html("");
            generateList(data);
            // $('#tagbox').html(data.boxes);
            // generateBoxes(data.boxes);
        }, "json");

    }

    function generateList(data) {
        $.each(data, function (key, tag) {
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
                    + 'px;">' + tag.name + '</div>'
                );

            }
        );


    }
}