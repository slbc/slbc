(function() {	
	tinymce.create('tinymce.plugins.churchpackShortcodeMce', {
		init : function(ed, url){
			tinymce.plugins.churchpackShortcodeMce.theurl = url;
		},
		createControl : function(btn, e) {
			if ( btn == "churchpack_shortcodes_button" ) {
				var a = this;	
				var btn = e.createSplitButton('churchpack_button', {
	                title: "Insert Shortcode",
					image: tinymce.plugins.churchpackShortcodeMce.theurl +"/images/shortcodes.png",
					icons: false,
	            });
	            btn.onRenderMenu.add(function (c, b) {
					a.render( b, "Accordion", "accordion" );
					a.render( b, "Box", "box" );
					a.render( b, "Superquote", "superquote" );
					a.render( b, "Button", "button" );
					a.render( b, "Clear Floats", "clear" );
					a.render( b, "Column", "column" );
					a.render( b, "Divider", "divider" );
					a.render( b, "Google Map", "googlemap" );
					a.render( b, "Highlight", "highlight" );
					a.render( b, "Spacing", "spacing" );
					a.render( b, "Social Icon", "social" );
					a.render( b, "Tabs", "tabs" );
					a.render( b, "Toggle", "toggle" );
				});
	            
	          return btn;
			}
			return null;               
		},
		render : function(ed, title, id) {
			ed.add({
				title: title,
				onclick: function () {
					
					// Accordion
					if(id == "accordion") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_accordion]<br />[churchpack_accordion_section title="Section 1"]<br />Accordion Content<br />[/churchpack_accordion_section]<br />[churchpack_accordion_section title="Section 2"]<br />Accordion Content<br />[/churchpack_accordion_section]<br />[/churchpack_accordion]');
					}
					
					// Box
					if(id == "box") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_box color="yellow" text_align="left" width="100%" float="none"]<br />Box Content<br />[/churchpack_box]');
					}
					
					// Button
					if(id == "button") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_button color="blue" url="http://www.wpforchurch.com" title="Visit Site" target="blank" border_radius=""]Button Text[/churchpack_button]');
					}
					
					// Clear Floats
					if(id == "clear") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_clear_floats]');
					}
					
					// Column
					if(id == "column") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_column size="one-half" position="first"]<br />Content<br />[/churchpack_column]');
					}
				
					// Divider
					if(id == "divider") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_divider style="solid" margin_top="20px" margin_bottom="20px"]');
					}
					
					// Google Map
					if(id == "googlemap") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_googlemap title="Church Office" location="4700 NW 10th St, Oklahoma City, OK 73127" zoom="10" height=250]');
					}
					
					// Highlight
					if(id == "highlight") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_highlight color="yellow"]highlighted text[/churchpack_highlight]');
					}
					
					// Superquote
					if(id == "superquote") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_superquote]supersized text[/churchpack_superquote]');
					}
					
					//Spacing
					if(id == "spacing") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_spacing size="40px"]');
					}
					
					//Social
					if(id == "social") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_social icon="twitter" url="http://www.twitter.com/wpforchurch" title="Follow Us" target="self" rel=""]');
					}
					
					//Tabs
					if(id == "tabs") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_tabgroup]<br />[churchpack_tab title="First Tab"]<br />First tab content<br />[/churchpack_tab]<br />[churchpack_tab title="Second Tab"]<br />Second Tab Content.<br />[/churchpack_tab]<br />[churchpack_tab title="Third Tab"]<br />Third Tab Content.<br />[/churchpack_tab]<br />[/churchpack_tabgroup]');
					}
					
					//Toggle
					if(id == "toggle") {
						tinyMCE.activeEditor.selection.setContent('[churchpack_toggle title="This Is Your Toggle Title"]Your Toggle Content[/churchpack_toggle]');
					}
					
					
					return false;
				}
			})
		}
	
	});
	tinymce.PluginManager.add("churchpack_shortcodes", tinymce.plugins.churchpackShortcodeMce);
})();  