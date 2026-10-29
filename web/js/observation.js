lightGallery(document.getElementById('lightgallery'));

var imageVersions = document.getElementById('image-versions');
if (imageVersions) {
    lightGallery(imageVersions, {selector: '.image-version'});
}
// $(".img-view").hover(function(){
//     $('.fullscreen-icon').show();
// },function(){
//     $('.fullscreen-icon').hide();
// });