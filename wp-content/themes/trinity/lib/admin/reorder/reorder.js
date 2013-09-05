jQuery(document).ready(function($) {

var config = {
		accept: 'page-item',
		noNestingClass: "no-nesting",
		opacity: 0.5,
		helperclass: 'reorder-highlight',
		onChange: function(serialized) {
			reorderData = serialized[0].hash;
		},
		autoScroll: true
};

// submit new order
jQuery(".submit-reorder").click(function() {
	//if the list has been reordered -- not the case when page first loads
	if ( typeof( window[ 'reorderData' ] ) != "undefined" ) {
		jQuery('span.reorder-loading').show().html('<img src="../wp-content/themes/' + reorder_vars.theme_folder + '/lib/admin/reorder/loading.gif" />');

		$.post('../wp-content/themes/' + reorder_vars.theme_folder + '/lib/admin/reorder/process-sortable.php', reorderData, function(data) {
			jQuery('span.reorder-loading').html('Updated').delay('300').fadeOut();
		});
	} else {
		jQuery('span.reorder-loading').show().html('Updated').delay('300').fadeOut();
	}
	return false;
});

jQuery('#order-posts-list-nested').NestedSortable(config);
jQuery('#order-posts-list').Sortable(config);

});
