<?php
/*
Simple:Press
User Class
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
#	User Class
#
# 	Version: 5.0
#	Pass in a user ID. 0 or null denotes a guest
#	Pass in the user login as an alternative
#
#	This class should NOT be instantiated directly. All calls to create a new
#	user object should be routed through the sp_get_user() function to allow
#	for user object caching.
#
#--------------------------------------------------------------------------------------

class spUser {
	public $list = array();

	# ------------------------------------------
	#	spUser_build_list()
	#	Master list of data that is retrieved
	#	from users and usermeta tables along
	#	with the filter to apply
	# ------------------------------------------
	function spUser_build_list() {
		global $spGlobals;

		$this->list = array(
			'user_login' 		=> 'name',
			'user_email' 		=> 'email',
			'user_url'			=> 'url',
			'user_registered' 	=> '',
			'description'		=> 'text',
			'location'			=> 'title',
			'first_name'		=> 'name',
			'last_name'			=> 'name',
			'aim'				=> 'title',
			'yim'				=> 'title',
			'jabber'			=> 'title',
			'msn'				=> 'title',
			'icq'				=> 'title',
			'skype'				=> 'title',
			'facebook'			=> 'title',
			'myspace'			=> 'title',
			'twitter'			=> 'title',
			'linkedin'			=> 'title',
			'youtube'			=> 'title',
			'googleplus'		=> 'title',
			'display_name'		=> 'name',
			'signature'			=> 'signature',
			'sp_change_pw'		=> '',
			'photos'		    => '',
		);

        # allow plugins to add more usermeta class data
        $this->list = apply_filters('sph_user_class_meta', $this->list);
	}

	# ------------------------------------------
	#	spUser_filter()
	#	The display filter calls based upon
	#	the array of user entered data and
	#	filters to apply
	# ------------------------------------------
	function spUser_filter($item, $filter) {
		if(is_array($this->$item)) return $this->$item;
		switch ($filter) {
			case 'title':
				$this->$item = sp_filter_title_display($this->$item);
				break;
			case 'email':
				$this->$item = sp_filter_email_display($this->$item);
				break;
			case 'url':
				$this->$item = sp_filter_url_display($this->$item);
				break;
			case 'text':
				$this->$item = sp_filter_text_display($this->$item);
				break;
			case 'name':
				$this->$item = sp_filter_name_display($this->$item);
				break;
			case 'signature':
				$this->$item = sp_filter_signature_display($this->$item);
				break;
		}
	}

	# ------------------------------------------
	#	spUser
	#	$ident		user id or user login
	#	$current	set to true for $spThisUser
	# ------------------------------------------
	function __construct($ident=0, $current=false) {
		global $spStatus, $spGlobals;

		$id = 0;
		if (is_numeric($ident)) {
			$w = "ID=$ident";
		} elseif ($ident != false) {
			$w = "user_login='$ident'";
		}
		if ($ident) {
			# Users data
			$d = spdb_table(SFUSERS, $w, 'row');
			if ($d) {
				$this->ID = $d->ID;
				$id = $d->ID;
			}
		}

		$this->spUser_build_list();

		if ($id) {
			# Others
			$this->member = true;
			$this->guest = 0;
			$this->guest_name = '';
			$this->guest_email = '';
			$this->offmember = false;
			$this->usertype = 'User';

			# Users data
			foreach ($d as $key => $item) {
				if (array_key_exists($key, $this->list)) {
					$this->$key = $item;
				}
			}
			$this->user_registered = sp_member_registration_to_server_tz($this->user_registered);

			# usermeta data
			$d = spdb_table(SFUSERMETA, "user_id=$id");
			if ($d) {
				foreach( $d as $m) {
					$t = $m->meta_key;
					if (array_key_exists($t, $this->list)) {
						$this->$t = maybe_unserialize($m->meta_value);
					}
				}
			}

			# sfmembers data
			$d = spdb_table(SFMEMBERS, "user_id=$id", 'row');
			#check for ghost user
			if(empty($d)) {
				#create the member
				sp_create_member_data($id);
				$d = spdb_table(SFMEMBERS, "user_id=$id", 'row');
			}
			if ($d) {
				foreach($d as $key => $item) {
					if ($key == 'admin_options' && !empty($item)) {
						$opts = unserialize($item);
						foreach ($opts as $opt => $set) {
							$this->$opt = $set;
						}
					} elseif ($key=='user_options' && !empty($item)) {
						$opts = unserialize($item);
						foreach ($opts as $opt => $set) {
							$this->$opt = $set;
						}
					} elseif ($key == 'lastvisit') {
						$this->lastvisit = $item;
					} else {
						$this->$key = maybe_unserialize($item);
					}
				}
			}

			# Check for new post list size
			if(!isset($this->unreadposts) || empty($this->unreadposts)) {
				$controls = sp_get_option('sfcontrols');
				if(empty($controls['sfunreadposts']) ? $this->unreadposts=50 : $this->unreadposts=$controls['sfunreadposts']);
			}

			# usertype for moderators
			if ($this->moderator) $this->usertype = 'Moderator';

			# check for super admins and make admin a moderator as well
			if ($this->admin || (is_multisite() && is_super_admin($id))) {
				$this->admin = true;
				$this->moderator = true;
				$this->usertype = 'Admin';
				$ins = sp_get_option('spInspect');
				if (!empty($ins) && array_key_exists($id, $ins)) {
					$this->inspect = $ins[$id];
				} else {
					$this->inspect = '';
				}
			}
		} else {
			# some basics for guests
			$this->ID = 0;
			$this->guest = true;
			$this->member = 0;
			$this->admin = false;
			$this->moderator = false;
			$this->display_name = 'guest';
			$this->guest_name = '';
			$this->guest_email = '';
			$this->usertype = 'Guest';
			$this->offmember = sp_check_unlogged_user();
			$this->timezone = 0;
			$this->timezone_string = '';
			$this->posts = 0;
			$this->avatar = '';
			$this->user_email = '';
			$this->auths = sp_get_option('sf_guest_auths');
	        $this->memberships = sp_get_option('sf_guest_memberships');
		}

		# Only perform this last section if forum is operational
		if ($spStatus == 'ok') {
			# Ranking
			$this->rank = sp_get_user_forum_rank($this->usertype, $id, $this->posts);
			$this->special_rank = sp_get_user_special_ranks($id);

			# if no memberships rebuild them and save
			if (empty($this->memberships)) {
				$memberships = array();
				if (!empty($id)) {
					if (!$this->admin) {
						# get the usergroup memberships for the user and save in sfmembers table
						$memberships = sp_get_user_memberships($id);
						sp_update_member_item($id, 'memberships', $memberships);
					}
				} else {
					# user is a guest or unassigned member so get the global permissions from the guest usergroup and save as option
					$value = sp_get_sfmeta('default usergroup', 'sfguests');
					$memberships[] = spdb_table(SFUSERGROUPS, 'usergroup_id='.$value[0]['meta_value'], 'row', '', '', ARRAY_A);
					sp_update_option('sf_guest_memberships', $memberships);
				}
				# put in the data
				$this->memberships = $memberships;
			}
			# if no auths rebuild them and save
			if (empty($this->auths)) {
				$this->auths = sp_rebuild_user_auths($id);
			}
		}

		$this->ip = sp_get_ip();
		$this->trackid = 0;

		# Things to do if user is current user
		if ($current) {
			# Set up editor type
			$spGlobals['editor']=0;
			# for a user...
			if ($this->member && !empty($this->editor)) $spGlobals['editor'] = $this->editor;

			# and if not defined or is for a guest...
			if ($spGlobals['editor'] == 0) {
				$defeditor = sp_get_option('speditor');
				if (!empty($defeditor)) $spGlobals['editor'] = $defeditor;
			}
			# final check to ensure selected editor type is indeed available
			if (($spGlobals['editor'] == 0) ||
				($spGlobals['editor'] == 1 && !defined('RICHTEXT')) ||
				($spGlobals['editor'] == 2 && !defined('HTML')) ||
				($spGlobals['editor'] == 3 && !defined('BBCODE'))) {

				$spGlobals['editor'] = PLAINTEXT;
				if (defined('BBCODE')) 		$spGlobals['editor'] = BBCODE;
				if (defined('HTML')) 		$spGlobals['editor'] = HTML;
				if (defined('RICHTEXT')) 	$spGlobals['editor'] = RICHTEXT;
			}

			# Grab any notices present
			if ($this->guest && !empty($this->guest_email)) {
				$this->user_notices = spdb_table(SFNOTICES, "guest_email='".$this->guest_email."'", '', $order='notice_id');
			} elseif ($this->member && !empty($this->user_email)) {
				$this->user_notices = spdb_table(SFNOTICES, "user_id=".$this->ID, '', $order='notice_id');
			}

			# allow plugins to add items to user class - ONLY for current user ($spThisUser)
            do_action_ref_array('sph_current_user_class', array(&$this));
		}

		# Finally filter the data for display
		foreach ($this->list as $item => $filter) {
			if (property_exists($this, $item)) $this->spUser_filter($item, $filter);
		}

		# allow plugins to add items to user class
        do_action_ref_array('sph_user_class', array(&$this));
	}
}

# ------------------------------------------------------------------
# sp_get_user()
#
# Version: 5.1.3
# The main call to creater a nbew user data object. This routine
# caches users ibnto an array and checks the cache before creating
# a new object
#
#	$userid:	user id to return
#	$current:	set to true if user is the current system user
# ------------------------------------------------------------------
function sp_get_user($userid=0, $current=false) {
	static $USERS = array();
	if(!array_key_exists($userid, $USERS)) {
		$USERS[$userid] = new spUser($userid, $current);
	}
	return (object) $USERS[$userid];
}

?>