<?php
/**
 * BuddyBoss Deal Room Component Class.
 *
 * @package BuddyBoss\DealRoom
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Deal Room Component.
 *
 * @since BuddyBoss 1.0.0
 */
class BP_Deal_Room_Component extends BP_Component {

	/**
	 * Start the Deal Room component creation process.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function __construct() {
		parent::start(
			'deal_room',
			__( 'Deal Room', 'buddyboss' ),
			buddypress()->plugin_dir,
			array(
				'adminbar_myaccount_order' => 100,
			)
		);
	}

	/**
	 * Include Deal Room component files.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @param array $includes Array of files to include.
	 */
	public function includes( $includes = array() ) {
		$includes = array(
			'functions',
			'template',
			'filters',
			'settings',
			'screens/index',
		);

		// Include admin files if in admin.
		if ( is_admin() ) {
			$includes[] = 'admin/bp-deal-room-admin';
		}

		parent::includes( $includes );
	}

	/**
	 * Set up Deal Room component global settings.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @param array $args Array of arguments.
	 */
	public function setup_globals( $args = array() ) {

		$bp = buddypress();

		// Define a slug for the component.
		if ( ! defined( 'BP_DEAL_ROOM_SLUG' ) ) {
			define( 'BP_DEAL_ROOM_SLUG', 'deal-room' );
		}

		/**
		 * Global tables for Deal Room component.
		 */
		$global_tables = array(
			'table_name'           => $bp->table_prefix . 'bp_deal_room',
			'table_name_documents' => $bp->table_prefix . 'bp_deal_room_documents',
			'table_name_access'    => $bp->table_prefix . 'bp_deal_room_access',
		);

		// All globals for Deal Room component.
		parent::setup_globals(
			array(
				'slug'                  => BP_DEAL_ROOM_SLUG,
				'has_directory'         => false,
				'global_tables'         => $global_tables,
				'search_string'         => __( 'Search Deal Rooms...', 'buddyboss' ),
			)
		);
	}

	/**
	 * Set up component navigation.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @param array $main_nav Array of main navigation items.
	 * @param array $sub_nav  Array of sub navigation items.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {

		// Only add navigation for users who have access.
		if ( ! bp_deal_room_user_has_access() ) {
			return;
		}

		// Add 'Deal Room' to the main navigation.
		$main_nav = array(
			'name'                => __( 'Deal Room', 'buddyboss' ),
			'slug'                => bp_get_deal_room_slug(),
			'position'            => 80,
			'screen_function'     => 'bp_deal_room_screen_index',
			'default_subnav_slug' => 'documents',
			'item_css_id'         => $this->id,
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @param array $wp_admin_nav Array of WordPress admin nav items.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {

		// Only add admin bar for users who have access.
		if ( ! bp_deal_room_user_has_access() ) {
			return;
		}

		// Add the "Deal Room" sub menu.
		$wp_admin_nav[] = array(
			'parent' => buddypress()->my_account_menu_id,
			'id'     => 'my-account-' . $this->id,
			'title'  => __( 'Deal Room', 'buddyboss' ),
			'href'   => bp_loggedin_user_domain() . bp_get_deal_room_slug() . '/',
		);

		parent::setup_admin_bar( $wp_admin_nav );
	}

	/**
	 * Set up the title for Deal Room pages.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function setup_title() {
		$bp = buddypress();

		if ( bp_is_deal_room_component() ) {
			$bp->bp_options_title = __( 'Deal Room', 'buddyboss' );
		}

		parent::setup_title();
	}
}