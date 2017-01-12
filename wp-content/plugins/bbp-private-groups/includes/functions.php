<?php

add_action('bbp_template_redirect', 'private_group_enforce_permissions', 1);
add_filter('protected_title_format', 'pg_remove_protected_title');
add_filter('private_title_format', 'pg_remove_private_title');

add_filter('bbp_get_forum_freshness_link', 'custom_freshness_link' );
//add assign role to register, 
add_action ('bbp_user_register', 'pg_role_group') ;
//and to wp-login
add_action('wp_login', 'pg_assign_role_on_login', 10, 2);


global $rpg_topic_permissions ;
if (!empty ($rpg_topic_permissions['activate']) ) {
add_filter ( 'bbp_current_user_can_access_create_topic_form', 'pg_current_user_can_access_create_topic_form') ;
add_filter ( 'bbp_current_user_can_access_create_reply_form', 'pg_current_user_can_access_create_reply_form') ;
}


// add filters for bbp-style-pack shortcodes if stylepack is running
    add_filter('bsp_display_topic_index_query', 'pg_display_topic_index_query_filter');
	add_filter('bsp_display_forum_query', 'pg_display_forum_query_filter');
	add_filter('bsp_activity_widget', 'pg_latest_activity_forum_query_filter') ;
	
// add filters for bbp-shortcodes plugin shortcodes if plugin is running
    add_filter('asc_display_topic_index_query', 'pg_display_topic_index_query_filter');
	add_filter('asc_display_forum_query', 'pg_display_forum_query_filter');

	
	

/*
 * Check if the current user has rights to view the given Post ID
 * Some of this core code is from the work of Aleksandar Adamovic in his Tehnik BBPress Permissions - thanks !
 * 
 */
 
 
function private_groups_check_can_user_view_post() {
//uses $post_id and $post_type to get the forum ($forum_id) that the post belongs to
    global $wp_query;

    // Get Forum Id for the current post    
    $post_id = $wp_query->post->ID;
    $post_type = $wp_query->get('post_type');
	
	if (bbp_is_topic_super_sticky($post_id)) return true;
	
	
    $forum_id = private_groups_get_forum_id_from_post_id($post_id, $post_type);
//then call the function that checks if the user can view this forum, and hence this post
    if (private_groups_can_user_view_post_id($forum_id))
        return true;
}



/**
 * Use the given query to determine which forums the user has access to. 
 * 
 * returns: an array of post IDs which user has access to.
 */
function private_groups_get_permitted_post_ids($post_query) {
    
    //Init the Array which will hold our list of allowed posts
    $allowed_posts = array();
    

    //Loop through all the posts
    while ($post_query->have_posts()) :
        $post_query->the_post();
		//Get the Post ID and post type
        $post_id = $post_query->post->ID;
		$post_type = $post_query->post->post_type;
        //Get the Forum ID based on Post Type (Reply, Topic, Forum)
        $forum_id = private_groups_get_forum_id_from_post_id($post_id, $post_type);
		//Check if User has permissions to view this Post ID
		//by calling the function that checks if the user can view this forum, and hence this post
        if (private_groups_can_user_view_post_id($forum_id)) {
		
			//User can view this post - add it to the allowed array
            array_push($allowed_posts, $post_id);
        }

    endwhile;

    //Return the list		
    return $allowed_posts;
}




/*
 * Returns the bbPress Forum ID from given Post ID and Post Type
 * 
 * returns: bbPRess Forum ID
 */
function private_groups_get_forum_id_from_post_id($post_id, $post_type) {
    $forum_id = 0;

    // Check post type
    switch ($post_type) {
        // Forum
        case bbp_get_forum_post_type() :
            $forum_id = bbp_get_forum_id($post_id);
            break;

        // Topic
        case bbp_get_topic_post_type() :
            $forum_id = bbp_get_topic_forum_id($post_id);
            break;

        // Reply
        case bbp_get_reply_post_type() :
            $forum_id = bbp_get_reply_forum_id($post_id);
            break;
    }

    return $forum_id;
}

