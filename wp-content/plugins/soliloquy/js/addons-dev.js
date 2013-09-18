/**
 * jQuery to power Addon installations, activations and deactivations.
 *
 * @package   TGM-Soliloquy
 * @version   1.2.0
 * @author    Thomas Griffin <thomas@thomasgriffinmedia.com>
 * @copyright Copyright (c) 2012, Thomas Griffin
 */
jQuery(document).ready(function($) {
	
	/** Re-enable install button if user clicks on it, needs creds but tries to install another addon instead */
	$('#soliloquy-addon-area').on('click.refreshInstallAddon', '.soliloquy-addon-action-button', function(e) {
		var el 		= $(this);
		var buttons = $('#soliloquy-addon-area').find('.soliloquy-addon-action-button');
		$.each(buttons, function(i, element) {
			if ( el == element )
				return true;
				
			soliloquyAddonRefresh(element);
		});
	});

	/** Process Addon activations for those currently installed but not yet active */
	$('#soliloquy-addon-area').on('click.activateAddon', '.soliloquy-activate-addon', function(e) {
		e.preventDefault();
		
		/** Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated */
		$('.soliloquy-addon-error').remove();
		$(this).text(soliloquy_addon.activating);
		$(this).after('<span class="soliloquy-waiting"><img class="soliloquy-spinner" src="' + soliloquy_addon.spinner + '" width="16px" height="16px" style="margin-left: 6px; vertical-align: middle;" /></span>');
		var button	= $(this);
		var plugin 	= $(this).attr('rel');
		var el		= $(this).parent().parent();
		var message	= $(this).parent().parent().find('.addon-status');
		
		/** Process the Ajax to perform the activation */
		var opts = {
			url: 		ajaxurl,
            type: 		'post',
            async: 		true,
            cache: 		false,
            dataType: 	'json',
            data: {
                action: 	'soliloquy_activate_addon',
				nonce: 		soliloquy_addon.activate_nonce,
				plugin:		plugin
            },
            success: function(response) {
            	/** If there is a WP Error instance, output it here and quit the script */
                if ( response && true !== response ) {
                	$(el).slideDown('normal', function() {
                		$(this).after('<div class="soliloquy-addon-error"><strong>' + response.error + '</strong></div>');
                		$('.soliloquy-waiting').remove();
                		$('.soliloquy-addon-error').delay(3000).slideUp();
                	});
                	return;
                }
                
                /** The Ajax request was successful, so let's update the output */
                $(button).text(soliloquy_addon.deactivate).removeClass('soliloquy-activate-addon').addClass('soliloquy-deactivate-addon');
                $(message).text(soliloquy_addon.active);
                $(el).removeClass('soliloquy-addon-inactive').addClass('soliloquy-addon-active');
                $('.soliloquy-waiting').remove();
            },
            error: function(xhr, textStatus ,e) { 
                $('.soliloquy-waiting').remove();
                return; 
            }
		}
		$.ajax(opts);
	});
	
	/** Process Addon deactivations for those currently active */
	$('#soliloquy-addon-area').on('click.deactivateAddon', '.soliloquy-deactivate-addon', function(e) {
		e.preventDefault();
		
		/** Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated */
		$('.soliloquy-addon-error').remove();
		$(this).text(soliloquy_addon.deactivating);
		$(this).after('<span class="soliloquy-waiting"><img class="soliloquy-spinner" src="' + soliloquy_addon.spinner + '" width="16px" height="16px" style="margin-left: 6px; vertical-align: middle;" /></span>');
		var button	= $(this);
		var plugin 	= $(this).attr('rel');
		var el		= $(this).parent().parent();
		var message	= $(this).parent().parent().find('.addon-status');
		
		/** Process the Ajax to perform the activation */
		var opts = {
			url: 		ajaxurl,
            type: 		'post',
            async: 		true,
            cache: 		false,
            dataType: 	'json',
            data: {
                action: 	'soliloquy_deactivate_addon',
				nonce: 		soliloquy_addon.deactivate_nonce,
				plugin:		plugin
            },
            success: function(response) {
            	/** If there is a WP Error instance, output it here and quit the script */
                if ( response && true !== response ) {
                	$(el).slideDown('normal', function() {
                		$(this).after('<div class="soliloquy-addon-error"><strong>' + response.error + '</strong></div>');
                		$('.soliloquy-waiting').remove();
                		$('.soliloquy-addon-error').delay(3000).slideUp();
                	});
                	return;
                }
                
                /** The Ajax request was successful, so let's update the output */
                $(button).text(soliloquy_addon.activate).removeClass('soliloquy-deactivate-addon').addClass('soliloquy-activate-addon');
                $(message).text(soliloquy_addon.inactive);
                $(el).removeClass('soliloquy-addon-active').addClass('soliloquy-addon-inactive');
                $('.soliloquy-waiting').remove();
            },
            error: function(xhr, textStatus ,e) { 
                $('.soliloquy-waiting').remove();
                return; 
            }
		}
		$.ajax(opts);
	});
	
	/** Process Addon installations */
	$('#soliloquy-addon-area').on('click.installAddon', '.soliloquy-install-addon', function(e) {
		e.preventDefault();
		
		/** Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated */
		$('.soliloquy-addon-error').remove();
		$(this).text(soliloquy_addon.installing);
		$(this).after('<span class="soliloquy-waiting"><img class="soliloquy-spinner" src="' + soliloquy_addon.spinner + '" width="16px" height="16px" style="margin-left: 6px; vertical-align: middle;" /></span>');
		var button	= $(this);
		var plugin 	= $(this).attr('rel');
		var el		= $(this).parent().parent();
		var message	= $(this).parent().parent().find('.addon-status');
		var hook	= soliloquy_addon.pagehook.split('soliloquy_page_');
		
		/** Process the Ajax to perform the activation */
		var opts = {
			url: 		ajaxurl,
            type: 		'post',
            async: 		true,
            cache: 		false,
            dataType: 	'json',
            data: {
                action: 	'soliloquy_install_addon',
				nonce: 		soliloquy_addon.install_nonce,
				plugin:		plugin,
				hook:		hook[1]
            },
            success: function(response) {
            	/** If there is a WP Error instance, output it here and quit the script */
                if ( response.error ) {
                	$(el).slideDown('normal', function() {
                		$(this).after('<div class="soliloquy-addon-error"><strong>' + response.error + '</strong></div>');
                		$(button).text(soliloquy_addon.install);
                		$('.soliloquy-waiting').remove();
                		$('.soliloquy-addon-error').delay(4000).slideUp();
                	});
                	return;
                }
                
                /** If we need more credentials, output the form sent back to us */
                if ( response.form ) {
                	/** Display the form to gather the users credentials */
                	$(el).slideDown('normal', function() {
                		$(this).after('<div class="soliloquy-addon-error">' + response.form + '</div>');
                		$('.soliloquy-waiting').remove();
                	});
                	
                	/** Add a disabled attribute the install button if the creds are needed */
                	$(button).attr('disabled', true);
                	
                	$('#soliloquy-addon-area').on('click.installCredsAddon', '#upgrade', function(e) {
                		/** Prevent the default action, let the user know we are attempting to install again and go with it */
                		e.preventDefault();
                		$('.soliloquy-waiting').remove();
                		$(this).val(soliloquy_addon.installing);
                		$(this).after('<span class="soliloquy-waiting"><img class="soliloquy-spinner" src="' + soliloquy_addon.spinner + '" width="16px" height="16px" style="margin-left: 6px; vertical-align: text-bottom;" /></span>');
                		
                		/** Now let's make another Ajax request once the user has submitted their credentials */
                		var hostname 	= $(this).parent().parent().find('#hostname').val();
                		var username	= $(this).parent().parent().find('#username').val();
                		var password	= $(this).parent().parent().find('#password').val();
                		var proceed		= $(this);
                		var connect		= $(this).parent().parent().parent().parent();
                		var cred_opts 	= {
                			url: 		ajaxurl,
            				type: 		'post',
            				async: 		true,
            				cache: 		false,
            				dataType: 	'json',
            				data: {
                				action: 	'soliloquy_install_addon',
								nonce: 		soliloquy_addon.install_nonce,
								plugin:		plugin,
								hook:		hook[1],
								hostname:	hostname,
								username:	username,
								password:	password
            				},
            				success: function(response) {
            					/** If there is a WP Error instance, output it here and quit the script */
                				if ( response.error ) {
                					$(el).slideDown('normal', function() {
                						$(button).after('<div class="soliloquy-addon-error"><strong>' + response.error + '</strong></div>');
										$(button).text(soliloquy_addon.install);
                						$('.soliloquy-waiting').remove();
                						$('.soliloquy-addon-error').delay(4000).slideUp();
                					});
                					return;
                				}
                				
                				if ( response.form ) {
                					$('.soliloquy-waiting').remove();
                					$('.soliloquy-inline-error').remove();
                					$(proceed).val(soliloquy_addon.proceed);
                					$(proceed).after('<span class="soliloquy-inline-error">' + soliloquy_addon.connect_error + '</span>');
                					return;
                				}
                				
                				/** The Ajax request was successful, so let's update the output */
                				$(connect).remove();
                				$(button).show();
                				$(button).text(soliloquy_addon.activate).removeClass('soliloquy-install-addon').addClass('soliloquy-activate-addon');
                				$(button).attr('rel', response.plugin);
                				$(button).removeAttr('disabled');
                				$(message).text(soliloquy_addon.inactive);
                				$(el).removeClass('soliloquy-addon-not-installed').addClass('soliloquy-addon-inactive');
                				$('.soliloquy-waiting').remove();
            				},
            				error: function(xhr, textStatus ,e) { 
                				$('.soliloquy-waiting').remove();
                				return; 
            				}
                		}
                		$.ajax(cred_opts);
                	});
                	
                	/** No need to move further if we need to enter our creds */
                	return;
                }
                
                /** The Ajax request was successful, so let's update the output */
                $(button).text(soliloquy_addon.activate).removeClass('soliloquy-install-addon').addClass('soliloquy-activate-addon');
                $(button).attr('rel', response.plugin);
                $(message).text(soliloquy_addon.inactive);
                $(el).removeClass('soliloquy-addon-not-installed').addClass('soliloquy-addon-inactive');
                $('.soliloquy-waiting').remove();
            },
            error: function(xhr, textStatus ,e) { 
                $('.soliloquy-waiting').remove();
                return; 
            }
		}
		$.ajax(opts);
	});
	
	/** Function to clear any disabled buttons and extra text if the user needs to add creds but instead tries to install a different addon */
	function soliloquyAddonRefresh(element) {
		if ( $(element).attr('disabled') )
			$(element).removeAttr('disabled');
			
		if ( $(element).parent().parent().hasClass('soliloquy-addon-not-installed') )
			$(element).text(soliloquy_addon.install);
	}

});