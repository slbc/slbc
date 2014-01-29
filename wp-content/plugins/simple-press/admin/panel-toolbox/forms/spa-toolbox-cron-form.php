<?php
/*
Simple:Press
Admin Toolbox Cron Inspector Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_toolbox_cron_form() {
    $ahahURL = SFHOMEURL."index.php?sp_ahah=toolbox-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=cron";
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfcronform', 'sfcron');
});
</script>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfcronform" name="sfcronform">
	<?php echo sp_create_nonce('forum-adminform_cron'); ?>
<?php
   	$cronData = spa_get_cron_data();

	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Toolbox')." - ".spa_text('CRON Inspector'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('CRON Schedules'), false, '', false);
?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php spa_etext('Name'); ?></th>
                            <th style='text-align:center'><?php spa_etext('Description'); ?></th>
                            <th style='text-align:center'><?php spa_etext('Interval'); ?></th>
                        </tr>
                    </thead>
<?php
                    $class = '';
                    foreach ($cronData->schedules as $name => $schedule) {
?>
                    <tbody>
                        <tr <?php echo $class; ?>>
                            <td><?php echo $name; ?></td>
                            <td style='text-align:center'><?php echo $schedule['display']; ?></td>
                            <td style='text-align:center'><?php echo $schedule['interval']; ?></td>
                        </tr>
<?php
                       $class = (empty($class)) ? 'class="alternate"' : '';
                    }
?>
                    </tbody>
                </table>
<?php
			spa_paint_close_fieldset(false);

			spa_paint_open_fieldset(spa_text('Active CRON'), false, '', false);
 ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php spa_etext('Next Run (date)'); ?></th>
                            <th style='text-align:center'><?php spa_etext('Next Run (timestamp)'); ?></th>
                            <th style='text-align:center'><?php spa_etext('Schedule'); ?></th>
                            <th style='text-align:center'><?php spa_etext('Hook'); ?></th>
                            <th style='text-align:center'><?php spa_etext('Arguments'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                    $class = '';
                    foreach ($cronData->cron as $time => $cron) {
                        foreach ($cron as $hook => $items) {
                            foreach ($items as $item) {
?>
                                <tr <?php echo $class; ?>>
                                    <td><?php echo $item['date']; ?></td>
                                    <td style='text-align:center'><?php echo $time; ?></td>
                                    <td style='text-align:center'>
<?php
                                        if ($item['schedule']) {
        								    echo $cronData->schedules[$item['schedule']]['display'];
                                        } else {
        								    spa_etext('One Time');
        								}
?>
                                    </td>
                                    <td style='text-align:center'>
<?php
                                        $sph = strncmp('sph_', $hook, 4 );
                                        if ($sph === 0) echo '<b>';
                                        echo $hook;
                                        if ($sph === 0) echo '</b>';
?>
                                    </td>
                                    <td style='text-align:center'>
<?php
                                        if (count($item['args']) > 0) {
        									foreach($item['args'] as $arg => $value) {
        										echo $arg.':'.$value.'<br />';
                                            }
                                        }
?>
                                    </td>
                                </tr>
<?php
                                $class = (empty($class)) ? 'class="alternate"' : '';
                            }
                        }
                    }
?>
                    </tbody>
                </table>
<?php
  			spa_paint_close_fieldset(false);
		spa_paint_close_panel();

		do_action('sph_toolbox_top_cron_panel');
	spa_paint_close_tab();

	spa_paint_open_tab(spa_text('Toolbox')." - ".spa_text('CRON Update'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Add CRON'), true, 'cron-add', true);
				spa_paint_input(spa_text('Next Run Timestamp'), 'add-timestamp', '');
				spa_paint_input(spa_text('Interval'), 'add-interval', '');
				spa_paint_input(spa_text('Hook'), 'add-hook', '');
				spa_paint_input(spa_text('Arguments'), 'add-args', '');
  			spa_paint_close_fieldset(true);

			spa_paint_open_fieldset(spa_text('Run CRON'), true, 'cron-run', true);
				spa_paint_input(spa_text('Hook to run'), 'run-hook', '');
  			spa_paint_close_fieldset(true);
		spa_paint_close_panel();

		do_action('sph_toolbox_left_cron_panel');

		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Delete CRON'), true, 'cron-delete', true);
				spa_paint_input(spa_text('Next Run Timestamp'), 'del-timestamp', '');
				spa_paint_input(spa_text('Hook'), 'del-hook', '');
				spa_paint_input(spa_text('Arguments'), 'del-args', '');
  			spa_paint_close_fieldset(true);
		spa_paint_close_panel();

		do_action('sph_toolbox_right_cron_panel');
	spa_paint_close_tab();
?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update CRON'); ?>" />
	</div>
	</form>
<?php
}
?>