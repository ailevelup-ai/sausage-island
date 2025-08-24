<?php
/**
 * BuddyBoss Deal Room Loader
 *
 * @package BuddyBoss\DealRoom
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Set up the Deal Room component.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_setup_deal_room() {
	buddypress()->deal_room = new BP_Deal_Room_Component();
}
add_action( 'bp_setup_components', 'bp_setup_deal_room', 6 );