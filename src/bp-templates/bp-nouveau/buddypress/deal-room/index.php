<?php
/**
 * BuddyBoss - Deal Room Index
 *
 * @package BuddyBoss\DealRoom
 * @since BuddyBoss 1.0.0
 */

?>

<div id="buddypress" class="deal-room-wrapper">

	<?php bp_nouveau_before_deal_room_content(); ?>

	<div class="deal-room-header">
		<div class="level5-branding">
			<h1 class="level5-title"><?php esc_html_e( 'Level 5', 'buddyboss' ); ?></h1>
			<p class="level5-tagline"><?php esc_html_e( 'Exclusive Investment Portal', 'buddyboss' ); ?></p>
		</div>
		
		<nav class="deal-room-nav" aria-label="<?php esc_attr_e( 'Deal Room navigation', 'buddyboss' ); ?>">
			<ul class="deal-room-nav-tabs">
				<li class="nav-tab active" data-section="overview">
					<a href="#overview"><?php esc_html_e( 'Overview', 'buddyboss' ); ?></a>
				</li>
				<li class="nav-tab" data-section="documents">
					<a href="#documents"><?php esc_html_e( 'Documents', 'buddyboss' ); ?></a>
				</li>
				<li class="nav-tab" data-section="companies">
					<a href="#companies"><?php esc_html_e( 'Portfolio Companies', 'buddyboss' ); ?></a>
				</li>
			</ul>
		</nav>
	</div>

	<div class="deal-room-content">
		
		<!-- Overview Section -->
		<div id="overview" class="deal-room-section active">
			<div class="section-header">
				<h2><?php esc_html_e( 'Welcome to Level 5 Deal Room', 'buddyboss' ); ?></h2>
				<p><?php esc_html_e( 'Access confidential documents and investment opportunities across our portfolio companies.', 'buddyboss' ); ?></p>
			</div>
			
			<div class="level5-companies-grid">
				<?php
				$sections = bp_deal_room_get_level5_sections();
				foreach ( $sections as $section_key => $section_name ) :
					$group = groups_get_group( array( 'group_id' => BP_Groups_Group::group_exists( $section_name ) ) );
					?>
					<div class="company-card" data-section="<?php echo esc_attr( $section_key ); ?>">
						<div class="company-header">
							<h3><?php echo esc_html( $section_name ); ?></h3>
							<?php if ( $group && ! empty( $group->id ) ) : ?>
								<a href="<?php echo esc_url( bp_get_group_permalink( $group ) ); ?>" class="company-group-link">
									<?php esc_html_e( 'View Group', 'buddyboss' ); ?>
								</a>
							<?php endif; ?>
						</div>
						<div class="company-stats">
							<div class="stat">
								<span class="stat-value"><?php echo bp_deal_room_get_document_count( $section_key ); ?></span>
								<span class="stat-label"><?php esc_html_e( 'Documents', 'buddyboss' ); ?></span>
							</div>
							<?php if ( $group && ! empty( $group->id ) ) : ?>
								<div class="stat">
									<span class="stat-value"><?php echo bp_get_group_total_members( $group->id ); ?></span>
									<span class="stat-label"><?php esc_html_e( 'Members', 'buddyboss' ); ?></span>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Documents Section -->
		<div id="documents" class="deal-room-section">
			<div class="section-header">
				<h2><?php esc_html_e( 'Investment Documents', 'buddyboss' ); ?></h2>
				<div class="document-filters">
					<select id="document-type-filter" class="document-filter">
						<option value=""><?php esc_html_e( 'All Document Types', 'buddyboss' ); ?></option>
						<?php
						$document_types = bp_deal_room_get_document_types();
						foreach ( $document_types as $type => $label ) :
							?>
							<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					
					<select id="company-filter" class="document-filter">
						<option value=""><?php esc_html_e( 'All Companies', 'buddyboss' ); ?></option>
						<?php foreach ( $sections as $section_key => $section_name ) : ?>
							<option value="<?php echo esc_attr( $section_key ); ?>"><?php echo esc_html( $section_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="documents-grid">
				<?php foreach ( $document_types as $type => $label ) : ?>
					<div class="document-category" data-type="<?php echo esc_attr( $type ); ?>">
						<div class="category-header">
							<h3><?php echo esc_html( $label ); ?></h3>
							<button class="button upload-document" data-type="<?php echo esc_attr( $type ); ?>">
								<i class="bb-icon-l bb-icon-upload"></i>
								<?php esc_html_e( 'Upload', 'buddyboss' ); ?>
							</button>
						</div>
						<div class="document-list" data-type="<?php echo esc_attr( $type ); ?>">
							<!-- Documents will be loaded via AJAX -->
							<div class="loading-spinner">
								<i class="bb-icon-l bb-icon-spinner animate-spin"></i>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Portfolio Companies Section -->
		<div id="companies" class="deal-room-section">
			<div class="section-header">
				<h2><?php esc_html_e( 'Portfolio Companies', 'buddyboss' ); ?></h2>
			</div>
			
			<div class="companies-detailed">
				<?php foreach ( $sections as $section_key => $section_name ) : ?>
					<div class="company-detail-card">
						<h3><?php echo esc_html( $section_name ); ?></h3>
						<?php
						// Get company description based on section
						$descriptions = array(
							'sudoself'         => __( 'Empowering individuals through self-improvement tools and personal development resources. Building the future of personal growth technology.', 'buddyboss' ),
							'bizbox'           => __( 'Revolutionary business tools and SaaS solutions for modern entrepreneurs. Streamlining operations for scaling businesses.', 'buddyboss' ),
							'level5_podcast'   => __( 'Premium podcast content and media production. Sharing insights from top entrepreneurs and thought leaders.', 'buddyboss' ),
							'sausage_software' => __( 'Cutting-edge software development and technology solutions. Building the infrastructure for tomorrow\'s digital landscape.', 'buddyboss' ),
							'trillion_club'    => __( 'Exclusive network for high-growth entrepreneurs targeting trillion-dollar markets. Where ambition meets opportunity.', 'buddyboss' ),
						);
						?>
						<p><?php echo esc_html( $descriptions[ $section_key ] ?? '' ); ?></p>
						
						<div class="company-actions">
							<?php
							$group = groups_get_group( array( 'group_id' => BP_Groups_Group::group_exists( $section_name ) ) );
							if ( $group && ! empty( $group->id ) ) :
								?>
								<a href="<?php echo esc_url( bp_get_group_permalink( $group ) ); ?>" class="button">
									<?php esc_html_e( 'View Community', 'buddyboss' ); ?>
								</a>
							<?php endif; ?>
							<button class="button view-documents" data-section="<?php echo esc_attr( $section_key ); ?>">
								<?php esc_html_e( 'View Documents', 'buddyboss' ); ?>
							</button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

	</div>

	<?php bp_nouveau_after_deal_room_content(); ?>

</div>

<!-- Upload Modal -->
<div id="deal-room-upload-modal" class="deal-room-modal" style="display: none;">
	<div class="modal-content">
		<div class="modal-header">
			<h3><?php esc_html_e( 'Upload Document', 'buddyboss' ); ?></h3>
			<button class="modal-close">&times;</button>
		</div>
		<form id="deal-room-upload-form" enctype="multipart/form-data">
			<div class="form-field">
				<label for="document-title"><?php esc_html_e( 'Document Title', 'buddyboss' ); ?></label>
				<input type="text" id="document-title" name="title" required>
			</div>
			
			<div class="form-field">
				<label for="document-description"><?php esc_html_e( 'Description', 'buddyboss' ); ?></label>
				<textarea id="document-description" name="description" rows="3"></textarea>
			</div>
			
			<div class="form-field">
				<label for="document-company"><?php esc_html_e( 'Company/Section', 'buddyboss' ); ?></label>
				<select id="document-company" name="section">
					<option value=""><?php esc_html_e( 'General', 'buddyboss' ); ?></option>
					<?php foreach ( $sections as $section_key => $section_name ) : ?>
						<option value="<?php echo esc_attr( $section_key ); ?>"><?php echo esc_html( $section_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			
			<div class="form-field">
				<label for="document-file"><?php esc_html_e( 'Select File', 'buddyboss' ); ?></label>
				<input type="file" id="document-file" name="document" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
				<p class="field-description"><?php esc_html_e( 'Accepted formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX', 'buddyboss' ); ?></p>
			</div>
			
			<input type="hidden" id="document-type" name="document_type" value="">
			<?php wp_nonce_field( 'bp-deal-room-upload', 'nonce' ); ?>
			
			<div class="modal-footer">
				<button type="button" class="button button-secondary modal-cancel"><?php esc_html_e( 'Cancel', 'buddyboss' ); ?></button>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Upload Document', 'buddyboss' ); ?></button>
			</div>
		</form>
	</div>
</div>