//enforce permission to ensure users only see permitted posts
function private_group_enforce_permissions() {
    global $rpg_settingsf ;
	// Bail if not viewing a bbPress item
    if (!is_bbpress())
        return;

    // Bail if not viewing a single item or if user has caps
    if (!is_singular() || bbp_is_user_keymaster() )
        
		return;

    if (!private_groups_check_can_user_view_post()) {
        if (!is_user_logged_in()) {
			if($rpg_settingsf['redirect_page2']) {
				$link=$rpg_settingsf['redirect_page2'] ;
				//header( "Location: $link" );
				wp_redirect($link);
				exit;
			}
			else {		
				auth_redirect();
			}
		}
		else {
			if($rpg_settingsf['redirect_page1']) {
				$link=$rpg_settingsf['redirect_page1'] ;
				//header( "Location: $link" );
				wp_redirect($link);
				exit;
			}	
			else {
				bbp_set_404();
			}
  	
		}
	}
}




function pg_remove_private_title($title) {
	global $rpg_settingsg ;
	if (isset( $rpg_settingsg['activate_remove_private_prefix']) ) {
	return '%s';
	}
		else {
		Return $title ;
		}
}

function pg_remove_protected_title($title) {
	global $rpg_settingsg ;
	if (isset ($rpg_settingsg['activate_remove_private_prefix'])  ) {
	return '%s';
	}
		else {
		Return $title ;
		}
}


function custom_freshness_link( $forum_id = 0 ) {
global $rpg_settingsf ;
		$forum_id  = bbp_get_forum_id( $forum_id );
		$active_id = bbp_get_forum_last_active_id( $forum_id );
		$link_url  = $title = '';
		$forum_title= bbp_get_forum_title ($forum_id) ;

		if ( empty( $active_id ) )
			$active_id = bbp_get_forum_last_reply_id( $forum_id );

		if ( empty( $active_id ) )
			$active_id = bbp_get_forum_last_topic_id( $forum_id );

		if ( bbp_is_topic( $active_id ) ) {
			$link_url = bbp_get_forum_last_topic_permalink( $forum_id );
			//$link_id added to get post_id and type to allow for later check
			$link_id= bbp_get_forum_last_topic_id ($forum_id) ;
			$check="topic" ;
			$title    = bbp_get_forum_last_topic_title( $forum_id );
			$forum_id_last_active = bbp_get_topic_forum_id($active_id);
		} elseif ( bbp_is_reply( $active_id ) ) {
			$link_url = bbp_get_forum_last_reply_url( $forum_id );
			//$link-id added to get post-id and type to allow for later check
			$link_id = bbp_get_forum_last_reply_id ( $forum_id );
			$check="reply" ;
			$title    = bbp_get_forum_last_reply_title( $forum_id );
			$forum_id_last_active = bbp_get_reply_forum_id($active_id);
		}

		$time_since = bbp_get_forum_last_active_time( $forum_id );
		

		if ( !empty( $time_since ) && !empty( $link_url ) ) {
			//ADDITIONAL CODE to original bbp_get_forum_freshness_link function
			//test if user can see this post, and post link if they can
			$user_id = wp_get_current_user()->ID;
			//get the forum id for the post - that's the forum ID against which we check to see if it is in a group - no idea what forum group the stuff above produces, suspect post id of when last changed.
			$forum_id_check = private_groups_get_forum_id_from_post_id($link_id, $check);
			//now we can check if the user can view this, and if it's not private
			if (private_groups_can_user_view_post($user_id,$forum_id_check) &&  !bbp_is_forum_private($forum_id_last_active)) {
			$anchor = '<a href="' .esc_url( $link_url) . '" title="' . esc_attr( $title ) . '">' .esc_html( $time_since ) .'</a>';
			}
			//if it is private, then check user can view
			elseif (private_groups_can_user_view_post($user_id,$forum_id_check) && bbp_is_forum_private($forum_id_last_active) && current_user_can( 'read_private_forums' ) ) {
			$anchor = '<a href="' .esc_url( $link_url) . '" title="' . esc_attr( $title ) . '">' .esc_html( $time_since ) .'</a>';
			}
		//else user cannot see post so... 
		else {
			//set up which link to send them to
			if (!is_user_logged_in()) {
			if($rpg_settingsf['redirect_page2']) {
				$link=$rpg_settingsf['redirect_page2'] ;
			}
			else {		
				$link="/wp-login";
			}
			}
			else {
			if($rpg_settingsf['redirect_page1']) {
				$link=$rpg_settingsf['redirect_page1'] ;
							}	
			else {
				$link='/404';
			}
  	
			}
			//now see if there is a freshness message
			if (!empty ($rpg_settingsf['set_freshness_message']) ){
				$title=$rpg_settingsf['freshness_message'] ;
				//and set up anchor 
				$anchor = '<a href="' . esc_url($link) . '">' .$title. '</a>';
				}
			else{
			$anchor = '<a href="' . esc_url($link) .  '">' .esc_html( $time_since ) .'</a>';
			}
	
			}
		}
				
		else
			$anchor = esc_html__( 'No Topics', 'bbpress' );

		return $anchor;
	}


