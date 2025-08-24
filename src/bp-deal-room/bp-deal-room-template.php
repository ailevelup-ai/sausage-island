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