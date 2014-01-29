<?php
/*
Simple:Press
Help and Troubleshooting
$LastChangedDate: 2013-08-04 12:56:39 -0700 (Sun, 04 Aug 2013) $
$Rev: 10465 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ahah_support();
include_once(SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php');

spa_paint_open_tab(spa_text('Help and Troubleshooting'));
	spa_paint_open_panel();
		spa_paint_open_fieldset(spa_text('Help and Troubleshooting'), false, '', false);
?>
		<div class="codex">

			<img class="spLeft" src="<?php echo(SFCOMMONIMAGES); ?>sp-small-megaphone.png" alt="" title="" />
			<p class="codex-head">Simple:Press Codex articles you may find useful</p>

			<p class="codex-sub">Something wrong after the install?</p>
			<p>If you have some problems displaying the forums - or some of the features do not seem to be
			working then the answer is usually something simple. You can see if there is a solution in
			our <a href="http://codex.simple-press.com/codex/faq/troubleshooting/" >Troubleshooting FAQ</a>.</p>

			<p class="codex-sub">Trouble getting started?</p>
			<p>Our simple and quick <a href="http://codex.simple-press.com/codex/getting-started/" >Getting
			Started Guide</a> may be all you need to get your forums up and running.</p>

			<p class="codex-sub">How to display Simple:Press in your language</p>
			<p>Find out how to <a href="http://codex.simple-press.com/codex/installation/installation-information/localization/" >
			Localise your Forums</a>. Remember you will need to download and install SP theme and
			plugin language files as well as the core files.</p>

			<p class="codex-sub">How do I install a Simple:Press Theme?</p>
			<p>An introduction to <a href="http://codex.simple-press.com/codex/themes/theme-basics/using-themes/" >
			Using Themes</a>.

			<p class="codex-sub">How do I install a Simple:Press Plugin?</p>
			<p>An introduction to <a href="http://codex.simple-press.com/codex/plugins/using-plugins/" >
			Using Plugins</a>.

			<p class="codex-sub">Need to know How To...?</p>
			<p>Maybe the answer is in our Frequently Asked <a href="http://codex.simple-press.com/codex/faq/how-to/" >
			How To</a> section.</p>

		</div>
<?php
		spa_paint_close_fieldset(false);

		spa_paint_open_fieldset(spa_text('Premium Support'), false, '', false);
?>
		<div class="codex">

			<img class="spLeft" src="<?php echo(SFCOMMONIMAGES); ?>sp-small-megaphone.png" alt="" title="" />
			<p class="codex-head">Simple:Press - Premium Support</p>

			<p class="codex-sub">Want that extra level of support?</p>
			<p>Premium support gains you full access to our forums where our user-praised response times will
			help you get the best out of Simple:Press.</p>
			<p>You will also be able to access and download all of our latest Simple:Press plugins and additional
			themes as they become available.</p>
			<p>And for those who want to get into the code to perform some serious customisation - our Codex will
			provide details of the full Simple:Press API  - currently under construction.</p>
			<p>For membership details and plans please visit our <a href="http://simple-press.com/membership/" >
			Membership</a> page.

		</div>
<?php
		spa_paint_close_fieldset(false);

		spa_paint_open_fieldset(spa_text('Simple:Press Themes'), false, '', false);
?>
		<div class="codex">

			<img class="spLeft" src="<?php echo(SFCOMMONIMAGES); ?>sp-small-megaphone.png" alt="" title="" />
			<p class="codex-head">Available Simple:Press Themes</p>
<?php
			$f = wp_remote_get('http://simple-press.com/downloads/simple-press/sp_theme_list.xml');
            if (is_wp_error($f) || wp_remote_retrieve_response_code($f) != 200) {
				echo '<p class="codex-sub">'.spa_text('Unable to communicate with Simple Press server').'</p>';
            } else {
				$l = new SimpleXMLElement($f['body']);
				if (!empty($l->theme)) {
					foreach ($l->theme as $i) {
						echo '<p class="codex-sub">'.$i->name.'</p>';
						echo '<p>'.$i->desc.'</p>';
					}
				}
			}
?>
		</div>
<?php
		spa_paint_close_fieldset(false);

	spa_paint_close_panel();

spa_paint_tab_right_cell();

	spa_paint_open_panel();
		spa_paint_open_fieldset(spa_text('Simple:Press Plugins'), false, '', false);

?>
		<div class="codex">

			<img class="spLeft" src="<?php echo(SFCOMMONIMAGES); ?>sp-small-megaphone.png" alt="" title="" />
			<p class="codex-head">Available Simple:Press Plugins</p>
<?php
			$f = wp_remote_get('http://simple-press.com/downloads/simple-press/sp_plugin_list.xml');
            if (is_wp_error($f) || wp_remote_retrieve_response_code($f) != 200) {
				echo '<p class="codex-sub">'.spa_text('Unable to communicate with Simple Press server').'</p>';
            } else {
				$l = new SimpleXMLElement($f['body']);
				if (!empty($l->plugin)) {
					foreach ($l->plugin as $i) {
						echo '<p class="codex-sub">'.$i->name.'</p>';
						echo '<p>'.$i->desc.'</p>';
					}
				}
			}
?>
		</div>
<?php
		spa_paint_close_fieldset(false);
	spa_paint_close_panel();
spa_paint_close_tab();

die();
?>