/**
 * Level 5 Deal Room JavaScript
 *
 * @package BuddyBoss\DealRoom
 * @since BuddyBoss 1.0.0
 */

(function($) {
	'use strict';

	// Deal Room object
	window.BP_Deal_Room = {
		init: function() {
			this.bindEvents();
			this.loadDocuments();
		},

		bindEvents: function() {
			// Navigation tabs
			$(document).on('click', '.deal-room-nav-tabs .nav-tab a', this.handleTabClick);

			// Upload button
			$(document).on('click', '.upload-document', this.showUploadModal);

			// Modal controls
			$(document).on('click', '.modal-close, .modal-cancel', this.hideModal);
			$(document).on('submit', '#deal-room-upload-form', this.handleUpload);

			// Document filters
			$(document).on('change', '#document-type-filter, #company-filter', this.filterDocuments);

			// Delete document
			$(document).on('click', '.delete-document', this.deleteDocument);

			// View documents by company
			$(document).on('click', '.view-documents', this.viewCompanyDocuments);

			// Modal backdrop click
			$(document).on('click', '.deal-room-modal', function(e) {
				if (e.target === this) {
					BP_Deal_Room.hideModal();
				}
			});
		},

		handleTabClick: function(e) {
			e.preventDefault();
			
			var $tab = $(this).parent();
			var section = $tab.data('section');

			// Update active tab
			$('.deal-room-nav-tabs .nav-tab').removeClass('active');
			$tab.addClass('active');

			// Show corresponding section
			$('.deal-room-section').removeClass('active');
			$('#' + section).addClass('active');

			// Update URL without reload
			if (history.pushState) {
				history.pushState(null, null, '#' + section);
			}
		},

		showUploadModal: function(e) {
			e.preventDefault();
			
			var documentType = $(this).data('type');
			$('#document-type').val(documentType);
			$('#deal-room-upload-modal').fadeIn(200);
		},

		hideModal: function() {
			$('.deal-room-modal').fadeOut(200);
			$('#deal-room-upload-form')[0].reset();
		},

		handleUpload: function(e) {
			e.preventDefault();

			var $form = $(this);
			var $submitBtn = $form.find('button[type="submit"]');
			var originalText = $submitBtn.text();

			// Disable submit button
			$submitBtn.prop('disabled', true).text(BP_Deal_Room_Vars.uploading_text);

			// Create FormData
			var formData = new FormData(this);
			formData.append('action', 'bp_deal_room_upload_document');

			// AJAX upload
			$.ajax({
				url: BP_Deal_Room_Vars.ajax_url,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if (response.success) {
						BP_Deal_Room.hideModal();
						BP_Deal_Room.loadDocuments();
						BP_Deal_Room.showNotice(response.data.message, 'success');
					} else {
						BP_Deal_Room.showNotice(response.data.message, 'error');
					}
				},
				error: function() {
					BP_Deal_Room.showNotice(BP_Deal_Room_Vars.error_text, 'error');
				},
				complete: function() {
					$submitBtn.prop('disabled', false).text(originalText);
				}
			});
		},

		loadDocuments: function() {
			$('.document-list').each(function() {
				var $list = $(this);
				var documentType = $list.data('type');

				$.ajax({
					url: BP_Deal_Room_Vars.ajax_url,
					type: 'POST',
					data: {
						action: 'bp_deal_room_load_documents',
						document_type: documentType,
						nonce: BP_Deal_Room_Vars.nonce
					},
					success: function(response) {
						if (response.success) {
							$list.html(response.data.html);
						} else {
							$list.html('<p class="no-documents">' + BP_Deal_Room_Vars.no_documents_text + '</p>');
						}
					}
				});
			});
		},

		filterDocuments: function() {
			var typeFilter = $('#document-type-filter').val();
			var companyFilter = $('#company-filter').val();

			$('.document-category').each(function() {
				var $category = $(this);
				var categoryType = $category.data('type');
				var show = true;

				if (typeFilter && categoryType !== typeFilter) {
					show = false;
				}

				if (show) {
					$category.show();
					// Reload documents with company filter
					BP_Deal_Room.loadFilteredDocuments($category.find('.document-list'), categoryType, companyFilter);
				} else {
					$category.hide();
				}
			});
		},

		loadFilteredDocuments: function($list, documentType, companyFilter) {
			$.ajax({
				url: BP_Deal_Room_Vars.ajax_url,
				type: 'POST',
				data: {
					action: 'bp_deal_room_load_documents',
					document_type: documentType,
					section: companyFilter,
					nonce: BP_Deal_Room_Vars.nonce
				},
				success: function(response) {
					if (response.success) {
						$list.html(response.data.html);
					} else {
						$list.html('<p class="no-documents">' + BP_Deal_Room_Vars.no_documents_text + '</p>');
					}
				}
			});
		},

		deleteDocument: function(e) {
			e.preventDefault();

			if (!confirm(BP_Deal_Room_Vars.confirm_delete_text)) {
				return;
			}

			var $button = $(this);
			var documentId = $button.data('id');

			$button.prop('disabled', true);

			$.ajax({
				url: BP_Deal_Room_Vars.ajax_url,
				type: 'POST',
				data: {
					action: 'bp_deal_room_delete_document',
					document_id: documentId,
					nonce: BP_Deal_Room_Vars.delete_nonce
				},
				success: function(response) {
					if (response.success) {
						$button.closest('.deal-room-document').fadeOut(300, function() {
							$(this).remove();
						});
						BP_Deal_Room.showNotice(response.data.message, 'success');
					} else {
						BP_Deal_Room.showNotice(response.data.message, 'error');
					}
				},
				error: function() {
					BP_Deal_Room.showNotice(BP_Deal_Room_Vars.error_text, 'error');
				},
				complete: function() {
					$button.prop('disabled', false);
				}
			});
		},

		viewCompanyDocuments: function(e) {
			e.preventDefault();

			var section = $(this).data('section');
			
			// Switch to documents tab
			$('.deal-room-nav-tabs .nav-tab[data-section="documents"] a').click();

			// Set company filter
			$('#company-filter').val(section).change();
		},

		showNotice: function(message, type) {
			var $notice = $('<div class="bp-deal-room-notice ' + type + '">' + message + '</div>');
			
			$('.deal-room-wrapper').prepend($notice);
			
			setTimeout(function() {
				$notice.fadeOut(300, function() {
					$(this).remove();
				});
			}, 3000);
		}
	};

	// Initialize on DOM ready
	$(document).ready(function() {
		if ($('.deal-room-wrapper').length) {
			BP_Deal_Room.init();

			// Handle initial hash
			var hash = window.location.hash.substr(1);
			if (hash && $('#' + hash).length) {
				$('.deal-room-nav-tabs .nav-tab[data-section="' + hash + '"] a').click();
			}
		}
	});

})(jQuery);