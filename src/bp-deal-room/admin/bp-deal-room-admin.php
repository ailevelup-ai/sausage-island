<?php
/**
 * BuddyBoss Deal Room Admin.
 *
 * @package BuddyBoss\DealRoom\Admin
 * @since BuddyBoss 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Deal Room Admin Class.
 *
 * @since BuddyBoss 1.0.0
 */
class BP_Deal_Room_Admin {

	/**
	 * Constructor.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function __construct() {
		add_action( 'bp_admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'bp_admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Add admin menu.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function admin_menu() {
		add_submenu_page(
			'buddyboss-platform',
			__( 'Level 5 Deal Room', 'buddyboss' ),
			__( 'Deal Room', 'buddyboss' ),
			'manage_options',
			'bp-deal-room',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Admin init.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function admin_init() {
		// Register settings.
		register_setting( 'bp_deal_room_settings', 'bp_deal_room_access_list' );
		register_setting( 'bp_deal_room_settings', 'bp_deal_room_email_notifications' );
		register_setting( 'bp_deal_room_settings', 'bp_deal_room_max_file_size' );
	}

	/**
	 * Admin page content.
	 *
	 * @since BuddyBoss 1.0.0
	 */
	public function admin_page() {
		// Handle form submission.
		if ( isset( $_POST['bp_deal_room_setup'] ) && check_admin_referer( 'bp_deal_room_setup' ) ) {
			// Run activation tasks.
			bp_deal_room_create_tables();
			bp_deal_room_add_investor_role();
			bp_deal_room_create_default_groups();
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Level 5 Deal Room setup completed!', 'buddyboss' ) . '</p></div>';
		}

		// Get settings.
		$access_list = get_option( 'bp_deal_room_access_list', array() );
		$email_notifications = get_option( 'bp_deal_room_email_notifications', 'yes' );
		$max_file_size = get_option( 'bp_deal_room_max_file_size', 10 );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Level 5 Deal Room Settings', 'buddyboss' ); ?></h1>

			<div class="bp-admin-card section-deal-room-setup">
				<h2><?php esc_html_e( 'Setup', 'buddyboss' ); ?></h2>
				<form method="post" action="">
					<?php wp_nonce_field( 'bp_deal_room_setup' ); ?>
					<p><?php esc_html_e( 'Click the button below to set up the Deal Room tables, create groups, and add the investor role.', 'buddyboss' ); ?></p>
					<p class="submit">
						<input type="submit" name="bp_deal_room_setup" class="button button-primary" value="<?php esc_attr_e( 'Run Setup', 'buddyboss' ); ?>">
					</p>
				</form>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'bp_deal_room_settings' ); ?>

				<div class="bp-admin-card section-access-control">
					<h2><?php esc_html_e( 'Access Control', 'buddyboss' ); ?></h2>
					
					<table class="form-table">
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'User Access List', 'buddyboss' ); ?></label>
							</th>
							<td>
								<p><?php esc_html_e( 'Users with the "Investor" role automatically have access. You can grant additional users access here.', 'buddyboss' ); ?></p>
								<?php
								$users = get_users( array( 'fields' => array( 'ID', 'display_name', 'user_email' ) ) );
								?>
								<select name="bp_deal_room_access_list[]" multiple style="width: 100%; max-width: 400px; height: 200px;">
									<?php foreach ( $users as $user ) : ?>
										<option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( in_array( $user->ID, $access_list ) ); ?>>
											<?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Hold Ctrl/Cmd to select multiple users.', 'buddyboss' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<div class="bp-admin-card section-settings">
					<h2><?php esc_html_e( 'General Settings', 'buddyboss' ); ?></h2>
					
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="bp_deal_room_email_notifications"><?php esc_html_e( 'Email Notifications', 'buddyboss' ); ?></label>
							</th>
							<td>
								<select name="bp_deal_room_email_notifications" id="bp_deal_room_email_notifications">
									<option value="yes" <?php selected( $email_notifications, 'yes' ); ?>><?php esc_html_e( 'Enabled', 'buddyboss' ); ?></option>
									<option value="no" <?php selected( $email_notifications, 'no' ); ?>><?php esc_html_e( 'Disabled', 'buddyboss' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Send email notifications when new documents are uploaded.', 'buddyboss' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="bp_deal_room_max_file_size"><?php esc_html_e( 'Maximum File Size (MB)', 'buddyboss' ); ?></label>
							</th>
							<td>
								<input type="number" name="bp_deal_room_max_file_size" id="bp_deal_room_max_file_size" value="<?php echo esc_attr( $max_file_size ); ?>" min="1" max="100">
								<p class="description"><?php esc_html_e( 'Maximum file size for document uploads in megabytes.', 'buddyboss' ); ?></p>
							</td>
						</tr>
					</table>
				</div>

				<div class="bp-admin-card section-statistics">
					<h2><?php esc_html_e( 'Statistics', 'buddyboss' ); ?></h2>
					<?php
					global $wpdb;
					$bp = buddypress();
					
					if ( isset( $bp->deal_room->table_name_documents ) ) {
						$total_documents = $wpdb->get_var( "SELECT COUNT(*) FROM {$bp->deal_room->table_name_documents}" );
						$sections = bp_deal_room_get_level5_sections();
						?>
						<p><?php printf( esc_html__( 'Total Documents: %d', 'buddyboss' ), $total_documents ); ?></p>
						
						<h3><?php esc_html_e( 'Documents by Section', 'buddyboss' ); ?></h3>
						<ul>
							<?php foreach ( $sections as $section_key => $section_name ) : 
								$count = bp_deal_room_get_document_count( $section_key );
								?>
								<li><?php echo esc_html( $section_name ); ?>: <?php echo esc_html( $count ); ?></li>
							<?php endforeach; ?>
						</ul>
						
						<h3><?php esc_html_e( 'Documents by Type', 'buddyboss' ); ?></h3>
						<ul>
							<?php
							$document_types = bp_deal_room_get_document_types();
							foreach ( $document_types as $type => $label ) :
								$count = $wpdb->get_var( $wpdb->prepare(
									"SELECT COUNT(*) FROM {$bp->deal_room->table_name_documents} WHERE document_type = %s",
									$type
								) );
								?>
								<li><?php echo esc_html( $label ); ?>: <?php echo esc_html( $count ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php } else { ?>
						<p><?php esc_html_e( 'Deal Room tables not found. Please run setup first.', 'buddyboss' ); ?></p>
					<?php } ?>
				</div>

				<p class="submit">
					<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'buddyboss' ); ?>">
				</p>
			</form>
		</div>

		<style>
			.bp-admin-card {
				background: #fff;
				border: 1px solid #ccd0d4;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
				margin: 20px 0;
				padding: 20px;
			}
			.bp-admin-card h2 {
				margin-top: 0;
			}
			.form-table th {
				width: 200px;
			}
		</style>
		<?php
	}
}

// Initialize admin.
if ( is_admin() ) {
	new BP_Deal_Room_Admin();
}