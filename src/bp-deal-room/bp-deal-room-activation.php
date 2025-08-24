<?php
/**
 * BuddyBoss Deal Room Activation Script.
 *
 * Run this script to set up Deal Room and create Level 5 groups.
 *
 * @package BuddyBoss\DealRoom
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Activate Deal Room and create groups.
 */
function bp_deal_room_activate_all() {
	// Load required files.
	if ( ! function_exists( 'bp_deal_room_create_tables' ) ) {
		require_once dirname( __FILE__ ) . '/bp-deal-room-functions.php';
	}

	// Create tables.
	bp_deal_room_create_tables();
	
	// Add investor role.
	bp_deal_room_add_investor_role();
	
	// Create default groups.
	bp_deal_room_create_default_groups();
	
	// Add success message.
	if ( is_admin() ) {
		add_action( 'admin_notices', function() {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Level 5 Deal Room has been activated successfully!', 'buddyboss' ); ?></p>
			</div>
			<?php
		} );
	}
	
	return true;
}

// Run activation if requested.
if ( isset( $_GET['bp_deal_room_activate'] ) && current_user_can( 'manage_options' ) ) {
	bp_deal_room_activate_all();
}