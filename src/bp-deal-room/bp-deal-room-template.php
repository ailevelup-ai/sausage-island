<?php
/**
 * BuddyBoss Deal Room Template Functions.
 *
 * @package BuddyBoss\DealRoom
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Output Deal Room navigation.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_deal_room_nav() {
	$document_types = bp_deal_room_get_document_types();
	?>
	<nav class="deal-room-nav">
		<ul>
			<?php foreach ( $document_types as $type => $label ) : ?>
				<li>
					<a href="#<?php echo esc_attr( $type ); ?>" data-type="<?php echo esc_attr( $type ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}

/**
 * Get Deal Room documents for a specific type.
 *
 * @since BuddyBoss 1.0.0
 *
 * @param string $type Document type.
 * @param int    $room_id Room ID. Default 1.
 * @return array Array of documents.
 */
function bp_deal_room_get_documents( $type, $room_id = 1 ) {
	global $wpdb;
	$bp = buddypress();

	$documents = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$bp->deal_room->table_name_documents} 
			WHERE room_id = %d AND document_type = %s 
			ORDER BY date_uploaded DESC",
			$room_id,
			$type
		)
	);

	return $documents;
}

/**
 * Display Deal Room document.
 *
 * @since BuddyBoss 1.0.0
 *
 * @param object $document Document object.
 */
function bp_deal_room_display_document( $document ) {
	$user = get_user_by( 'id', $document->uploaded_by );
	?>
	<div class="deal-room-document">
		<h4><?php echo esc_html( $document->title ); ?></h4>
		<?php if ( ! empty( $document->description ) ) : ?>
			<p><?php echo esc_html( $document->description ); ?></p>
		<?php endif; ?>
		<div class="document-meta">
			<span class="uploaded-by">
				<?php printf( 
					esc_html__( 'Uploaded by %s', 'buddyboss' ), 
					esc_html( $user ? $user->display_name : __( 'Unknown', 'buddyboss' ) ) 
				); ?>
			</span>
			<span class="upload-date">
				<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $document->date_uploaded ) ) ); ?>
			</span>
		</div>
		<div class="document-actions">
			<a href="<?php echo esc_url( $document->file_url ); ?>" class="button download-document" target="_blank">
				<?php esc_html_e( 'Download', 'buddyboss' ); ?>
			</a>
			<?php if ( bp_current_user_can( 'bp_moderate' ) || get_current_user_id() == $document->uploaded_by ) : ?>
				<button class="button delete-document" data-id="<?php echo esc_attr( $document->id ); ?>">
					<?php esc_html_e( 'Delete', 'buddyboss' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Get document count for a section.
 *
 * @since BuddyBoss 1.0.0
 *
 * @param string $section Section key.
 * @return int Document count.
 */
function bp_deal_room_get_document_count( $section = '' ) {
	global $wpdb;
	$bp = buddypress();

	if ( ! isset( $bp->deal_room->table_name_documents ) ) {
		return 0;
	}

	if ( empty( $section ) ) {
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$bp->deal_room->table_name_documents}" );
	} else {
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$bp->deal_room->table_name_documents} WHERE section = %s",
			$section
		) );
	}

	return intval( $count );
}

/**
 * Add Deal Room template hooks.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_before_deal_room_content() {
	/**
	 * Fires before Deal Room content.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	do_action( 'bp_before_deal_room_content' );
}

/**
 * After Deal Room content hook.
 *
 * @since BuddyBoss 1.0.0
 */
function bp_nouveau_after_deal_room_content() {
	/**
	 * Fires after Deal Room content.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	do_action( 'bp_after_deal_room_content' );
}