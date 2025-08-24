<?php
/**
 * BuddyBoss Deal Room Filters.
 *
 * @package BuddyBoss\DealRoom
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Register Deal Room component activation hook.
 */
add_action( 'bp_activation', 'bp_deal_room_activation' );

/**
 * Deal Room activation tasks.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_activation() {
	// Create tables.
	bp_deal_room_create_tables();
	
	// Add investor role.
	bp_deal_room_add_investor_role();
	
	// Create default groups.
	bp_deal_room_create_default_groups();
}

/**
 * Restrict Deal Room access.
 */
add_action( 'bp_actions', 'bp_deal_room_restrict_access' );

/**
 * Restrict access to Deal Room for non-authorized users.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_restrict_access() {
	if ( bp_is_deal_room_component() && ! bp_deal_room_user_has_access() ) {
		bp_core_add_message( __( 'You do not have access to the Deal Room.', 'buddyboss' ), 'error' );
		bp_core_redirect( bp_loggedin_user_domain() );
	}
}

/**
 * Add Deal Room to components list.
 */
add_filter( 'bp_active_components', 'bp_deal_room_add_active_component' );

/**
 * Add Deal Room to active components.
 *
 * @since BuddyBoss 1.0.0
 *
 * @param array $components Active components.
 * @return array Modified components.
 */
function bp_deal_room_add_active_component( $components ) {
	$components['deal_room'] = '1';
	return $components;
}

/**
 * Handle AJAX document upload.
 */
add_action( 'wp_ajax_bp_deal_room_upload_document', 'bp_deal_room_handle_upload' );

/**
 * Handle document upload via AJAX.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_handle_upload() {
	// Check nonce.
	check_ajax_referer( 'bp-deal-room-upload', 'nonce' );

	// Check access.
	if ( ! bp_deal_room_user_has_access() ) {
		wp_die( __( 'Access denied.', 'buddyboss' ) );
	}

	// Get document type.
	$document_type = isset( $_POST['document_type'] ) ? sanitize_key( $_POST['document_type'] ) : '';
	$valid_types = array_keys( bp_deal_room_get_document_types() );
	
	if ( ! in_array( $document_type, $valid_types, true ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid document type.', 'buddyboss' ) ) );
	}

	// Handle file upload.
	if ( ! isset( $_FILES['document'] ) ) {
		wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'buddyboss' ) ) );
	}

	// Use WordPress media handling.
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	$file = $_FILES['document'];
	$upload = wp_handle_upload( $file, array( 'test_form' => false ) );

	if ( isset( $upload['error'] ) ) {
		wp_send_json_error( array( 'message' => $upload['error'] ) );
	}

	// Save document to database.
	global $wpdb;
	$bp = buddypress();

	$data = array(
		'room_id'       => 1, // Default room.
		'document_type' => $document_type,
		'document_id'   => 0, // Can be linked to attachment ID if needed.
		'title'         => sanitize_text_field( $_POST['title'] ),
		'description'   => sanitize_textarea_field( $_POST['description'] ),
		'file_url'      => $upload['url'],
		'uploaded_by'   => get_current_user_id(),
		'date_uploaded' => current_time( 'mysql' ),
	);

	$wpdb->insert( $bp->deal_room->table_name_documents, $data );
	$document_id = $wpdb->insert_id;

	// Send email notifications if enabled.
	if ( 'yes' === get_option( 'bp_deal_room_email_notifications', 'yes' ) ) {
		bp_deal_room_send_upload_notification( $document_id, $data );
	}

	// Add to activity stream.
	if ( bp_is_active( 'activity' ) ) {
		bp_deal_room_add_upload_activity( $document_id, $data );
	}

	wp_send_json_success( array( 'message' => __( 'Document uploaded successfully.', 'buddyboss' ) ) );
}

/**
 * Handle AJAX document deletion.
 */
add_action( 'wp_ajax_bp_deal_room_delete_document', 'bp_deal_room_handle_delete' );

