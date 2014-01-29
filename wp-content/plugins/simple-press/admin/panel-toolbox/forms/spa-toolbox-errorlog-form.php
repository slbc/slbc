<?php
/*
Simple:Press
Admin Toolbox Error Log Form
$LastChangedDate: 2013-09-15 12:34:26 -0700 (Sun, 15 Sep 2013) $
$Rev: 10694 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_errorlog_form()
{
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfclearlog', 'sfreloadel');
});
</script>
<?php

	$sflog = spa_get_errorlog_data();

	spa_paint_open_tab(spa_text('Toolbox')." - ".spa_text('Error Log'), true);

		spa_paint_open_fieldset(spa_text('Error Log'), false, '', true);

			echo "<table class='sfhelptext'><tr>";

			echo "<td class='spaErrError spaErrCell'><b>".spa_text('Error')."</b></td>";
			echo "<td class='spaErrCellDesc'>".spa_text('Errors should be reported to Simple:Press support as they may effect the proper behaviour of your forum')."</td>";

			echo "<td class='spaErrWarning spaErrCell'><b>".spa_text('Warning')."</b></td>";
			echo "<td class='spaErrCellDesc'>".spa_text('Warnings suggest a code conflict of some type that should be investigated but which will not stop Simple:Press execution')."</td>";

			echo "<td class='spaErrNotice spaErrCell'><b>".spa_text('Notice')."</b></td>";
			echo "<td class='spaErrCellDesc'>".spa_text('Notices are generally non-important and have no effect on Simple:Press execution. We make every effort to clear these when we are informed of them')."</td>";

			echo "<td class='spaErrStrict spaErrCell'><b>".spa_text('Strict')."</b></td>";
			echo "<td class='spaErrCellDesc'>".spa_text('If you receive any Strict entries they are non-urgent but please inform Simple:Press support so we can deal with them')."</td>";

			echo "</tr></table><p>&nbsp;</p>";

			if(!$sflog) {
				echo '<p>'.spa_text('There are no Error Log Entries').'</p>';
			} else {

				echo "<table class='form-table'><tr>";
				echo "<th>".spa_text('Date')."</th>";
				echo "<th>#</th>";
				echo "<th>".spa_text('Type')."</th>";
				echo "<th>".spa_text('Description')."</th>";
				echo "</tr>";

				foreach ($sflog as $log)
				{
					echo "<tr>";
					echo "<td class='sferror ".$log['error_cat']."'>".sp_date('d', $log['error_date']).'<br />'.sp_date('t', $log['error_date'])."</td>";
					echo "<td class='sferror ".$log['error_cat']."'>".$log['error_count']."</td>";
					echo "<td class='sferror ".$log['error_cat']."'>".$log['error_type']."</td>";
					echo "<td class='sferror ".$log['error_cat']."'>".$log['error_text']."</td>";
					echo "</tr>";
				}
				echo '</table>';
			}
		spa_paint_close_fieldset();
		do_action('sph_toolbox_error_panel');
	spa_paint_close_tab();

    $ahahURL = SFHOMEURL."index.php?sp_ahah=toolbox-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=sfclearlog";
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfclearlog" name="sfclearlog">
	<?php echo sp_create_nonce('forum-adminform_clearlog'); ?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Empty Error Log'); ?>" />
	</div>
	</form>

<?php
}
?>