//This function is no longer used - bbp_get_author_link does not have the required filters for this function to work as it send return to topic and reply before filter
function pg_get_author_link( ) {
$user_id2 = wp_get_current_user()->ID;
	
		// Parse arguments against default values
		$r = bbp_parse_args( $args, array(
			'post_id'    => $post_id,
			'link_title' => '',
			'type'       => 'both',
			'size'       => 14
		), 'pg_get_author_link' );
	
	
	//confirmed topic
	if( bbp_is_topic( $post_id) ) {
	$topic=bbp_get_topic_post_type() ;
	$forum_id_check = private_groups_get_forum_id_from_post_id($post_id, $topic);
		//now we can check if the user can view this
		if (!private_groups_can_user_view_post($user_id2,$forum_id_check)) 
				return;
		return bbp_get_topic_author_link( $r );
			

	// Confirmed reply
	} elseif ( bbp_is_reply( $post_id ) ) {
	$reply=bbp_get_reply_post_type() ;
	$forum_id_check = private_groups_get_forum_id_from_post_id($post_id, $reply);
		//now we can check if the user can view this
		if (!private_groups_can_user_view_post($user_id2,$forum_id_check)) 
		return ;
		return bbp_get_reply_author_link( $r );
		}
		// Neither a reply nor a topic, so could be a revision
		//if it isn't a topic or reply, not sure so better to just not display the author in this case
		//could be revised to look up the post_parent of this post and then churn that round the code above if required return ;
		return ;

}


//This function is added to bbp_user_register which in turn hooks to wordpress user_register.  It checks if the user role has a group set against it, and if so assigns that to the user

function pg_role_group ($user_id) {
	if ($user_id == 0) return ;  // bail if no user ID
	global $rpg_roles ;
	if (empty ($rpg_roles)) return ; //bail if not set in $rpg_roles
	pg_set_user_group ($user_id) ;
}

//this function assigns user to roles on login
function pg_assign_role_on_login($user_login, $user) {
	$user_id = $user->ID ; 
	//bail if user groups are set for this user 
	$blank_test = get_user_meta ($user_id , 'private_group', true) ;
	if (!empty ($blank_test)) return ; //has an entry in the database
	//bail if login option not set in $rpg_roles
	global $rpg_roles ;
	if (!isset ($rpg_roles['login'])) return ;
	if (empty ($rpg_roles)) return ;  //bail if not set in $rpg_roles
	pg_set_user_group ($user_id) ;
}


function pg_set_user_group ($user_id) {
	global $rpg_roles ;
	$user_info = get_userdata($user_id);
    $user_roles = $user_info->roles ;
		foreach ((array)$user_roles as $list=>$role) {
			if (!empty ($rpg_roles[$role] ) ) {
				$group = $rpg_roles[$role] ;
					if ($group != 'no-group' && (!empty($group)) ) {
					update_user_meta( $user_id, 'private_group', $group); 
					}
			}
		}
	
}

