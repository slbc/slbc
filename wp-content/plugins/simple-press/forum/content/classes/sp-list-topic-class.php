<?php
/*
Simple:Press
List Topic Class
$LastChangedDate: 2013-09-26 07:23:35 -0700 (Thu, 26 Sep 2013) $
$Rev: 10746 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	Returns simplified but rich data based upon the Topic IDs passed in
#	Intended for simple listings
#
#	Version: 5.0
#
# ==========================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_has_list()
#	sp_loop_list()
#	sp_the_list()
#
#	List (Topic Loop) functions for ListView data objects
#
#	Instantiate spListView	All arguments are optional but 1 of the first two are required
#
#	Pass:	$topicIds:	Pass an array of TOPIC ids to specifically use in the list
#			$count:		Optional count of how many rows to return (will also pad IDs)
#			$group:		Boolean: Group/Order the results into forums (Default true)
#			$forumIds:	Optional array of FORUM ids to filter the topic selection by
#			$popup:		New post list only - whether inline of in a popup
#
#	Returns a data object based upon the topic ids
#
#	IMPORTANT NOTES:
#
#	* If NO topic Ids are passed and a count of zero is passed - no data is returned.
#	* If topic Ids are passed with a count higher than the ids count then the object
#	will be padded to include the most recent topics updated as well as the ids passed in
#	* If forum Ids are passed in they will be used to filter the selection of topic Ids
#	to use in the returned data but will NOT verify that any topic Ids also passed in
#	belong within those forums.
#
# --------------------------------------------------------------------------------------

function sp_has_list() {
	global $list, $spListView;
	return $spListView->sp_has_list();
}

function sp_loop_list() {
	global $spListView;
	return $spListView->sp_loop_list();
}

function sp_the_list() {
	global $spListView, $spThisListTopic;
	$spThisListTopic = $spListView->sp_the_list();
}

# --------------------------------------------------------------------------------------


# ==========================================================================================
#
#	Topic List. Topic Listing Class
#
# ==========================================================================================

class spTopicList {
	# Forum View DB query result set
	var $listData = array();

	# Topic single row object
	var $topicData = '';

	# Internal counter
	var $currentTopic = 0;

	# Count of topic records
	var $listCount = 0;

	# Whether inline or popup (new posts only)
	var $popup = 1;

	# Run in class instantiation - populates data
	function __construct($topicIds='', $count=0, $group=true, $forumIds='', $firstPost=0, $popup=1) {
		$this->listData = $this->sp_listview_query($topicIds, $count, $group, $forumIds, $firstPost, $popup);
		sp_display_inspector('tlv_spTopicListView', $this->listData);
	}

	# True if there are Topic records
	function sp_has_list() {
		if (!empty($this->listData)) {
			$this->listCount = count($this->listData);
			reset($this->listData);
			return true;
		} else {
			return false;
		}
	}

	# Loop control on Topic records
	function sp_loop_list() {
		if ($this->currentTopic > 0) do_action_ref_array('sph_after_list', array(&$this));
		$this->currentTopic++;
		if ($this->currentTopic <= $this->listCount) {
			do_action_ref_array('sph_before_list', array(&$this));
			return true;
		} else {
			$this->currentTopic = 0;
			$this->listCount = 0;
			unset($this->listData);
			return false;
		}
	}

	# Sets array pointer and returns current Topic data
	function sp_the_list() {
		$this->topicData = current($this->listData);
		sp_display_inspector('tlv_spThisListTopic', $this->topicData);
		next($this->listData);
		return $this->topicData;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_listview_query()
	#	Builds the data structure for the Listview data object
	#
	# --------------------------------------------------------------------------------------
	function sp_listview_query($topicIds, $count, $group, $forumIds, $firstPost, $popup) {
		global $spThisUser, $spGlobals;

		# If no topic ids and no count then nothjing to do - return empty
		if (empty($topicIds) && $count == 0) return;

		# set popup flag for new posts
		$this->popup = $popup;

		# Do we have enough topic ids to satisfy count?
		if (empty($topicIds) || ($count != 0 && count($topicIds) < $count)) $topicIds = $this->sp_listview_populate_topicids($topicIds, $forumIds, $count);

		# Do we havwe too many topic ids?
		if ($topicIds && ($count != 0 && count($topicIds) > $count)) $topicIds = array_slice($topicIds, 0, $count, true);

		if (empty($topicIds)) return;

		# Construct the main WHERE clause and then main query
		$where = SFTOPICS.'.topic_id IN ('.implode(',', $topicIds).')' ;

		if ($group) {
			$orderby = 'group_seq, forum_seq, '.SFTOPICS.'.post_id DESC';
		} else {
			$orderby = SFTOPICS.'.post_id DESC';
		}

		$spdb = new spdbComplex;
			$spdb->table		= SFTOPICS;
			$spdb->fields		= SFTOPICS.'.forum_id, forum_name, forum_slug, forum_disabled, '.SFTOPICS.'.topic_id, topic_name, topic_slug, topic_icon, topic_icon_new, '.SFTOPICS.'.post_count,
								'.SFTOPICS.'.post_id, post_status, post_index, '.spdb_zone_datetime('post_date').',
								guest_name, '.SFPOSTS.'.user_id, post_content, display_name';
			$spdb->join			= array(SFFORUMS.' ON '.SFFORUMS.'.forum_id = '.SFTOPICS.'.forum_id',
										SFGROUPS.' ON '.SFGROUPS.'.group_id = '.SFFORUMS.'.group_id',
										SFPOSTS.' ON '.SFPOSTS.'.post_id = '.SFTOPICS.'.post_id');
			$spdb->left_join	= array(SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFPOSTS.'.user_id');
			$spdb->where		= $where;
			$spdb->orderby		= $orderby;
		$spdb = apply_filters('sph_topic_list_query', $spdb, $this);

		$records = $spdb->select();

		# add filters where required plus extra data
		# And the new array
		$list = array();

		if ($records) {
			# check if all forum ids are the same
			$x = current($records);
			$f = $x->forum_id;
			$single = 1;
			foreach($records as $r) {
				if($r->forum_id != $f) $single = 0;
			}
			reset($records);

			$new = '';
			$first = '';

			# Now we can grab the supplementary post records where there may be new posts...
			if ($spThisUser->member) $new = $this->sp_listview_populate_newposts($topicIds);

			# go and grab the first post info if desired
			if ($firstPost) $first = $this->sp_listview_populate_firstposts($topicIds);

			# Some values we need
			# How many topics to a page?
			$ppaged = $spGlobals['display']['posts']['perpage'];
			if (empty($ppaged) || $ppaged == 0) $ppaged = 20;
			# establish topic sort order
			$order = 'ASC'; # default
			if ($spGlobals['display']['posts']['sortdesc']) $order = 'DESC'; # global override
			$listPos = 1;

			foreach ($records as $r) {
				$show = true;
				# can the user see this forum?
				if (!sp_can_view($r->forum_id, 'topic-title')) $show = false;
				# if in moderattion can this user approve posts?
				if ($r->post_status != 0 && !sp_get_auth('moderate_posts', $r->forum_id)) $show = false;

				if ($show) {
					$t = $r->topic_id;
                    $list[$t] = new stdClass();
					$list[$t]->forum_id 		= $r->forum_id;
					$list[$t]->forum_name 		= sp_filter_title_display($r->forum_name);
					$list[$t]->forum_disabled   = $r->forum_disabled;
					$list[$t]->forum_permalink	= sp_build_url($r->forum_slug, '', 1, 0);
					$list[$t]->topic_id 		= $r->topic_id;
					$list[$t]->topic_name 		= sp_filter_title_display($r->topic_name);
					$list[$t]->topic_permalink	= sp_build_url($r->forum_slug, $r->topic_slug, 1, 0);
					$list[$t]->topic_icon		= sanitize_file_name($r->topic_icon);
					$list[$t]->topic_icon_new	= sanitize_file_name($r->topic_icon_new);
					$list[$t]->post_count 		= $r->post_count;
					$list[$t]->post_id 			= $r->post_id;
					$list[$t]->post_status 		= $r->post_status;
					$list[$t]->post_date 		= $r->post_date;
					$list[$t]->user_id	 		= $r->user_id;
					$list[$t]->guest_name	 	= sp_filter_name_display($r->guest_name);
					$list[$t]->display_name 	= sp_filter_name_display($r->display_name);
					if (sp_can_view($r->forum_id, 'post-content', $spThisUser->ID, $r->user_id)) {
						$list[$t]->post_tip = ($r->post_status) ? sp_text('Post awaiting moderation') : sp_filter_tooltip_display($r->post_content, $r->post_status);
					} else {
						$list[$t]->post_tip = '';
					}
					$list[$t]->list_position	= $listPos;

					if (empty($r->display_name)) $list[$t]->display_name = $list[$t]->guest_name;

					# Lastly determine the page for the post permalink
					if ($order == 'ASC') {
						$page = $r->post_index / $ppaged;
						if (!is_int($page)) $page = intval($page+1);
					} else {
						$page = $r->post_count - $r->post_index;
						$page = $page / $ppaged;
						$page = intval($page+1);
					}
					$r->page = $page;
					$list[$t]->post_permalink = sp_build_url($r->forum_slug, $r->topic_slug, $r->page, $r->post_id, $r->post_index);

					$list[$t]->single_forum = $single;

					# add in any new post details if they exist
					if (!empty($new) && array_key_exists($t, $new)) {
						$list[$t]->new_post_count 			= $new[$t]->new_post_count;
						$list[$t]->new_post_post_id			= $new[$t]->new_post_post_id;
						$list[$t]->new_post_post_index		= $new[$t]->new_post_post_index;
						$list[$t]->new_post_post_date		= $new[$t]->new_post_post_date;
						$list[$t]->new_post_user_id			= $new[$t]->new_post_user_id;
						$list[$t]->new_post_display_name	= $new[$t]->new_post_display_name;
						$list[$t]->new_post_guest_name		= $new[$t]->new_post_guest_name;
						$list[$t]->new_post_permalink 		= sp_build_url($r->forum_slug, $r->topic_slug, 0, $new[$t]->new_post_post_id, $new[$t]->new_post_post_index);
						if (empty($new[$t]->new_post_display_name)) $list[$t]->new_post_display_name = $new[$t]->new_post_guest_name;
					}

                    # add the first post info if desired
                    if ($firstPost) {
    					$list[$t]->first_post_permalink = sp_build_url($r->forum_slug, $r->topic_slug, 0, $first[$t]->post_id, 1);
    					$list[$t]->first_post_date 		= $first[$t]->post_date;
    					$list[$t]->first_user_id	 	= $first[$t]->user_id;
    					$list[$t]->first_guest_name	 	= sp_filter_name_display($first[$t]->guest_name);
    					$list[$t]->first_display_name 	= sp_filter_name_display($first[$t]->display_name);
    					if (sp_can_view($r->forum_id, 'post-content', $spThisUser->ID, $first[$t]->user_id)) {
    						$list[$t]->first_post_tip = ($first[$t]->post_status) ? sp_text('Post awaiting moderation') : sp_filter_tooltip_display($first[$t]->post_content, $first[$t]->post_status);
    					} else {
    						$list[$t]->first_post_tip = '';
    					}

    					if (empty($list[$t]->first_display_name)) $list[$t]->first_display_name = $list[$t]->first_guest_name;
                    }

					$list[$t] = apply_filters('sph_topic_list_record', $list[$t], $r);

					$listPos++;
				}
			}
			unset($records);
			unset($new);
			unset($first);
		}
		return $list;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_listview_populate_newposts()
	#	Adds first new posts data into the result
	#
	# --------------------------------------------------------------------------------------
	function sp_listview_populate_newposts($topicIds) {
		global $spThisUser;

		$newList = array();

		# First filter topics by those in the users new post list
		$newTopicIds = array();
		foreach ($topicIds as $topic) {
			if (sp_is_in_users_newposts($topic)) $newTopicIds[] = $topic;
		}
		if ($newTopicIds) {
			# construct the query - need to add in sfwaiting for admins
			$where = SFPOSTS.'.topic_id IN ('.implode(',', $newTopicIds).') AND (post_date > "'.spdb_zone_mysql_checkdate($spThisUser->lastvisit).'")';
			if ($spThisUser->admin || $spThisUser->moderator) {
				$wPosts = spdb_select('col', 'SELECT post_id FROM '.SFWAITING);
				if ($wPosts) $where.= ' OR ('.SFPOSTS.'.post_id IN ('.implode(",", $wPosts).'))';
			}

			$spdb = new spdbComplex;
				$spdb->table		= SFPOSTS;
				$spdb->fields		= SFPOSTS.'.topic_id, '.SFPOSTS.'.post_id, post_index, '.spdb_zone_datetime('post_date').',
									guest_name, '.SFPOSTS.'.user_id, display_name, post_count-post_index+1 AS new_post_count';
				$spdb->left_join	= array(SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFPOSTS.'.user_id');
				$spdb->join			= array(SFTOPICS.' ON '.SFPOSTS.'.topic_id = '.SFTOPICS.'.topic_id');
				$spdb->where		= $where;
				$spdb->orderby		= 'topic_id, post_id';
			$spdb = apply_filters('sph_listview_newposts_query', $spdb, $this);
			$postrecords = $spdb->select();

			if ($postrecords) {
				$cTopic = 0;
				foreach ($postrecords as $p) {
					if ($p->topic_id != $cTopic) {
						$cTopic = $p->topic_id;
                        $newList[$cTopic] = new stdClass();
						$newList[$cTopic]->topic_id					= $cTopic;
						$newList[$cTopic]->new_post_count 			= $p->new_post_count;
						$newList[$cTopic]->new_post_post_id			= $p->post_id;
						$newList[$cTopic]->new_post_post_index		= $p->post_index;
						$newList[$cTopic]->new_post_post_date		= $p->post_date;
						$newList[$cTopic]->new_post_user_id			= $p->user_id;
						$newList[$cTopic]->new_post_display_name	= sp_filter_name_display($p->display_name);
						$newList[$cTopic]->new_post_guest_name		= sp_filter_name_display($p->guest_name);
					}
				}
			}
		}
		return $newList;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_listview_populate_firstposts()
	#	Populates the first post ina  topic if requested for inclusion
	#
	# --------------------------------------------------------------------------------------
	function sp_listview_populate_firstposts($topicIds) {
		$first = array();

		$spdb = new spdbComplex;
			$spdb->table		= SFPOSTS;
			$spdb->fields		= 'post_id, topic_id, '.spdb_zone_datetime('post_date').', '.SFPOSTS.'.user_id, guest_name, post_content, post_status, display_name';
			$spdb->left_join	= array(SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFPOSTS.'.user_id');
			$spdb->where		= 'topic_id IN ('.implode(',', $topicIds).') AND post_index=1';
		$spdb = apply_filters('sph_listview_firstposts_query', $spdb, $this);
		$postrecords = $spdb->select();

		if($postrecords) {
			foreach ($postrecords as $p) {
				$cTopic = $p->topic_id;
                $first[$cTopic] = new stdClass();
				$first[$cTopic]->topic_id		= $cTopic;
				$first[$cTopic]->post_id		= $p->post_id;
				$first[$cTopic]->post_date		= $p->post_date;
				$first[$cTopic]->user_id		= $p->user_id;
				$first[$cTopic]->post_status	= $p->post_status;
				$first[$cTopic]->display_name	= $p->display_name;
				$first[$cTopic]->guest_name		= $p->guest_name;
				$first[$cTopic]->post_content	= $p->post_content;
			}
		}
		return $first;
	}

	# --------------------------------------------------------------------------------------
	#
	#	sp_listview_populate_topicids()
	#	Populates the topic id list to satisfy required count
	#
	# --------------------------------------------------------------------------------------
	function sp_listview_populate_topicids($topicIds, $forumIds, $count) {
		global $spGlobals, $spThisUser;

		if (empty($topicIds)) $topicIds=array();
		$w = array();
		$needJoin = false;

		if(!empty($forumIds)) {
			if(!is_array($forumIds)) {
				$forumIds = explode(',', $forumIds);
			}
		} else {
			$forumIds = sp_user_visible_forums();
		}

        if (!empty($forumIds)) {
    		foreach ($forumIds as $f) {
    			if (sp_can_view($f, 'topic-title')) {
    				$t = SFTOPICS.".forum_id=$f";
    				if (sp_get_auth('moderate_posts', $f)==false) {
    					$t.= ' AND post_status=0';
    					$needJoin = true;
    				}
    				$w[] = $t;
    			}
    		}
        }

		# if NO forums ids then we should go no further
		if (empty($w)) {
			return $topicIds;
		}

		# Next construct the WHERE clause
		$where = '';
		for ($x=0; $x<count($w); $x++) {
			$where.= "($w[$x])";
			if ($x < count($w)-1) $where.= ' OR ';
		}

		# finally construct query and get base data
		$spdb = new spdbComplex;
			$spdb->table	= SFTOPICS;
			$spdb->distinct	= true;
			if ($needJoin) {
				$spdb->join	= array(SFPOSTS.' ON '.SFTOPICS.'.post_id = '.SFPOSTS.'.post_id');
			}
			$spdb->fields	= SFTOPICS.'.topic_id';
			$spdb->where	= $where;
			$spdb->orderby	= SFTOPICS.'.post_id DESC';
			$spdb->limits	= $count;
		$r = $spdb->select('col', ARRAY_N);

		if ($r) {
			foreach ($r as $t) {
				if (!in_array($t, $topicIds)) $topicIds[] = $t;
			}
		}

		# Only process if there are topics defined
		if (empty($topicIds)) return '';

		unset($r);
		unset($w);

		# Now make sure we just have the required number
		$topicIds = array_slice($topicIds, 0, $count, true);

		return $topicIds;
	}
}
?>