<?php
/*
Simple:Press
User Group Specials
$LastChangedDate: 2013-03-02 10:15:32 -0700 (Sat, 02 Mar 2013) $
$Rev: 9944 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ahah_support();

# Check Whether User Can Manage User Groups
if (!sp_current_user_can('SPF Manage User Groups')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

if (isset($_GET['ug'])) {
	$usergroup_id = sp_esc_int($_GET['ug']);
    if ($usergroup_id == 0) {
    	$members = spdb_select('set', '
    		SELECT '.SFMEMBERS.'.user_id, display_name
    		FROM '.SFMEMBERS.'
    		WHERE user_id NOT IN (SELECT user_id FROM '.SFMEMBERSHIPS.') AND admin=0
    		ORDER BY display_name'
    	);
        $text1 = spa_text('Members With No Memberships');
        $text2 = spa_text('All members have a usergroup membership.');
    } else {
    	$sql = "SELECT ".SFMEMBERSHIPS.".user_id, display_name
    			FROM ".SFMEMBERSHIPS."
    			JOIN ".SFMEMBERS." ON ".SFMEMBERS.".user_id = ".SFMEMBERSHIPS.".user_id
    			WHERE ".SFMEMBERSHIPS.".usergroup_id=".$usergroup_id."
    			ORDER BY display_name";
    	$members = spdb_select('set', $sql);
        $text1 = spa_text('User Group Members');
        $text2 = spa_text('No Members in this User Group.');
    }
	echo spa_display_member_roll($members, $text1, $text2);
	die();
}

function spa_display_member_roll($members, $text1, $text2) {
	$out = '';
	$cap = '';
	$first = true;
	$out.= '<fieldset class="sfsubfieldset">';
    $out.= '<legend>'.$text1.'</legend>';
	if ($members) {
	    $out.= '<p><b>'.count($members).' '.spa_text('member(s) in this user group').'</b></p>';
		for ($x=0; $x<count($members); $x++) {
			if (strncasecmp($members[$x]->display_name, $cap, 1) != 0) {
				if (!$first) {
					$out.= '</ul>';
				}

				$cap = substr($members[$x]->display_name, 0, 2);
				if (function_exists('mb_strwidth')) {
					if (mb_strwidth($cap) == 2) $cap = substr($cap, 0, 1);
				} else {
					$cap = substr($cap, 0, 1);
				}

				$out.= '<p style="clear:both;"></p><hr /><h4>'.strtoupper($cap).'</h4>';
				$out.= '<ul class="memberlist">';
				$first = false;
			}
			$out.= '<li>'.sp_filter_name_display($members[$x]->display_name).'</li>';
		}
		$out.= '</ul>';
	} else {
		$out.= $text2;
	}
    $out.= '</fieldset>';

	return $out;
}

?>