//function for style pack topic index query
function pg_display_topic_index_query_filter ($args) {
		//get forums this user is allowed to see
		$allowed_posts = pg_get_user_forums () ;
	     // now we have $allowed forums, so then we need to only have forums that are common to both (intersect)
		if (!empty($args['post_parent__in'])) {
		$allowed_posts = array_intersect( $allowed_posts, $args['post_parent__in']);
		}
		
		//if there are no allowed forums set post_type to rubbish to ensure a nil return (otherwise it shows all allowed as post__in is blank)
		if (empty ($allowed_posts)) $args['post_type'] = 'qvyzzvxx' ;
		
		//then we can create the post__in data		
        $args['post_parent__in'] = $allowed_posts;
		
              
	return apply_filters( 'pg_display_topic_index_query_filter', $args );
}





//function for style pack topic index query
function pg_display_forum_query_filter ($args) {
		//get forums this user is allowed to see
		$allowed_posts = pg_get_user_forums () ;
	     // now we have $allowed forums, so then we need to only have forums that are common to both (intersect)
		if (!empty($args['post__in'])) {
		$allowed_posts = array_intersect( $allowed_posts, $args['post__in']);
		}
		//if there are no allowed forums set post_type to rubbish to ensure a nil return (otherwise it shows all allowed as post__in is blank)
		if (empty ($allowed_posts)) $args['post_type'] = 'qvyzzvxx' ;
		
		//then we can create the post__in data		
        $args['post__in'] = $allowed_posts;
		
              
	return apply_filters( 'pg_display_topic_index_query_filter', $args );
}




