///////////////////////////////		
// Lightbox
///////////////////////////////	

function lightboxInit() {
	jQuery("a[rel^='prettyPhoto']").prettyPhoto({
		social_tools: false,
		deeplinking: false,
		overlay_gallery: false,
		show_title: false
	});
}
	

jQuery.noConflict();
jQuery(document).ready(function(){
	
	lightboxInit();	
	
});