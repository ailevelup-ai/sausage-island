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
 * Get Level 5 sections.
 *
 * @since BuddyBoss 1.0.0
 *
 * @return array Array of Level 5 sections.
 */
function bp_deal_room_get_level5_sections() {
	$sections = array(
		'sudoself'         => __( 'SudoSelf', 'buddyboss' ),
		'bizbox'           => __( 'BizBox', 'buddyboss' ),
		'level5_podcast'   => __( 'Level 5 Podcast', 'buddyboss' ),
		'sausage_software' => __( 'Sausage Software', 'buddyboss' ),
		'trillion_club'    => __( 'Trillion Club', 'buddyboss' ),
	);

	/**
	 * Filter Level 5 sections.
	 *
	 * @since BuddyBoss 1.0.0
	 *
	 * @param array $sections Array of sections.
	 */
	return apply_filters( 'bp_deal_room_level5_sections', $sections );
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
		section varchar(50) DEFAULT NULL,
		document_type varchar(50) NOT NULL,
		document_id bigint(20) NOT NULL,
		title varchar(255) NOT NULL,
		description text,
		file_url text NOT NULL,
		uploaded_by bigint(20) NOT NULL,
		date_uploaded datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (id),
		KEY room_id (room_id),
		KEY section (section),
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
			'name'        => 'SudoSelf',
			'description' => 'SudoSelf community - Self-improvement and personal development',
			'status'      => 'private',
		),
		array(
			'name'        => 'BizBox',
			'description' => 'BizBox community - Business tools and resources',
			'status'      => 'private',
		),
		array(
			'name'        => 'Level 5 Podcast',
			'description' => 'Level 5 Podcast community - Podcast discussions and content',
			'status'      => 'private',
		),
		array(
			'name'        => 'Sausage Software',
			'description' => 'Sausage Software community - Software development and tech',
			'status'      => 'private',
		),
		array(
			'name'        => 'Trillion Club',
			'description' => 'Trillion Club - Exclusive high-growth entrepreneurs',
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
		<h2><?php esc_html_e( 'Level 5 Deal Room', 'buddyboss' ); ?></h2>
		<p><?php esc_html_e( 'Secure document repository for Level 5 investors and partners.', 'buddyboss' ); ?></p>

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

/**
 * Send email notification for document upload.
 *
 * @since BuddyBoss 1.0.0
 *
 * @param int   $document_id Document ID.
 * @param array $document_data Document data.
 */
function bp_deal_room_send_upload_notification( $document_id, $document_data ) {
	// Get all users with access.
	$users = bp_deal_room_get_users_with_access();
	
	// Get uploader info.
	$uploader = get_user_by( 'id', $document_data['uploaded_by'] );
	$uploader_name = $uploader ? $uploader->display_name : __( 'Someone', 'buddyboss' );
	
	// Get document type label.
	$document_types = bp_deal_room_get_document_types();
	$document_type_label = isset( $document_types[ $document_data['document_type'] ] ) 
		? $document_types[ $document_data['document_type'] ] 
		: $document_data['document_type'];
	
	// Get section label.
	$section_label = '';
	if ( ! empty( $document_data['section'] ) ) {
		$sections = bp_deal_room_get_level5_sections();
		$section_label = isset( $sections[ $document_data['section'] ] ) 
			? $sections[ $document_data['section'] ] 
			: $document_data['section'];
	}
	
	// Email subject and message.
	$subject = sprintf( __( '[Level 5] New %s Uploaded', 'buddyboss' ), $document_type_label );
	
	$message = sprintf(
		__( 'Hello,

%1$s has uploaded a new document to the Level 5 Deal Room:

Document: %2$s
Type: %3$s
%4$s

To view this document, please visit the Deal Room.

Best regards,
Level 5 Team', 'buddyboss' ),
		$uploader_name,
		$document_data['title'],
		$document_type_label,
		$section_label ? sprintf( __( 'Section: %s', 'buddyboss' ), $section_label ) : ''
	);
	
	// Send to each user with access.
	foreach ( $users as $user_id ) {
		// Don't send to uploader.
		if ( $user_id == $document_data['uploaded_by'] ) {
			continue;
		}
		
		$user = get_user_by( 'id', $user_id );
		if ( $user && ! empty( $user->user_email ) ) {
			wp_mail( $user->user_email, $subject, $message );
		}
	}
}

/**
 * Get all users with Deal Room access.
 *
 * @since BuddyBoss 1.0.0
 *
 * @return array Array of user IDs.
 */
function bp_deal_room_get_users_with_access() {
	$users = array();
	
	// Get users with investor role.
	$investors = get_users( array(
		'role'   => 'investor',
		'fields' => 'ID',
	) );
	$users = array_merge( $users, $investors );
	
	// Get users from access list.
	$access_list = get_option( 'bp_deal_room_access_list', array() );
	$users = array_merge( $users, $access_list );
	
	// Get administrators.
	$admins = get_users( array(
		'role'   => 'administrator',
		'fields' => 'ID',
	) );
	$users = array_merge( $users, $admins );
	
	// Remove duplicates.
	return array_unique( $users );
}

/**
 * Add document upload to activity stream.
 *
 * @since BuddyBoss 1.0.0
 *
 * @param int   $document_id Document ID.
 * @param array $document_data Document data.
 */
function bp_deal_room_add_upload_activity( $document_id, $document_data ) {
	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}
	
	// Get document type label.
	$document_types = bp_deal_room_get_document_types();
	$document_type_label = isset( $document_types[ $document_data['document_type'] ] ) 
		? $document_types[ $document_data['document_type'] ] 
		: $document_data['document_type'];
	
	// Get section label.
	$section_label = '';
	if ( ! empty( $document_data['section'] ) ) {
		$sections = bp_deal_room_get_level5_sections();
		$section_label = isset( $sections[ $document_data['section'] ] ) 
			? ' for ' . $sections[ $document_data['section'] ] 
			: '';
	}
	
	// Activity action.
	$action = sprintf(
		__( '%1$s uploaded a new %2$s to Level 5 Deal Room%3$s', 'buddyboss' ),
		bp_core_get_userlink( $document_data['uploaded_by'] ),
		strtolower( $document_type_label ),
		$section_label
	);
	
	// Activity content.
	$content = sprintf(
		'<div class="deal-room-activity">
			<h4>%1$s</h4>
			%2$s
			<p class="activity-deal-room-meta">
				<span class="activity-deal-room-type">%3$s</span>
			</p>
		</div>',
		esc_html( $document_data['title'] ),
		! empty( $document_data['description'] ) ? '<p>' . esc_html( $document_data['description'] ) . '</p>' : '',
		esc_html( $document_type_label )
	);
	
	// Add activity.
	bp_activity_add( array(
		'user_id'           => $document_data['uploaded_by'],
		'action'            => $action,
		'content'           => $content,
		'primary_link'      => bp_core_get_user_domain( $document_data['uploaded_by'] ) . bp_get_deal_room_slug() . '/',
		'component'         => 'deal_room',
		'type'              => 'deal_room_upload',
		'item_id'           => $document_id,
		'secondary_item_id' => 0,
		'recorded_time'     => bp_core_current_time(),
		'hide_sitewide'     => false,
	) );
}