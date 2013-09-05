jQuery(document).ready(function($) {
	jQuery('#manager-sidebars #sidebar-list tbody tr:even').addClass('alternate');
	jQuery('#manager-sidebars #sidebar-list tbody tr:first td').append('<p>This will serve as the default sidebar area, it cannot be deleted.</p>');
	jQuery('#manager-sidebars #sidebar-list tbody tr:first th input').attr('disabled', 'true');
	jQuery('#manager-sidebars #sidebar-list tbody tr th input[name="home"]').parent().next().append('<p>Home sidebar is successfully created. It is intended solely for your homepage, and is not usable elsewhere.</p>');
});