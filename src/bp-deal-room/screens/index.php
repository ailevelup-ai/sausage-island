<?php
/**
 * Deal Room Main Screen
 *
 * @package BuddyBoss\DealRoom
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Deal Room main screen function.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_screen_index() {
	// Add title and content.
	add_action( 'bp_template_title', 'bp_deal_room_screen_index_title' );
	add_action( 'bp_template_content', 'bp_deal_room_screen_index_content' );

	// Load the template.
	bp_core_load_template( apply_filters( 'bp_deal_room_screen_index', 'members/single/plugins' ) );
}

/**
 * Deal Room main screen title.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_screen_index_title() {
	echo '<h2 class="bb-bp-profile-title">' . esc_html__( 'Level 5 Deal Room', 'buddyboss' ) . '</h2>';
}

/**
 * Deal Room main screen content.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_screen_index_content() {
	bp_get_template_part( 'deal-room/index' );
}