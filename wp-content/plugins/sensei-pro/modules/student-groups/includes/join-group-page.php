<?php
/**
 * File containing Join Group page attributes.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Student_Groups;

/**
 * Get the attributes to create the Join Group page.
 *
 * @return array Post attributes.
 */
function get_join_group_page_attributes() {
	return [
		'post_title'   => __( 'Join your new group', 'sensei-pro' ),
		'post_content' => '<!-- wp:sensei-pro/join-group -->
			<div class="wp-block-sensei-pro-join-group has-border-color" style="border-color:#c7c3c34f;border-style:solid;border-width:1px;padding-top:30px;padding-right:30px;padding-bottom:30px;padding-left:30px"><!-- wp:columns {"style":{"spacing":{"margin":{"bottom":"0"}}},"className":"wp-block-sensei-pro-join-group__columns"} -->
			<div class="wp-block-columns wp-block-sensei-pro-join-group__columns" style="margin-bottom:0"><!-- wp:column {"width":"70%"} -->
			<div class="wp-block-column" style="flex-basis:70%"><!-- wp:sensei-pro/group-name {"style":{"spacing":{"padding":{"top":"0","bottom":"0"},"margin":{"bottom":"10px"}}}} -->
			<h2 class="wp-block-sensei-pro-group-name" style="margin-bottom:10px;padding-top:0;padding-bottom:0">{{groupName}}</h2>
			<!-- /wp:sensei-pro/group-name -->

			<!-- wp:sensei-pro/group-members-count {"style":{"spacing":{"margin":{"top":"10px"}}}} -->
			<div class="wp-block-sensei-pro-group-members-count" style="margin-top:10px">{{groupMembersCount}}</div>
			<!-- /wp:sensei-pro/group-members-count -->

			<!-- wp:sensei-pro/group-members-list {"style":{"spacing":{"margin":{"top":"18px"}}}} -->
			<div class="wp-block-sensei-pro-group-members-list" style="margin-top:18px">{{groupMembersList}}</div>
			<!-- /wp:sensei-pro/group-members-list --></div>
			<!-- /wp:column -->

			<!-- wp:column {"verticalAlignment":"bottom"} -->
			<div class="wp-block-column is-vertically-aligned-bottom"><!-- wp:buttons {"layout":{"type":"flex","justifyContent":"right"}} -->
			<div class="wp-block-buttons"><!-- wp:button {"className":"wp-block-sensei-pro-join-group__button"} -->
			<div class="wp-block-button wp-block-sensei-pro-join-group__button"><a class="wp-block-button__link wp-element-button" href="#{{joinGroupUrl}}">Join Group</a></div>
			<!-- /wp:button --></div>
			<!-- /wp:buttons --></div>
			<!-- /wp:column --></div>
			<!-- /wp:columns --></div>
			<!-- /wp:sensei-pro/join-group -->',
		'post_status'  => 'publish',
		'post_type'    => 'page',
	];
}
