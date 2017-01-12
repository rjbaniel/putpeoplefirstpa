<?php
/**
 * This adds the meta box for the permissions component.  This allows restriction to bbpress groups
 */
/** 
 */

 
 
add_action( 'admin_menu', 'private_groups_create_meta_box' );

/* Saves the content permissions metabox data to a custom field. */
add_action( 'save_post', 'private_groups_save_meta', 1, 2 );


function private_groups_create_meta_box() {
	add_meta_box( 'forum-group-meta-box', __( 'Forum Groups' , 'bbp-private-groups') , 'private_groups_meta_box', 'forum', 'normal', 'high' );
}

/**
 * Controls the display of the content permissions meta box.  This allows users
 * to select groups that should have access to an individual post/page.
 */
 
function private_groups_meta_box( $object, $box ) {
	global $post ?>

	<input type="hidden" name="private_groups_meta_nonce" value="<?php echo wp_create_nonce(plugin_basename(__FILE__)); ?>" />

	<p>
		<label for="groups"><?php _e('<strong>Groups:</strong> Restrict the content to these groups on the front end of the site.  If all boxes are left unchecked, everyone can view the content.', 'bbp-private_groups'); ?></label>
	</p>

	<div style="overflow: hidden;">

		<?php

		/* Get the meta*/
		
		$meta = get_post_meta( $post->ID, '_private_group', false );
		global $rpg_groups ;

		/* Loop through each of the available roles. */
		if(empty($rpg_groups)) {
		echo _e('<b>No groups have yet been set up - go to Dashboard>Settings>bbp Private Groups to set</b>', 'bbp-private_groups') ; 
		}
		else {
		foreach ( $rpg_groups as $group => $details ) {
			$checked = false;
		
			/* If the role has been selected, make sure it's checked. */
			if ( is_array( $meta ) && in_array( $group, $meta ) )
				$checked = ' checked="checked" '; ?>
			<p style="width: 32%; float: left; margin-right: 0;">
				<label for="group-<?php echo $group; ?>">
				<?php $groupname=__('Group','bbp-private-groups').substr($group,5,strlen($group)) ; ?>
					<input type="checkbox" name=<?php echo $group ; ?> id=<?php echo $group ; ?> <?php echo $checked; ?> value="<?php echo $group; ?>" /> 
					<?php echo $groupname." ".$details ; ?>
				</label>
			</p>
			
		<?php } ?>
		
		<?php
		global $rpg_topic_permissions ;
		if (!empty ($rpg_topic_permissions['activate']) ) {
			?>
		<div>
		<p>
		<label for="group_topics"><?php _e('<strong>Group Topic Permsissions:</strong> <i> These will appear for any groups set for this forum when you publish or update this forum.</i>', 'bbp-private_groups'); ?></label></p>
		<p>
		<label for="group_topics"><?php _e('For each group, you can set whether group members are able to view, create topics and replies.', 'bbp-private_groups'); ?></label>
		</p>
		<p>
		<label for="group_topics"><?php _e('Please see the <a href="/wp-admin/options-general.php?page=bbp-private-group-settings&tab=topic_permissions" target="_blank">Topic Permissions settings</a> to understand the impact of this', 'bbp-private_groups'); ?></label>
		</p>
		<table>
		<?php foreach ( $rpg_groups as $group => $details ) { ?>
			
			<?php
			//if this group is selected
			if ( is_array( $meta ) && in_array( $group, $meta ) ) { 
			$groupname=__('Group','bbp-private-groups').substr($group,5,strlen($group)) ;
			$tp = '_private_group_'.$group ;
			$valuename1 = __('Only View Topics/Replies', 'bbp-private_groups') ;
			$valuename2 = __('Create/Edit Topics', 'bbp-private_groups') ;
			$valuename3 = __('Create/Edit Replies', 'bbp-private_groups') ;
			$valuename4 = __('Create/Edit Topics and Replies', 'bbp-private_groups') ;
			$perm = get_post_meta($post->ID , $tp, true) ;
			$value =  (!empty ($perm ) ? $perm : '4' );
			$valuex = 'valuename'.$value ;
			$valuename = $$valuex ;
			?>			
			<tr>
			<td> <?php echo $groupname ; ?> </td>
			<td style ="width:250px"> <?php echo $details ; ?> </td>
			<td style ="width:150px">
			<select name="<?php echo $tp ; ?>">
			<?php echo '<option value="'.esc_html( $value).'">'.esc_html( $valuename) ; ?> 
			<option value="4"><?php echo $valuename4 ; ?></option>
			<option value="2"><?php echo $valuename2 ; ?></option>
			<option value="3"><?php echo $valuename3 ; ?></option>
			<option value="1"><?php echo $valuename1 ; ?></option>
			</select>
			</td>
			</tr>
			
			<?php
							
			} // end of if is array
				
		} // end of foreach?>
		</table>
		</div>
		<?php }  // end of if $rpg_topic_permissions
		
		} //end of else ?>

		</div><?php
}
/**
 * Saves the content permissions metabox data to a custom field.
 *
 */
function private_groups_save_meta( $post_id, $post ) {
	global $rpg_groups ;

	/* Only allow users that can edit the current post to submit data. */
	if ( 'post' == $post->post_type && !current_user_can( 'edit_posts', $post_id ) )
		return;

	/* Only allow users that can edit the current page to submit data. */
	elseif ( 'page' == $post->post_type && !current_user_can( 'edit_pages', $post_id ) )
		return;

	/* Don't save if the post is only a revision. */
	if ( 'revision' == $post->post_type )
		return;

	/* Loop through each of the site's available roles. */
	if(!empty($rpg_groups)) {
		foreach ( $rpg_groups as $group => $details ){
		//Set topic permissions for this group
		$tp = '_private_group_'.$group ;
		if (!empty ($_POST[$tp]) ) update_post_meta( $post_id, $tp, $_POST[$tp], false );
		
		/* Get post metadata for the custom field key 'group'. */
		$meta = (array)get_post_meta( $post_id, '_private_group', false );
		      
		/* Check if the group was selected. */
		if ( !empty ($_POST[$group] )){
			
		

			/* If selected and already saved, continue looping through the roles and do nothing for this role. */
			if ( in_array( $group, $meta ) )
			continue;

			/* If the group was selected and not already saved, add the group as a new value to the 'group' custom field. */
			else
				add_post_meta( $post_id, '_private_group', $group, false );
		}

		/* If role not selected, delete. */
		else {
			delete_post_meta( $post_id, '_private_group', $group );
			delete_post_meta( $post_id, $tp);
		}
		
			
	} // End loop through site's groups
	
	
	
	
	
	
}
}
?>