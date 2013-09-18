/**
 * jQuery to power image uploads, modifications and removals.
 *
 * The object passed to this script file via wp_localize_script is
 * soliloquy.
 *
 * @package   TGM-Soliloquy
 * @version   1.0.0
 * @author    Thomas Griffin <thomas@thomasgriffinmedia.com>
 * @copyright Copyright (c) 2012, Thomas Griffin
 */
jQuery(document).ready(function($){

	/** Prepare formfield variable */
	var formfield, soliloquy_html_holder = {};

	/** Set the default slider type */
	if ( ! $('input[name="_soliloquy_settings[type]"]').is(':checked') )
		$('#soliloquy-default-slider').attr('checked', 'checked');

	/** Show/hide the default area on page load */
	if ( 'default' == $('input[name="_soliloquy_settings[type]"]:checked').val() ) {
		$('.soliloquy-upload-text, #soliloquy-upload, #soliloquy-images').show();
		$('#soliloquy-upload').css('display', 'inline-block');
	} else {
		$('.soliloquy-upload-text, #soliloquy-upload, #soliloquy-images').hide();
	}

	/** Show/hide the default area on user selection */
	$('input[name="_soliloquy_settings[type]"]').on('change', function() {
		if ( 'default' == $(this).val() ) {
			$('.soliloquy-upload-text, #soliloquy-upload, #soliloquy-images').fadeIn();
			$('#soliloquy-upload').css('display', 'inline-block');
		} else {
			$('.soliloquy-upload-text, #soliloquy-upload, #soliloquy-images').hide();
		}
	});

	/** Hide elements on page load */
	$('.soliloquy-image-meta').hide();

	/** Hide advanced options on page load */
	if ( $('#soliloquy-advanced').is(':checked') ) {
		$('#soliloquy-navigation-box, #soliloquy-control-box, #soliloquy-keyboard-box, #soliloquy-multi-keyboard-box, #soliloquy-mousewheel-box, #soliloquy-pauseplay-box, #soliloquy-random-box, #soliloquy-number-box, #soliloquy-control-box, #soliloquy-loop-box, #soliloquy-action-box, #soliloquy-hover-box, #soliloquy-slider-css-box, #soliloquy-reverse-box, #soliloquy-smooth-box, #soliloquy-touch-box, #soliloquy-delay-box, .soliloquy-advanced').show();
	} else {
		$('#soliloquy-navigation-box, #soliloquy-control-box, #soliloquy-keyboard-box, #soliloquy-multi-keyboard-box, #soliloquy-mousewheel-box, #soliloquy-pauseplay-box, #soliloquy-random-box, #soliloquy-number-box, #soliloquy-control-box, #soliloquy-loop-box, #soliloquy-action-box, #soliloquy-hover-box, #soliloquy-slider-css-box, #soliloquy-reverse-box, #soliloquy-smooth-box, #soliloquy-touch-box, #soliloquy-delay-box, .soliloquy-advanced').hide();
	}

	/** Set default post meta fields */
	if ( 0 == $('#soliloquy-width').val().length ) {
		$('#soliloquy-width').val(soliloquy.width);
	}

	if ( 0 == $('#soliloquy-height').val().length ) {
		$('#soliloquy-height').val(soliloquy.height);
	}

	if ( 'custom' !== $('#soliloquy-default-size option[selected]').val() )
		$('#soliloquy-custom-sizes').hide();
	else
		$('#soliloquy-default-sizes').hide();

	if ( 0 == $('#soliloquy-speed').val().length ) {
		$('#soliloquy-speed').val(soliloquy.speed);
	}

	if ( 0 == $('#soliloquy-duration').val().length ) {
		$('#soliloquy-duration').val(soliloquy.duration);
	}

	/** Process toggle switches for field changes */
	$('#soliloquy-default-size').on('change', function() {
		if ( 'default' !== $(this).val() ) {
			$('#soliloquy-default-sizes').hide();
			$('#soliloquy-custom-sizes').fadeIn('normal');
		}
		else {
			$('#soliloquy-custom-sizes').hide();
			$('#soliloquy-default-sizes').fadeIn('normal');
		}
	});

	$('#soliloquy-advanced').on('change', function() {
		if ( $(this).is(':checked') ) {
			$('#soliloquy-navigation-box, #soliloquy-control-box, #soliloquy-keyboard-box, #soliloquy-multi-keyboard-box, #soliloquy-mousewheel-box, #soliloquy-pauseplay-box, #soliloquy-random-box, #soliloquy-number-box, #soliloquy-control-box, #soliloquy-loop-box, #soliloquy-action-box, #soliloquy-hover-box, #soliloquy-slider-css-box, #soliloquy-reverse-box, #soliloquy-smooth-box, #soliloquy-touch-box, #soliloquy-delay-box, .soliloquy-advanced').fadeIn('normal');
		} else {
			$('#soliloquy-navigation-box, #soliloquy-control-box, #soliloquy-keyboard-box, #soliloquy-multi-keyboard-box, #soliloquy-mousewheel-box, #soliloquy-pauseplay-box, #soliloquy-random-box, #soliloquy-number-box, #soliloquy-control-box, #soliloquy-loop-box, #soliloquy-action-box, #soliloquy-hover-box, #soliloquy-slider-css-box, #soliloquy-reverse-box, #soliloquy-smooth-box, #soliloquy-touch-box, #soliloquy-delay-box, .soliloquy-advanced').fadeOut('normal');
		}
	});

	/** Process fadeToggle for slider size explanation */
	$('.soliloquy-size-more').on('click.soliloquySizeExplain', function(e) {
		e.preventDefault();
		$('#soliloquy-explain-size').fadeToggle();
	});

	/** Process image removals */
	$('#soliloquy-area').on('click.soliloquyRemove', '.remove-image', function(e) {
		e.preventDefault();

		/** Bail out if the user does not actually want to remove the image */
		var confirm_delete = confirm(soliloquy.delete_nag);
		if ( ! confirm_delete )
			return;

		var maybe_delete = $(this).parent().attr('data-full-delete'),
			maybe_true	 = (typeof maybe_delete !== 'undefined' && maybe_delete !== false) ? true : false;
		formfield 	  	 = $(this).parent().attr('id');

		/** Output loading icon and message */
		$('#soliloquy-upload').after('<span class="soliloquy-waiting"><img class="soliloquy-spinner" src="' + soliloquy.spinner + '" width="16px" height="16px" style="margin: -1px 5px 0; vertical-align: middle;" />' + soliloquy.removing + '</span>');

		/** Prepare our data to be sent via Ajax */
		var remove = {
			action: 		'soliloquy_remove_images',
			attachment_id: 	formfield,
			do_delete:		maybe_true ? true : false,
			nonce: 			soliloquy.removenonce
		};

		/** Process the Ajax response and output all the necessary data */
		$.post(
			soliloquy.ajaxurl,
			remove,
			function(response) {
				$('#' + formfield).fadeOut('normal', function() {
					$(this).remove();

					/** Remove the spinner and loading message */
					$('.soliloquy-waiting').fadeOut('normal', function() {
						$(this).remove();
						$('.soliloquy-load-library').attr('data-soliloquy-offset', 0).addClass('has-search').trigger('click');
					});
				});
			},
			'json'
		);
	});

	// Open up the new media modal area for modifying slide metadata.
	$('#soliloquy-area').on('click.soliloquyModify', '.modify-image', function(e) {
		e.preventDefault();
		var attach_id = $(this).parent().attr('id'),
			formfield = 'meta-' + attach_id;

		// Show the modal.
		$('#' + formfield).appendTo('body').show();
		$.each(soliloquy_html_holder, function(){
			this.refresh();
		});

		// Close the modal window on user action
		var append_and_hide = function(e){
			e.preventDefault();
			$('#' + formfield).appendTo('#' + attach_id).hide();
		};
		$(document).on('click.soliloquyIframe', '.media-modal-close, .media-modal-backdrop', append_and_hide);
		$(document).on('keydown.soliloquyIframe', function(e){
			if ( 27 == e.keyCode )
				append_and_hide(e);
		});
	});

	/** Save image meta via Ajax */
	$(document).on('click.soliloquyMeta', '.soliloquy-meta-submit', function(e) {
		e.preventDefault();

		/** Set default meta values that any addon would need */
		var table		  = $(this).parent().parent().parent().prev().find('.soliloquy-meta-table').attr('id');
		var attach_id 	  = $(this).parent().parent().parent().prev().find('.soliloquy-meta-table').attr('data-attachment-id');
		var default_txt	  = $(this).text();
		var el			  = $(this);
		var type		  = $(this).parent().parent().parent().prev().find('.soliloquy-meta-table').attr('data-slide-type');
		var title 		  = false;

		// Set a saving message.
		el.text(soliloquy.saving);

		// If the slide type is video or HTML, save the title to be updated after everything has been saved.
		if ( 'video' == type ) title = $("#meta-" + attach_id).find('.soliloquy-video-title').val();
		if ( 'html' == type ) title = $("#meta-" + attach_id).find('.soliloquy-html-title').val();

		/** Prepare our data to be sent via Ajax */
		var meta = {
			action: 	'soliloquy_update_meta',
			attach: 	attach_id,
			id: 		soliloquy.id,
			type: 		type,
			nonce: 		soliloquy.metanonce
		};

		/** Loop through each table item and send data for every item that has a usable class */
		$('#' + table + ' td').each(function() {
			/** Grab all the items within each td element */
			var children = $(this).find('*');

			/** Loop through each child element */
			$.each(children, function() {
				var field_class = $(this).attr('class');
				    if ( field_class )
				        field_class = field_class.replace(' ', '-');
				var field_val 	= $(this).val();

				if ( 'checkbox' == $(this).attr('type') )
					var field_val = $(this).is(':checked') ? 'true' : 'false';

				if ( 'radio' == $(this).attr('type') ) {
					if ( ! $(this).is(':checked') ) {
						return;
					}
					var field_val = $(this).val();
				}

				/** Store all data in the meta object */
				meta[field_class] = field_val;
			});
		});

		/** Process the Ajax response and output all the necessary data */
		$.post(
			soliloquy.ajaxurl,
			meta,
			function(response){
				// Set the saved message to default text
				el.text(soliloquy.saved);

				// Make sure to set the image title to the update value.
				if ( title ) $('#' + attach_id).find('h4').text(title);

				// Set small delay before closing out the meta modal.
				setTimeout(function(){
					$('#meta-' + attach_id).hide().appendTo('#' + attach_id);
					el.text(default_txt);
				}, 500);
			},
			'json'
		);
	});

	// Use the new media manager to handle uploads to Soliloquy.
	$('#soliloquy-area').on('click.soliloquyUpload', '#soliloquy-upload', function(e){
		e.preventDefault();

		// Flag to show main frame is open.
		var main_frame = true;

		// Show the modal.
		$('#soliloquy-upload-ui').appendTo('body').show();

		// Close the modal window on user action
		var append_and_hide = function(e){
			e.preventDefault();
			$('#soliloquy-upload-ui').appendTo('#soliloquy-upload-ui-wrapper').hide();
			soliloquyRefresh();
			main_frame = false;
		};
		$(document).on('click.soliloquyIframe', '#soliloquy-upload-ui .media-modal-close, #soliloquy-upload-ui .media-modal-backdrop', append_and_hide);
		$(document).on('keydown.soliloquyIframe', function(e){
			if ( 27 == e.keyCode && main_frame )
				append_and_hide(e);
		});
	});

	// Change content areas and active menu states on media router click.
	$('.soliloquy-media-frame .media-menu-item').on('click', function(e){
		e.preventDefault();
		var $this = $(this),
			old_content = $this.parent().find('.active').removeClass('active').data('soliloquy-content'),
			new_content = $this.addClass('active').data('soliloquy-content');
		$('#' + old_content).hide();
		$('#' + new_content).show();
		if ( 'soliloquy-html-slides' == new_content ) {
			$.each(soliloquy_html_holder, function(){
				this.refresh();
			});
		}
	});

	// Add the selected state to images when selected from the library view.
	$('.soliloquy-gallery').on('click', '.thumbnail, .check, .media-modal-icon', function(e){
		e.preventDefault();
		if ( $(this).parent().parent().hasClass('soliloquy-in-slider') )
			return;
		if ( $(this).parent().parent().hasClass('selected') )
			$(this).parent().parent().removeClass('details selected');
		else
			$(this).parent().parent().addClass('details selected');
	});

	// Load more images into the library view.
	$('.soliloquy-load-library').on('click', function(e){
		e.preventDefault();
		var $this = $(this);
		$this.after('<span class="soliloquy-waiting" style="display: inline-block; margin-top: 16px;"><img class="soliloquy-spinner" src="' + soliloquy.spinner + '" width="16px" height="16px" style="margin: -1px 5px 0; vertical-align: middle;" />' + soliloquy.loading + '</span>');

		/** Prepare our data to be sent via Ajax */
		var load = {
			action: 	'soliloquy_load_library',
			offset: 	parseInt($this.attr('data-soliloquy-offset')),
			post_id:	soliloquy.id,
			nonce: 		soliloquy.loadnonce
		};

		/** Process the Ajax response and output all the necessary data */
		$.post(
			soliloquy.ajaxurl,
			load,
			function(response) {
				$this.attr('data-soliloquy-offset', parseInt($this.attr('data-soliloquy-offset')) + 20);

				// Append the response data.
				if ( response && response.html && $this.hasClass('has-search') ) {
					$('.soliloquy-gallery').html(response.html);
					$this.removeClass('has-search');
				} else {
					$('.soliloquy-gallery').append(response.html);
				}

				/** Remove the spinner and loading message */
				$('.soliloquy-waiting').fadeOut('normal', function() {
					$(this).remove();
				});
			},
			'json'
		);
	});

	// Initialize the code editor for HTML slides.
	$('.soliloquy-html').find('.soliloquy-html-code').each(function(i, el){
		var id = $(el).attr('id');
		soliloquy_html_holder[id] = CodeMirror.fromTextArea(el, {
			enterMode: 		'keep',
			indentUnit: 	4,
			electricChars:  false,
			lineNumbers: 	true,
			lineWrapping: 	true,
			matchBrackets: 	true,
			mode: 			'php',
			smartIndent:    false,
			tabMode: 		'shift',
			theme:			'eclipse'
		});
		soliloquy_html_holder[id].on('blur', function(obj){
			$('#' + id).text(obj.getValue());
		});
		soliloquy_html_holder[id].refresh();
	});

	// Load in new HTML slides when the add HTML slide button is clicked.
	$('.soliloquy-add-html-slide').on('click', function(e){
		e.preventDefault();
		var number = parseInt($(this).attr('data-soliloquy-html-number')),
			id	   = 'soliloquy-html-slide-' + $(this).attr('data-soliloquy-html-number');
		$(this).attr('data-soliloquy-html-number', number + 1 ).parent().before(soliloquyGetHtmlSlideMarkup(number));
		soliloquy_html_holder[id] = CodeMirror.fromTextArea(document.getElementById(id), {
			enterMode: 		'keep',
			indentUnit: 	4,
			electricChars:  false,
			lineNumbers: 	true,
			lineWrapping: 	true,
			matchBrackets: 	true,
			mode: 			'php',
			smartIndent:    false,
			tabMode: 		'shift',
			theme:			'eclipse'
		});
		soliloquy_html_holder[id].on('blur', function(obj){
			$('#' + id).text(obj.getValue());
		});
		soliloquy_html_holder[id].refresh();
	});

	function soliloquyGetHtmlSlideMarkup(number) {
		var html = '';
		html += '<div class="soliloquy-html-slide-holder"><p class="no-margin-top"><a href="#" class="button button-secondary soliloquy-delete-html-slide" title="' + soliloquy.htmlremove + '">' + soliloquy.htmlremove + '</a><label for="soliloquy-html-slide-' + number + '-title"><strong>' + soliloquy.htmlslide + '</strong></label><br /><input type="text" class="soliloquy-html-slide-title" id="soliloquy-html-slide-' + number + '-title" value="" placeholder="' + soliloquy.htmlplace + '" /></p><textarea class="soliloquy-html-slide-code" id="soliloquy-html-slide-' + number + '">' + soliloquy.htmlstart + '</textarea></div>';
		return html;
	}

	// Delete an HTML slide from the DOM when the user clicks to remove it.
	$('#soliloquy-html-slides').on('click', '.soliloquy-delete-html-slide', function(e){
		e.preventDefault();
		$(this).parent().parent().remove();
	});

	// Load in new video slides when the add video slide button is clicked.
	$('.soliloquy-add-video-slide').on('click', function(e){
		e.preventDefault();
		var number = parseInt($(this).attr('data-soliloquy-video-number')),
			id	   = 'soliloquy-video-slide-' + $(this).attr('data-soliloquy-html-number');
		$(this).attr('data-soliloquy-video-number', number + 1 ).parent().before(soliloquyGetVideoSlideMarkup(number));
	});

	function soliloquyGetVideoSlideMarkup(number) {
		var html = '';
		html += '<div class="soliloquy-video-slide-holder"><p class="no-margin-top"><a href="#" class="button button-secondary soliloquy-delete-video-slide" title="' + soliloquy.htmlremove + '">' + soliloquy.htmlremove + '</a><label for="soliloquy-video-slide-' + number + '-title"><strong>' + soliloquy.videoslide + '</strong></label><br /><input type="text" class="soliloquy-video-slide-title" id="soliloquy-video-slide-' + number + '-title" value="" placeholder="' + soliloquy.videoplace + '" /></p><p><label for="soliloquy-video-slide-' + number + '"><strong>' + soliloquy.videotitle + '</strong></label><br /><input type="text" class="soliloquy-video-slide-url" id="soliloquy-video-slide-' + number + '" value="" placeholder="' + soliloquy.videooutput + '" /></p><p class="no-margin-bottom"><label for="soliloquy-video-slide-' + number + '-caption"><strong>' + soliloquy.videocaption + '</strong></label><br /><textarea class="soliloquy-video-slide-caption" id="soliloquy-video-slide-' + number + '-caption"></textarea></p></div>';
		return html;
	}

	// Delete a video slide from the DOM when the user clicks to remove it.
	$('#soliloquy-video-slides').on('click', '.soliloquy-delete-video-slide', function(e){
		e.preventDefault();
		$(this).parent().parent().remove();
	});

	// Process inserting slides into slider when the Insert button is pressed.
	$(document).on('click', '.soliloquy-media-insert', function(e){
		e.preventDefault();
		var $this = $(this),
			text  = $(this).text(),
			data  = {
				action: 'soliloquy_insert_slides',
				nonce: soliloquy.insertnonce,
				post_id: soliloquy.id,
				data: {
					selected: {},
					video: {},
					html: {}
				}
			},
			selected = false,
			video = false,
			html = false;
		$this.text(soliloquy.inserting);

		// Set var for closing the modal.
		var append_and_hide = function(){
			$('#soliloquy-upload-ui').appendTo('#soliloquy-upload-ui-wrapper').hide();
			soliloquyRefresh();
			main_frame = false;
		};

		// Loop through potential data to send when inserting slides, including selected slides, video slides and HTML slides.
		// Uploaded slides will already be inserted as attachments, so we can pass over them.
		// First, we loop through the selected items and add them to the data var.
		$('.soliloquy-media-frame').find('.attachment.selected:not(.soliloquy-in-slider)').each(function(i, el){
			data.data.selected[i] = $(el).attr('data-attachment-id');
			selected = true;
		});

		// Next, we loop through any video slides that have been created.
		$('.soliloquy-media-frame').find('.soliloquy-video-slide-holder').each(function(i, el){
			data.data.video[i] = {
				title: $(el).find('.soliloquy-video-slide-title').val(),
				url: $(el).find('.soliloquy-video-slide-url').val(),
				caption: $(el).find('.soliloquy-video-slide-caption').val()
			};
			video = true;
		});

		// Finally, we loop through any HTML slides that have been created.
		$('.soliloquy-media-frame').find('.soliloquy-html-slide-holder').each(function(i, el){
			data.data.html[i] = {
				title: $(el).find('.soliloquy-html-slide-title').val(),
				code: $(el).find('.soliloquy-html-slide-code').val()
			};
			html = true;
		});

		// Send the ajax request with our data to be processed.
		$.post(
			soliloquy.ajaxurl,
			data,
			function(response){
				$this.text(soliloquy.inserted);
				// Set small delay before closing modal.
				setTimeout(function(){
					// Re-append modal to correct spot and revert text back to default.
					append_and_hide();
					$this.text(text);

					// If we have selected items, be sure to properly load first images back into view to show updated slides.
					if ( selected ) $('.soliloquy-load-library').attr('data-soliloquy-offset', 0).addClass('has-search').trigger('click');

					// If we have inserted video slides, be sure to clear out the slide list when the modal is opened again.
					if ( video ) {
						$('#soliloquy-video-slides').find('.soliloquy-delete-video-slide').each(function(){
							$(this).trigger('click');
						});
					}

					// If we have inserted HTML slides, be sure to clear out the slide list when the modal is opened again.
					if ( html ) {
						$('#soliloquy-html-slides').find('.soliloquy-delete-html-slide').each(function(){
							$(this).trigger('click');
						});
					}
				}, 500);
			},
			'json'
		);

	});

	$(document).on('keyup.soliloquySearchLibrary keydown.soliloquySearchLibrary', '#soliloquy-gallery-search', function(){
		var $this = $(this);
		// Ensure loading icon has been removed before outputting again.
		$('.soliloquy-waiting').remove();
		$this.before('<span class="soliloquy-waiting" style="display: inline-block; margin-top: 16px; margin-right: 10px;"><img class="soliloquy-spinner" src="' + soliloquy.spinner + '" width="16px" height="16px" style="margin: -1px 5px 0; vertical-align: middle;" />' + soliloquy.searching + '</span>');

		var text 		= $(this).val();
		var search 		= {
			action: 	'soliloquy_library_search',
			nonce: 		soliloquy.librarysearch,
			post_id: 	soliloquy.id,
			search: 	text
		}

		/** Send the ajax request with a delay (500ms after the user stops typing */
		delay(function() {
			/** Process the Ajax response and output all the necessary data */
			$.post(
				soliloquy.ajaxurl,
				search,
				function(response) {
					// Notify the load button that we have entered a search and reset the offset counter.
					$('.soliloquy-load-library').addClass('has-search').attr('data-soliloquy-offset', parseInt(0));

					// Append the response data.
					if ( response )
						$('.soliloquy-gallery').html(response.html);

					/** Remove the spinner and loading message */
					$('.soliloquy-waiting').fadeOut('normal', function() {
						$(this).remove();
					});
				},
				'json'
			);
		}, '500');
	});

	/** Make image uploads sortable */
	var items = $('#soliloquy-images');

	/** Use Ajax to update the item order */
	items.sortable({
		containment: '#soliloquy-area',
		update: function(event, ui) {
			/** Show the loading text and icon */
			$('.soliloquy-waiting').show();

			/** Prepare our data to be sent via Ajax */
			var opts = {
				url: 		soliloquy.ajaxurl,
                type: 		'post',
                async: 		true,
                cache: 		false,
                dataType: 	'json',
                data:{
                    action: 	'soliloquy_sort_images',
					order: 		items.sortable('toArray').toString(),
					post_id: 	soliloquy.id,
					nonce: 		soliloquy.sortnonce
                },
                success: function(response) {
                    $('.soliloquy-waiting').hide();
                    return;
                },
                error: function(xhr, textStatus ,e) {
                    $('.soliloquy-waiting').hide();
                    return;
                }
            };
            $.ajax(opts);
		}
	});

	/** jQuery function for loading the image uploads */
	function soliloquyRefresh() {
		/** Prepare our data to be sent via Ajax */
		var refresh = {
			action: 'soliloquy_refresh_images',
			id: 	soliloquy.id,
			nonce: 	soliloquy.nonce
		};

		/** Output loading icon and message */
		$('.soliloquy-waiting').remove();
		$('#soliloquy-upload').after('<span class="soliloquy-waiting"><img class="soliloquy-spinner" src="' + soliloquy.spinner + '" width="16px" height="16px" style="margin: -1px 5px 0; vertical-align: middle;" />' + soliloquy.loading + '</span>');

		/** Process the Ajax response and output all the necessary data */
		$.post(
			soliloquy.ajaxurl,
			refresh,
			function(json) {
				/** Load the new HTML with the newly uploaded images */
				$('#soliloquy-images').html(json.images);

				/** Hide the image meta when refreshing the list */
				$('.soliloquy-image-meta').hide();

				// Load TinyMCE editors for captions.
				$('.soliloquy-image').find('.wp-editor-area').each(function(i, el){
				    var id = $(el).attr('id').split('-')[2];
				    quicktags({id: 'soliloquy-caption-' + id, buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,close'});
				    QTags._buttonsInit(); // Force buttons to initialize since they have already been intialized on page load.
				});

				// Initialize any code editors that have been generated with HTML slides.
				$('.soliloquy-html').find('.soliloquy-html-code').each(function(i, el){
					var id = $(el).attr('id');
					soliloquy_html_holder[id] = CodeMirror.fromTextArea(el, {
						enterMode: 		'keep',
						indentUnit: 	4,
						electricChars:  false,
						lineNumbers: 	true,
						lineWrapping: 	true,
						matchBrackets: 	true,
						mode: 			'php',
						smartIndent:    false,
						tabMode: 		'shift',
						theme:			'eclipse'
					});
					soliloquy_html_holder[id].on('blur', function(obj){
						$('#' + id).text(obj.getValue());
					});
					soliloquy_html_holder[id].refresh();
				});
			},
			'json'
		);

		/** Remove the spinner and loading message */
		$('.soliloquy-waiting').fadeOut('normal', function() {
			$(this).remove();
		});
	}

	/** Process internal linking component */
	var delay = (function() {
  		var timer = 0;
  		return function(callback, ms) {
    		clearTimeout (timer);
    		timer = setTimeout(callback, ms);
  		};
	})();

	$(document).on('click.soliloquyInternalLinking', '#soliloquy-link-existing', function(e){
		e.preventDefault();
		var el = $(this);
		$(this).toggleClass('needs-close');
		$(this).next().fadeToggle('normal', function(){
			if ( el.hasClass('needs-close') )
				el.text(soliloquy.linkclose);
			else
				el.text(soliloquy.linkopen);
		});
	});

	$(document).on('keyup.soliloquySearchLinks keydown.soliloquySearchLinks', '.soliloquy-search', function() {
		var id 			= $(this).attr('id');
		var text 		= $(this).val();
		var link_output = $(this).next().find('ul').attr('id');
		var search 		= {
			action: 'soliloquy_link_search',
			id: 	soliloquy.id,
			nonce: 	soliloquy.linknonce,
			search: text
		}

		/** Send the ajax request with a delay (500ms after the user stops typing */
		delay(function() {
			soliloquySearch(id, link_output, search);
		}, '500');
	});

	/** Insert internal link when clicked */
	$(document).on('click.soliloquyInsertLink', '.soliloquy-results-list li', function(){
		// Don't do anything if there are no results
		if ( $(this).hasClass('soliloquy-no-results') )
			return;

		/** Remove the old selected class if it exists and add it to the selected item */
		$('.soliloquy-results-list li').removeClass('selected');
		$(this).addClass('selected');

		var search_link 	= $(this).find('input').val();
		var search_title 	= $(this).find('.soliloquy-item-title').text();
		var image_link 		= $(this).parent().parent().parent().parent().parent().find('.soliloquy-link').attr('id');
		var image_title 	= $(this).parent().parent().parent().parent().parent().find('.soliloquy-link-title').attr('id');

		$('#' + image_link).val(search_link);
		$('#' + image_title).val(search_title);
	});

	/** Callback function when searching for internal link matches */
	function soliloquySearch(id, link_output, search) {
		/** Output loading icon and message */
		$('#' + id).after('<span class="soliloquy-waiting"><img class="soliloquy-spinner" src="' + soliloquy.spinner + '" width="16px" height="16px" style="margin: -1px 5px 0; vertical-align: middle;" />' + soliloquy.searching + '</span>');

		/** Send the Ajax request and output the returned data */
		$.post(
			soliloquy.ajaxurl,
			search,
			function(json) {
				/** Remove old links to refresh with new ones */
				$('#' + link_output).children().remove();

				/** If no results were found, display a message */
				if ( ! json.links ) {
					var output =
						'<li class="soliloquy-no-results">' +
							'<span>' + soliloquy.noresults + '</span>' +
						'</li>';

					/** Display the newly generated link results */
					$('#' + link_output).append(output);
				}

				$.each(json.links, function(i, object) {
					/** Store each link and its data into the link variable */
					var link 	= json.links[i];
					var row 	= (i%2 == 0) ? 'even' : 'odd';

					/** Store the output into a variable */
					var output =
						'<li id="link-id-' + link.ID + '" class="soliloquy-result ' + row + '">' +
							'<input type="hidden" class="soliloquy-item-permalink" value="' + link.permalink + '" />' +
							'<span class="soliloquy-item-title">' + link.title + '</span>' +
							'<span class="soliloquy-item-info">' + link.info + '</span>' +
						'</li>';

					/** Display the newly generated link results */
					$('#' + link_output).append(output);
				});

				var output = '';
			},
			'json'
		);

		/** Remove the spinner and loading message */
		$('.soliloquy-waiting').fadeOut('normal', function() {
			$(this).remove();
		});
	}

	// Empty callback to ensure buttons initialize properly.
	function soliloquyButtonHelper(){}

});