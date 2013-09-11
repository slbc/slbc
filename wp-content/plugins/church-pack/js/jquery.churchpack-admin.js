/*
 * Church Pack Admin JS
 */

jQuery(document).ready(function (){
  jQuery(":checkbox").iButton({
	enableDrag: false 
  });
  // disable options only available in Pro version
  jQuery("#events").iButton("disable");
  //jQuery("#prayers").iButton("disable");
});