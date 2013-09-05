(function() {
	tinymce.create('tinymce.plugins.CT_YouTubeButton', {
		init : function(ed, url) {
			ed.addButton('ct_youtube_button', {
				title : 'Add YouTube Video',
				image : url+'/youtube.png',
				onclick : function() {
					idPattern = /(?:(?:[^v]+)+v.)?([^&=]{11})(?=&|$)/;
					var vidId = prompt("Embed YouTube Video", "Enter the ID of your video");
					var m = idPattern.exec(vidId);
					if (m != null && m != 'undefined')
						ed.execCommand('mceInsertContent', false, '[youtube id="'+m[1]+'"]');
				}
			});
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			return {
				longname : "YouTube Shortcode",
				author : 'ChurchThemes',
				authorurl : 'http://churchthemes.net',
				infourl : 'http://churchthemes.net',
				version : "1.2"
			};
		}
	});
	tinymce.PluginManager.add('ct_youtube_button', tinymce.plugins.CT_YouTubeButton);
})();

(function() {
	tinymce.create('tinymce.plugins.CT_VimeoButton', {
		init : function(ed, url) {
			ed.addButton('ct_vimeo_button', {
				title : 'Add Vimeo Video',
				image : url+'/vimeo.png',
				onclick : function() {
					idPattern = /(^\s*\d+\s*$)/;
					var vidId = prompt("Embed Vimeo Video", "Enter the ID of your video");
					var m = idPattern.exec(vidId);
					if (m != null && m != 'undefined')
						ed.execCommand('mceInsertContent', false, '[vimeo id="'+m[1]+'"]');
				}
			});
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			return {
				longname : "Vimeo Shortcode",
				author : 'ChurchThemes',
				authorurl : 'http://churchthemes.net',
				infourl : 'http://churchthemes.net',
				version : "1.2"
			};
		}
	});
	tinymce.PluginManager.add('ct_vimeo_button', tinymce.plugins.CT_VimeoButton);
})();