/**
 * Handle document deletion via AJAX.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_handle_delete() {
	// Check nonce.
	check_ajax_referer( 'bp-deal-room-delete', 'nonce' );

	// Check access.
	if ( ! bp_deal_room_user_has_access() ) {
		wp_die( __( 'Access denied.', 'buddyboss' ) );
	}

	$document_id = isset( $_POST['document_id'] ) ? absint( $_POST['document_id'] ) : 0;
	if ( ! $document_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid document ID.', 'buddyboss' ) ) );
	}

	global $wpdb;
	$bp = buddypress();

	// Get document details.
	$document = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$bp->deal_room->table_name_documents} WHERE id = %d",
			$document_id
		)
	);

	if ( ! $document ) {
		wp_send_json_error( array( 'message' => __( 'Document not found.', 'buddyboss' ) ) );
	}

	// Check permission.
	if ( ! bp_current_user_can( 'bp_moderate' ) && get_current_user_id() != $document->uploaded_by ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to delete this document.', 'buddyboss' ) ) );
	}

	// Delete from database.
	$wpdb->delete(
		$bp->deal_room->table_name_documents,
		array( 'id' => $document_id ),
		array( '%d' )
	);

	wp_send_json_success( array( 'message' => __( 'Document deleted successfully.', 'buddyboss' ) ) );
}

/**
 * Handle AJAX document loading.
 */
add_action( 'wp_ajax_bp_deal_room_load_documents', 'bp_deal_room_load_documents' );

/**
 * Load documents via AJAX.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_load_documents() {
	// Check nonce.
	check_ajax_referer( 'bp-deal-room', 'nonce' );

	// Check access.
	if ( ! bp_deal_room_user_has_access() ) {
		wp_die( __( 'Access denied.', 'buddyboss' ) );
	}

	$document_type = isset( $_POST['document_type'] ) ? sanitize_key( $_POST['document_type'] ) : '';
	$section = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : '';

	global $wpdb;
	$bp = buddypress();

	// Build query.
	$where = array( '1=1' );
	if ( ! empty( $document_type ) ) {
		$where[] = $wpdb->prepare( 'document_type = %s', $document_type );
	}
	if ( ! empty( $section ) ) {
		$where[] = $wpdb->prepare( 'section = %s', $section );
	}

	$documents = $wpdb->get_results(
		"SELECT * FROM {$bp->deal_room->table_name_documents} 
		WHERE " . implode( ' AND ', $where ) . " 
		ORDER BY date_uploaded DESC"
	);

	ob_start();
	if ( ! empty( $documents ) ) {
		foreach ( $documents as $document ) {
			bp_deal_room_display_document( $document );
		}
	} else {
		echo '<p class="no-documents">' . esc_html__( 'No documents found.', 'buddyboss' ) . '</p>';
	}
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}

/**
 * Enqueue Deal Room scripts and styles.
 */
add_action( 'bp_enqueue_scripts', 'bp_deal_room_enqueue_scripts' );

/**
 * Enqueue scripts and styles for Deal Room.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_enqueue_scripts() {
	if ( ! bp_is_deal_room_component() ) {
		return;
	}

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Enqueue CSS.
	wp_enqueue_style(
		'bp-deal-room',
		plugins_url( 'css/deal-room.css', dirname( __FILE__ ) ),
		array(),
		bp_get_version()
	);

	// Enqueue JavaScript.
	wp_enqueue_script(
		'bp-deal-room',
		plugins_url( 'js/deal-room.js', dirname( __FILE__ ) ),
		array( 'jquery' ),
		bp_get_version(),
		true
	);

	// Localize script.
	wp_localize_script(
		'bp-deal-room',
		'BP_Deal_Room_Vars',
		array(
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'nonce'               => wp_create_nonce( 'bp-deal-room' ),
			'delete_nonce'        => wp_create_nonce( 'bp-deal-room-delete' ),
			'uploading_text'      => __( 'Uploading...', 'buddyboss' ),
			'error_text'          => __( 'An error occurred. Please try again.', 'buddyboss' ),
			'no_documents_text'   => __( 'No documents found.', 'buddyboss' ),
			'confirm_delete_text' => __( 'Are you sure you want to delete this document?', 'buddyboss' ),
		)
	);
}