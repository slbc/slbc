<?php
/*
Simple:Press
Admin Users Members Form
$LastChangedDate: 2013-08-22 13:13:08 -0700 (Thu, 22 Aug 2013) $
$Rev: 10564 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_users_members_form() {
	spa_paint_options_init();

	spa_paint_open_tab(spa_text('Users').' - '.spa_text('Member Information'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Members Info'), 'true', 'users-info', false);
				if (isset($_POST['usersearch'])) $term = sp_filter_title_save(trim($_POST['usersearch'])); else $term = '';
				if (isset($_GET['userspage'])) $page = sp_esc_int($_GET['userspage']); else $page = '';
				$user_search = new SP_User_Search($term, $page);
?>
				<form id="posts-filter" name="searchfilter" action="<?php echo SFADMINUSER.'&amp;form=members'; ?>" method="post">
					<div class="tablenav">
						<?php if ($user_search->results_are_paged()) : ?>
							<div class="tablenav-pages">
<?php
								$args = array();
								if (!empty($user_search->search_term)) $args['usersearch'] = urlencode($user_search->search_term);
								$user_search->paging_text = paginate_links( array(
									'total' => ceil($user_search->total_users_for_query / $user_search->users_per_page),
									'current' => $user_search->page,
									'base' => 'admin.php?page=simple-press/admin/panel-users/spa-users.php&amp;form=members&amp;%_%',
									'format' => 'userspage=%#%',
									'add_args' => $args) );
								echo $user_search->page_links();
?>
							</div>
						<?php endif; ?>
						<div>
							<label class="hidden" for="post-search-input"><?php spa_etext('Search Members'); ?>:</label>
							<input type="text" class="sfacontrol" id="post-search-input" name="usersearch" value="<?php echo esc_attr($user_search->search_term); ?>" />
							<input type="button" class="button-primary" onclick="javascript:document.searchfilter.submit();" id="sfusersearch" name="sfusersearch" value="<?php spa_etext('Search Members'); ?>" />
			 			</div>
						<br class="clear" />
					</div>
					<br class="clear" />
				</form>
				<?php if ($user_search->get_results()) : ?>
					<?php if ($user_search->is_search()) : ?>
						<p><a href="<?php echo SFADMINUSER; ?>"><?php echo sprintf(spa_text('%s Back to All Members'), '&laquo;'); ?></a></p>
					<?php endif; ?>
					<table class="sfsubtable">
						<thead>
							<tr class="thead">
								<th width="10"><?php spa_etext('ID'); ?></th>
								<th align="left"><?php spa_etext('Login Name') ?></th>
								<th align="left"><?php spa_etext('SP Display Name') ?></th>
								<th align="center"><?php spa_etext('First Post') ?></th>
								<th align="center"><?php spa_etext('Last Post') ?></th>
								<th align="center" class="num"><?php spa_etext('Posts') ?></th>
								<th align="center"><?php spa_etext('Last Visit') ?></th>
								<th><?php spa_etext('Memberships') ?></th>
								<th><?php spa_etext('Rank') ?></th>
								<th align="center" style="width:20"><?php spa_etext('Actions') ?></th>
							</tr>
						</thead>
						<tbody id="users" class="list:user user-list">
<?php
							$style = '';

                            # grab user post/page counts
                            $users = $user_search->get_results();
                           	$userlist = implode(',', array_map('absint', $users));
                            $result = spdb_select('set', "SELECT post_author, COUNT(*) FROM ".SFWPPOSTS." WHERE (post_status = 'publish' OR post_status = 'private') AND post_author IN ($userlist) GROUP BY post_author", ARRAY_N );
                        	$post_counts = array();
                        	foreach ($result as $row) {
                        		$post_counts[$row[0]] = $row[1];
                        	}

                        	foreach ($users as $id) {
                        		if (!isset($post_counts[$id])) $post_counts[$id] = 0;
                        	}

                            # output users
            				foreach ($users as $userid) {
								$data = spa_get_members_info($userid);
                                if ($data) {
?>
    								<tr id="user-delete-<?php echo $userid; ?>">
    									<td><?php echo($userid); ?></td>
    									<td align="left"><?php echo $data['login']; ?></td>
                                        <?php $displayname = (!empty($data['display_name'])) ? $data['display_name'] : ''; ?>
    									<td align="left"><?php echo sp_filter_name_display($displayname); ?></td>
    									<td align="center"><?php echo $data['first']; ?></td>
    									<td align="center"><?php echo $data['last']; ?></td>
    									<td align="center">
<?php
                                            if ($data['posts'] == -1) {
                                                echo '<img style="vertical-align:top" src="'.SFADMINIMAGES.'sp_UserNoPosts.png" title="'.spa_text('User has not yet visited forum').'" alt="" />';
                                            } else {
                                                echo $data['posts'];
                                            }
?>
                                        </td>
    									<td align="center"><?php echo sp_date('d', $data['lastvisit']); ?></td>
    									<td><?php echo $data['memberships']; ?></td>
    									<td><?php echo $data['rank']; ?></td>
    									<td align="center">
    										<table>
    											<tr>
    												<td>
<?php
                                                        $site = SFHOMEURL.'index.php?sp_ahah=profile&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;action=popup&amp;user=$userid";
                                                        $title = spa_text('Member Profile');
                                                        $position = 'center';
    													echo '<a id="memberprofile'.$userid.'" href="javascript:void(null)" onclick="spjDialogAjax(this, \''.$site.'\', \''.$title.'\', 750, 0, \''.$position.'\');"><img src="'.SFADMINIMAGES.'sp_UserProfile.png" title="'.spa_text('View Member Profile').'" alt="" /></a>';
?>
    												</td>
    												<td>
<?php
    													# check to see if user can delete users before giving option to delete
    													if (current_user_can('delete_user', $userid)) {
    														# if user has wp posts, redirect to wp user delete; otherwise just delete
    														if ($post_counts[$userid] > 0) {
    															$nonce = wp_create_nonce('bulk-users');
    															$url = admin_url('users.php?action=delete&amp;user='.$userid.'&_wpnonce='.$nonce.'&amp;wp_http_referer=admin.php?page=simple-press/admin/panel-users/spa-users.php');
    															echo '<a href="'.$url.'"><img src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete User').'" alt="" />';
    														} else {
                                                        		$msg = esc_js(sp_text('Are you sure you want to delete this WP user (remember, SP users are just WP users)? '));
    	                                                        $site = SFHOMEURL.'index.php?sp_ahah=users&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;action=delete&amp;id=$userid";
    															echo '<a href="javascript: if(confirm(\''.$msg.'\')) {spjDelRow(\''.$site.'\', \'user-delete-'.$userid.'\');}"><img src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete User').'" alt="" /></a>';
    														}
    													}
?>
    												</td>
    											</tr>
    										</table>
    									</td>
    								</tr>
                                <?php } ?>
							<?php } ?>
						</tbody>
					</table>

					<div class="tablenav">
						<?php if ( $user_search->results_are_paged() ) : ?>
							<div class="tablenav-pages"><?php $user_search->page_links(); ?></div>
						<?php endif; ?>
						<br class="clear" />
					</div>
				<?php endif; ?>
<?php
			spa_paint_close_fieldset(false);
		spa_paint_close_panel();
		do_action('sph_users_members_panel');
	spa_paint_close_tab();
}

function spa_get_members_info($userid) {
	$data = sp_get_member_row($userid);
    if (empty($data)) return '';

	$first = spdb_select('row', '
			SELECT '.SFPOSTS.'.forum_id, forum_name, forum_slug, '.SFPOSTS.'.topic_id, topic_name, topic_slug, post_date
			FROM '.SFPOSTS.'
			JOIN '.SFTOPICS.' ON '.SFTOPICS.'.topic_id = '.SFPOSTS.'.topic_id
			JOIN '.SFFORUMS.' ON '.SFFORUMS.'.forum_id = '.SFPOSTS.'.forum_id
			WHERE '.SFPOSTS.".user_id=$userid
			ORDER BY post_date ASC
			LIMIT 1");
	if ($first) {
		$url = '<a href="'.sp_build_url($first->forum_slug, $first->topic_slug, 1, 0).'">'.sp_filter_title_display($first->topic_name).'</a>';
		$data['first'] = sp_filter_title_display($first->forum_name).'<br />'.$url .'<br />'.sp_date('d', $first->post_date);
	} else {
		$data['first'] = spa_text('No Posts');
	}

	$last = spdb_select('row', '
			SELECT '.SFPOSTS.'.forum_id, forum_name, forum_slug, '.SFPOSTS.'.topic_id, topic_name, topic_slug, post_date
			FROM '.SFPOSTS.'
			JOIN '.SFTOPICS.' ON '.SFTOPICS.'.topic_id = '.SFPOSTS.'.topic_id
			JOIN '.SFFORUMS.' ON '.SFFORUMS.'.forum_id = '.SFPOSTS.'.forum_id
			WHERE '.SFPOSTS.".user_id=$userid
			ORDER BY post_date DESC
			LIMIT 1");
	if ($last) {
		$url = '<a href="'.sp_build_url($last->forum_slug, $last->topic_slug, 1, 0).'">'.sp_filter_title_display($last->topic_name).'</a>';
		$data['last'] = sp_filter_title_display($last->forum_name).'<br />'.$url .'<br />'.sp_date('d', $last->post_date);
	} else {
		$data['last'] = spa_text('No posts');
	}

	if ($data['admin']) {
		$user_memberships = 'Admin';
		$status = 'Admin';
		$start = 0;
	} elseif ($data['moderator']) {
		$status = 'Moderator';
		$start = 1;
	} else {
		$status = 'User';
		$start = 1;
	}

	$memberships = spdb_table(SFMEMBERSHIPS, "user_id=$userid", '', '', '', ARRAY_A);
	if ($memberships) {
		foreach ($memberships as $membership) {
			$name = spdb_table(SFUSERGROUPS, 'usergroup_id='.$membership['usergroup_id'], 'usergroup_name');
			if ($start) {
				$user_memberships = $name;
				$start = 0;
			} else {
				$user_memberships.= ', '.$name;
			}
		}
	} elseif ($start) {
		$user_memberships = 'No Memberships';
	}
	$data['memberships'] = $user_memberships;

	$rank = sp_get_user_forum_rank($status, $userid, $data['posts']);
	$data['rank'] = $rank[0]['name'];

    $user = get_userdata($userid);
    $data['login'] = $user->user_login;

	return $data;
}

?>