//function to create array used by above two functions
function pg_get_user_forums () {
//create an array of current forums this user can view
	$query_data = array(
            'post_type' => bbp_get_forum_post_type(),
			'post_status' => bbp_get_public_status_id(),
            'posts_per_page' => get_option('_bbp_forums_per_page', 50),
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
		//PRIVATE GROUPS Get an array of forums which the current user has permissions to view
        $allowed_posts = private_groups_get_permitted_post_ids(new WP_Query($query_data));
		return $allowed_posts ;
}

//function for style pack latest activity widget
function pg_latest_activity_forum_query_filter ($topics_query) {
	    $topics_query['posts_per_page'] = 200;
		$allowed_posts = private_groups_get_permitted_post_ids(new WP_Query($topics_query));
		$topics_query['post__in'] = $allowed_posts ;
		return apply_filters( 'pg_latest_activity_forum_query_filter', $topics_query ) ;
}




//function for reply permissions
function pg_current_user_can_access_create_reply_form () {
	//much of this code isn't really needed as far as I can see, but is in the original bbpress function, so repeated in case !
	//in practice as far as I can see this code is only called when we have a topic ID, so can work out a forum ID
	//echo '<br>AT START OF REPLY FORM' ;
	// Users need to earn access
	$retval = false;

	// Always allow keymasters 
	if ( bbp_is_user_keymaster() ) {
		$retval = true;
	}
	
	else {
	//TOPICS PERMISSION CHECK
	$user_id = wp_get_current_user()->ID;
	$topic = bbp_get_topic_id() ;	
	//we should only ever have a topic ID, and in theory the next lines aren't needed, but here just in case
	if (!empty ($topic)) $forum_id = get_post_field( 'post_parent', $topic );
	//if no topic is set, then we are in theory creating a new topic so should not be in this part, but just in case  - we don't have  topic=forum, so we need to find if we're in a single forum, and check that
	elseif (bbp_is_single_forum() ) $forum_id = bbp_get_forum_id() ;
	else {
	//else no idea why we are in this function for return false to be safe
	$retval = false;
	return (bool) apply_filters( 'bsp_current_user_can_access_create_reply_form1', (bool) $retval );
	}
	//else we have a forum, so check permissions
	$valuecheck = pg_check_topic_permissions ($forum_id) ;
	//so we exit with potentials of on the $valuecheck array, and now need to check these in order
	//users can have multiple groups, so we start with highest permission and work down
	//var_dump ($valuecheck) ;
	if (empty ($valuecheck) || !empty ($valuecheck['check4']) ) {
		//echo '<br> value check is NULL or 4 - create topics/replies' ;
		//if the array doesn't exist, then either the forum has no topic permissions, or the user doesn't have any matching so we can return
		//if we have a $value('check4'] then we can return, as access has already been decided and we can return
		//either with retval true as the user is admin or if the user can edit
			if ( ( bbp_is_single_forum() || is_page() || is_single() ) && bbp_is_forum_open() ) {
			//don't think this is ever called as only topics appear at forum level, never replies ;
			$retval = bbp_current_user_can_publish_topics();
			}elseif ( bbp_is_topic_edit() ) {
				$retval = current_user_can( 'edit_topic', bbp_get_topic_id() );
			}
		return (bool) apply_filters( 'bsp_current_user_can_access_create_reply_form2', (bool) $retval );		
	}
	//if $value['check3'] exists, then user can edit reply, so set to true then test user capabilities and return
	if (!empty ($valuecheck['check3']) ) {
		//echo '<br> value chcek is 3 create replies' ;
		$retval = true;
		//unless their base permission changes it back to false
		if ( ( bbp_is_single_forum() || is_page() || is_single() ) && bbp_is_forum_open() ) {
			//only used for forum level, not for replies ;
			$retval = bbp_current_user_can_publish_topics();
			}elseif ( bbp_is_topic_edit() ) {
				$retval = current_user_can( 'edit_topic', bbp_get_topic_id() );
			}
		return (bool) apply_filters( 'bsp_current_user_can_access_create_reply_form3', (bool) $retval );
	}
	//if $value['check2'] or $value ['check1'] exists, then user can only either edit topics or view topics and this is a reply, so set to false and return
	if (!empty ($valuecheck['check2']) || !empty ($valuecheck['check1']) ) {	
		//echo '<br> value check is 2 or 1  create topics or only view - so currently set to false as only done for replies' ;
		$retval = false;
		return (bool) apply_filters( 'bsp_current_user_can_access_create_reply_form4', (bool) $retval );	
	}
	} // end of else
	// Allow access to be filtered
	return (bool) apply_filters( 'bsp_current_user_can_access_create_reply_form5', (bool) $retval );
}

function pg_current_user_can_access_create_topic_form() {
	//echo '<br>AT START OF TOPIC FORM' ;
	// Users need to earn access
	$retval = false;

	// Always allow keymasters
	if ( bbp_is_user_keymaster() ) {
		$retval = true;
		
	//for topics 
	//either we are in multiple forums - in which case this function is not called -  'private_groups_get_dropdown_forums' in forum functions takes care of restricting access
	//or we are in a single forum so check if user is allowed topics in that forum
	//or we are editing an existing topic - so check even though user should not have been able to create !
		
	//TOPICS PERMISSION CHECK
	$user_id = wp_get_current_user()->ID;
	
	// Looking at a single forum & forum is open
	} elseif ( ( bbp_is_single_forum() || is_page() || is_single() ) && bbp_is_forum_open() ) {
		$forum_id = bbp_get_forum_id() ;
		$valuecheck = pg_check_topic_permissions ($forum_id) ;
		//so we exit with potentials of on the $valuecheck array, and now need to check these in order
		//users can have multiple groups, so we start with highest permission and work down
			//var_dump ($valuecheck) ;
			if (empty ($valuecheck) || !empty ($valuecheck['check4']) ) {
				//echo '<br> value check is NULL or 4 - create topics/replies' ;
				//if the array doesn't exist, then either the forum has no topic permissions, or the user doesn't have any matching so we can return
				//if we have a $value('check4'] then we can return, as access has already been decided and we can return
				//either with retval true as the user is admin or if the user can edit
				$retval = bbp_current_user_can_publish_topics();
				return (bool) apply_filters( 'bsp_current_user_can_access_create_topic_form1', (bool) $retval );		
			}
			//if $value['check2'] exists, then user can edit reply, so set to true then test user capabilities and return
			if (!empty ($valuecheck['check2']) ) {
				//echo '<br> value check is 2 create topics' ;
				$retval = true;
				//unless their base permission changes it back to false
					$retval = bbp_current_user_can_publish_topics(); 
					return (bool) apply_filters( 'bsp_current_user_can_access_create_topic_form2', (bool) $retval );
					}
			//if $value['check3'] or $value ['check1'] exists, then user can only either edit replies or vie, so set to false and return
			if (!empty ($valuecheck['check3']) || !empty ($valuecheck['check1']) ) {	
			//echo '<br> value check is 3 or 1  create topics or only view - so currently set to false as only done for replies' ;
			$retval = false;
			return (bool) apply_filters( 'bsp_current_user_can_access_create_topic_form3', (bool) $retval );	
			}
		
	// or.. User can edit this topic
	} elseif ( bbp_is_topic_edit() ) {
		 $topic = bbp_get_topic_id() ;
		if (!empty ($topic)) $forum_id = get_post_field( 'post_parent', $topic );
			$valuecheck = pg_check_topic_permissions ($forum_id) ;
			//so we exit with potentials of on the $valuecheck array, and now need to check these in order
			//users can have multiple groups, so we start with highest permission and work down
			//var_dump ($valuecheck) ;
			if (empty ($valuecheck) || !empty ($valuecheck['check4']) ) {
				//echo '<br> value check is NULL or 4 - create topics/replies' ;
				//if the array doesn't exist, then either the forum has no topic permissions, or the user doesn't have any matching so we can return
				//if we have a $value('check4'] then we can return, as access has already been decided and we can return
				//either with retval true as the user is admin or if the user can edit
				$retval = current_user_can( 'edit_topic', bbp_get_topic_id() );
				return (bool) apply_filters( 'bsp_current_user_can_access_create_topic_form1', (bool) $retval );		
			}
			//if $value['check2'] exists, then user can edit reply, so set to true then test user capabilities and return
			if (!empty ($valuecheck['check2']) ) {
				//echo '<br> value check is 2 create topics' ;
				$retval = true;
				//unless their base permission changes it back to false
					$retval = current_user_can( 'edit_topic', bbp_get_topic_id() );
					return (bool) apply_filters( 'bsp_current_user_can_access_create_topic_form2', (bool) $retval );
					}
			//if $value['check3'] or $value ['check1'] exists, then user can only either edit replies or view  , so set to false and return
			if (!empty ($valuecheck['check3']) || !empty ($valuecheck['check1']) ) {	
			//echo '<br> value check is 3 or 1  create replies or only view - so currently set to false as only done for replies' ;
			$retval = false;
			return (bool) apply_filters( 'bsp_current_user_can_access_create_topic_form3', (bool) $retval );	
			}
		
	
	}  //end of topic edit
return (bool) apply_filters( 'bsp_current_user_can_access_create_topic_form4', (bool) $retval );	
}





Function pg_check_topic_permissions ($forum_id = 0) {
	if ($forum_id == 0 ) {
	return ;
	}
	$user_id = wp_get_current_user()->ID;
	$groups = get_post_meta( $forum_id, '_private_group', false );
	
	//so now we know which forum it is in - now we need to know if user has access to any groups of this forum  - so we get the groups this user has
	$check=get_user_meta( $user_id, 'private_group',true);
	foreach ( $groups as $group ) {
				$has_group = false ;
				//single group set?
				if ($check==$group ) $has_group = true;
				//multiple group set
				if (strpos($check, '*'.$group.'*') !== FALSE) $has_group = true;
				//so if user has this group, so check if forum has this set in topic permissions.
				if (!empty($has_group)) {
				//user might have multiple groups with different topic permissions, so we need to create an array of these.
					$tp = '_private_group_'.$group ;
					$value = get_post_meta( $forum_id, $tp, true);
					//$value = (!empty (get_post_meta( $forum_id, $tp, true)) ? get_post_meta( $forum_id, $tp, true ) : '' );
					if ($value == '1') $valuecheck['check1']='1' ;
					if ($value == '2' ) $valuecheck['check2']='1' ;
					if ($value == '3') $valuecheck['check3']='1' ;
					if ($value == '4') $valuecheck['check4']='1' ;
				}
	} //end of foreach
return ($valuecheck) ;
}

