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