<?php
/**
 * BuddyBoss Deal Room Functions.
 *
 * @package BuddyBoss\DealRoom
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Check if current user has access to Deal Room.
 *
 * @since BuddyBoss 1.0.0
 *
 * @param int $user_id User ID to check. Default current user.
 * @return bool True if user has access, false otherwise.
 */
function bp_deal_room_user_has_access( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	// Check if user is an administrator.
	if ( bp_current_user_can( 'bp_moderate' ) ) {
		return true;
	}

	// Check if user has the investor role.
	$user = get_user_by( 'id', $user_id );
	if ( $user && in_array( 'investor', (array) $user->roles, true ) ) {
		return true;
	}

	// Check if user is in Deal Room access list.
	$access_list = get_option( 'bp_deal_room_access_list', array() );
	if ( in_array( $user_id, $access_list, true ) ) {
		return true;
	}

	/**
	 * Filter whether user has access to Deal Room.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @param bool $has_access Whether user has access.
	 * @param int  $user_id    User ID being checked.
	 */
	return apply_filters( 'bp_deal_room_user_has_access', false, $user_id );
}

/**
 * Get Deal Room slug.
 *
 * @since BuddyBoss 1.0.0
 *
 * @return string Deal Room slug.
 */
function bp_get_deal_room_slug() {
	return apply_filters( 'bp_get_deal_room_slug', BP_DEAL_ROOM_SLUG );
}

/**
 * Check if we are on Deal Room component.
 *
 * @since BuddyBoss 1.0.0
 *
 * @return bool True if on Deal Room component.
 */
function bp_is_deal_room_component() {
	return bp_is_current_component( 'deal_room' );
}

/**
 * Get Deal Room document types.
 *
 * @since BuddyBoss 1.0.0
 *
 * @return array Array of document types.
 */
function bp_deal_room_get_document_types() {
	$types = array(
		'pitch_deck'          => __( 'Pitch Decks', 'buddyboss' ),
		'market_research'     => __( 'Market Research', 'buddyboss' ),
		'competitive_analysis' => __( 'Competitive Analysis', 'buddyboss' ),
		'business_plan'       => __( 'Business Plans', 'buddyboss' ),
		'financial_projection' => __( 'Financial Projections', 'buddyboss' ),
		'cap_table'           => __( 'Cap Table', 'buddyboss' ),
		'legal_document'      => __( 'Legal Documents', 'buddyboss' ),
	);

	/**
	 * Filter Deal Room document types.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @param array $types Array of document types.
	 */
	return apply_filters( 'bp_deal_room_document_types', $types );
}

/**
 * Create Deal Room tables on activation.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_create_tables() {
	global $wpdb;
	$bp = buddypress();

	$charset_collate = $wpdb->get_charset_collate();

	// Deal Room main table.
	$sql = "CREATE TABLE IF NOT EXISTS {$bp->deal_room->table_name} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		name varchar(255) NOT NULL,
		description text,
		created_by bigint(20) NOT NULL,
		date_created datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY created_by (created_by),
		KEY date_created (date_created)
	) {$charset_collate};";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	// Deal Room documents table.
	$sql = "CREATE TABLE IF NOT EXISTS {$bp->deal_room->table_name_documents} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		room_id bigint(20) NOT NULL,
		document_type varchar(50) NOT NULL,
		document_id bigint(20) NOT NULL,
		title varchar(255) NOT NULL,
		description text,
		file_url text NOT NULL,
		uploaded_by bigint(20) NOT NULL,
		date_uploaded datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY room_id (room_id),
		KEY document_type (document_type),
		KEY uploaded_by (uploaded_by),
		KEY date_uploaded (date_uploaded)
	) {$charset_collate};";

	dbDelta( $sql );

	// Deal Room access table.
	$sql = "CREATE TABLE IF NOT EXISTS {$bp->deal_room->table_name_access} (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		room_id bigint(20) NOT NULL,
		user_id bigint(20) NOT NULL,
		access_level varchar(20) NOT NULL default 'view',
		date_granted datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY room_id (room_id),
		KEY user_id (user_id)
	) {$charset_collate};";

	dbDelta( $sql );
}

/**
 * Add investor role.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_add_investor_role() {
	add_role(
		'investor',
		__( 'Investor', 'buddyboss' ),
		array(
			'read'              => true,
			'upload_files'      => true,
			'edit_posts'        => false,
			'delete_posts'      => false,
			'publish_posts'     => false,
			'bp_deal_room_access' => true,
		)
	);
}

/**
 * Create default groups.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_create_default_groups() {
	$groups = array(
		array(
			'name'        => 'Level 5',
			'description' => 'Level 5 community group',
			'status'      => 'private',
		),
		array(
			'name'        => 'BizBox',
			'description' => 'BizBox community group',
			'status'      => 'private',
		),
		array(
			'name'        => 'SudoSelf',
			'description' => 'SudoSelf community group',
			'status'      => 'private',
		),
	);

	foreach ( $groups as $group_data ) {
		// Check if group already exists.
		$existing = BP_Groups_Group::group_exists( $group_data['name'] );
		if ( ! $existing ) {
			$group_args = array(
				'creator_id'  => get_current_user_id(),
				'name'        => $group_data['name'],
				'description' => $group_data['description'],
				'status'      => $group_data['status'],
			);
			groups_create_group( $group_args );
		}
	}
}

/**
 * Handle Deal Room screen display.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_screen() {
	add_action( 'bp_template_content', 'bp_deal_room_screen_content' );
	bp_core_load_template( 'members/single/plugins' );
}

/**
 * Display Deal Room screen content.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_screen_content() {
	?>
	<div class="bp-deal-room">
		<h2><?php esc_html_e( 'Deal Room', 'buddyboss' ); ?></h2>
		<p><?php esc_html_e( 'Secure document repository for investors.', 'buddyboss' ); ?></p>

		<div class="deal-room-sections">
			<?php
			$document_types = bp_deal_room_get_document_types();
			foreach ( $document_types as $type => $label ) {
				?>
				<div class="deal-room-section">
					<h3><?php echo esc_html( $label ); ?></h3>
					<div class="deal-room-documents" data-type="<?php echo esc_attr( $type ); ?>">
						<!-- Documents will be loaded here via AJAX -->
					</div>
					<button class="button upload-document" data-type="<?php echo esc_attr( $type ); ?>">
						<?php esc_html_e( 'Upload', 'buddyboss' ); ?>
					</button>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
}