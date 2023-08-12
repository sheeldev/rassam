jQuery(document).ready(function () {
	if (jQuery('#uploader').length)
	{
		var uploader = fetch_js_object('uploader');
		upclick({
			element: uploader,
			dataname: 'upload',
			action: iL['BASEURL'] + 'admin/settings/memberships/upload/',
			onstart: function(filename)
			{
				jQuery('#uploading').removeClass('hide');
				toggle_id('uploading');
			},
			oncomplete: function(response_data)
			{
				response = JSON.parse(response_data);
				if (response.error == 1)
				{
					jQuery.growl.error({ title: phrase['_error'], message: response.note, duration: 5000, size: 'large' });
				}
				else
				{
					jQuery("#form_icon")[0].options.add( new Option(response.text, response.value) );
					jQuery.growl.notice({ title: phrase['_success'], message: response.note, duration: 5000, size: 'large' });
				}
				toggle_id('uploading');
			}
		});
	}
	jQuery("#autoselect").on('change', function() {
	  	console.log( this.value );
		if (this.value == '')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
		}
		else if (this.value == 'admin' || this.value == 'ceo')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
		        checkboxes.prop('checked', true);
		}
		else if (this.value == 'mod')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#orders_orders').prop('checked', true);
			jQuery('#orders_pending').prop('checked', true);
			jQuery('#whosonline').prop('checked', true);
			jQuery('#marketplace_listings').prop('checked', true);
			jQuery('#marketplace_bids').prop('checked', true);
			jQuery('#customers').prop('checked', true);
			jQuery('#customers_questions').prop('checked', true);
			jQuery('#customers_violation').prop('checked', true);
			jQuery('#settings_censor').prop('checked', true);
			jQuery('#marketplace_feedback').prop('checked', true);
			jQuery('#settings_blacklist').prop('checked', true);
			jQuery('#marketplace_plans').prop('checked', true);
			jQuery('#marketplace_motd').prop('checked', true);
			jQuery('#stores_listings').prop('checked', true);
			jQuery('#apps').prop('checked', true);
			jQuery('#app_store').prop('checked', true);
			jQuery('#categories').prop('checked', true);
			jQuery('#categories_attributes').prop('checked', true);
			jQuery('#settings_diagnosis').prop('checked', true);
			jQuery('#marketplace_attachments').prop('checked', true);
			jQuery('#marketplace_locations').prop('checked', true);
			jQuery('#brands_overview').prop('checked', true);
			jQuery('#brands_owners').prop('checked', true);
			jQuery('#brands_listings').prop('checked', true);
		}
		else if (this.value == 'cto')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#marketplace_themes').prop('checked', true);
			jQuery('#marketplace_automation').prop('checked', true);
			jQuery('#marketplace_maintenance').prop('checked', true);
			jQuery('#settings_updates').prop('checked', true);
			jQuery('#settings_session').prop('checked', true);
			jQuery('#settings_diagnosis').prop('checked', true);
			jQuery('#settings_serverinfo').prop('checked', true);
			jQuery('#settings_license').prop('checked', true);
			jQuery('#settings_distance').prop('checked', true);
			jQuery('#settings_security').prop('checked', true);
			jQuery('#settings_shipping').prop('checked', true);
			jQuery('#settings_listings').prop('checked', true);
			jQuery('#settings_payment').prop('checked', true);
			jQuery('#settings_mail').prop('checked', true);
			jQuery('#apps').prop('checked', true);
			jQuery('#app_store').prop('checked', true);
			jQuery('#brands_owners').prop('checked', true);
			jQuery('#brands_listings').prop('checked', true);
			jQuery('#categories').prop('checked', true);
			jQuery('#categories_attributes').prop('checked', true);
			jQuery('#whosonline').prop('checked', true);
			jQuery('#marketplace_listings').prop('checked', true);
			jQuery('#marketplace_bids').prop('checked', true);
			jQuery('#customers').prop('checked', true);
			jQuery('#customers_questions').prop('checked', true);
			jQuery('#customers_violation').prop('checked', true);
			jQuery('#settings_censor').prop('checked', true);
			jQuery('#marketplace_feedback').prop('checked', true);
			jQuery('#settings_blacklist').prop('checked', true);
			jQuery('#settings_registration').prop('checked', true);
			jQuery('#settings_seo').prop('checked', true);
			jQuery('#settings_attachments').prop('checked', true);
			jQuery('#settings_branding').prop('checked', true);
			jQuery('#settings_locale').prop('checked', true);
		}
		else if (this.value == 'cfo')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#accounting_overview').prop('checked', true);
			jQuery('#accounting_currency').prop('checked', true);
			jQuery('#accounting_invoices').prop('checked', true);
			jQuery('#accounting_escrow').prop('checked', true);
			jQuery('#accounting_deposits').prop('checked', true);
			jQuery('#accounting_withdraws').prop('checked', true);
			jQuery('#accounting_creditcards').prop('checked', true);
			jQuery('#accounting_bankaccounts').prop('checked', true);
			jQuery('#settings_invoice').prop('checked', true);
			jQuery('#settings_payment').prop('checked', true);
			jQuery('#settings_tax').prop('checked', true);
			jQuery('#settings_currency').prop('checked', true);
			jQuery('#marketplace_fees').prop('checked', true);
			jQuery('#stores_overview').prop('checked', true);
			jQuery('#stores_listings').prop('checked', true);
			jQuery('#stores_fees').prop('checked', true);
			jQuery('#settings_escrow').prop('checked', true);
			jQuery('#settings_license').prop('checked', true);
			jQuery('#marketplace_bids').prop('checked', true);
		}
		else if (this.value == 'mkd')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#customers').prop('checked', true);
			jQuery('#customers_questions').prop('checked', true);
			jQuery('#customers_bulkmailer').prop('checked', true);
			jQuery('#whosonline').prop('checked', true);
			jQuery('#marketplace_pages').prop('checked', true);
			jQuery('#marketplace_heros').prop('checked', true);
			jQuery('#marketplace_emails').prop('checked', true);
			jQuery('#marketplace_motd').prop('checked', true);
			jQuery('#marketplace_keywords').prop('checked', true);
			jQuery('#marketplace_locations').prop('checked', true);
		}
		else if (this.value == 'webdesigner')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#marketplace_themes').prop('checked', true);
			jQuery('#marketplace_heros').prop('checked', true);
			jQuery('#marketplace_emails').prop('checked', true);
		}
		else if (this.value == 'graphicdesigner')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#marketplace_heros').prop('checked', true);
		}
		else if (this.value == 'hr')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#customers').prop('checked', true);
			jQuery('#customers_questions').prop('checked', true);
			jQuery('#customers_bulkmailer').prop('checked', true);
			jQuery('#customers_violation').prop('checked', true);
			jQuery('#marketplace_feedback').prop('checked', true);
			jQuery('#whosonline').prop('checked', true);
			jQuery('#marketplace_nonprofit').prop('checked', true);
			jQuery('#settings_censor').prop('checked', true);
			jQuery('#settings_blacklist').prop('checked', true);
			jQuery('#marketplace_emails').prop('checked', true);
		}
		else if (this.value == 'translator')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#marketplace_languages').prop('checked', true);
			jQuery('#marketplace_emails').prop('checked', true);
			jQuery('#categories').prop('checked', true);
			jQuery('#categories_attributes').prop('checked', true);
			jQuery('#marketplace_pages').prop('checked', true);
		}
		else if (this.value == 'srexployee')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#customers').prop('checked', true);
			jQuery('#whosonline').prop('checked', true);
			jQuery('#marketplace_pages').prop('checked', true);
			jQuery('#marketplace_heros').prop('checked', true);
			jQuery('#marketplace_keywords').prop('checked', true);
			jQuery('#marketplace_attachments').prop('checked', true);
			jQuery('#categories').prop('checked', true);
			jQuery('#categories_attributes').prop('checked', true);
		}
		else if (this.value == 'jrexployee')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
			jQuery('#customers').prop('checked', true);
			jQuery('#whosonline').prop('checked', true);
			jQuery('#marketplace_pages').prop('checked', true);
			jQuery('#marketplace_keywords').prop('checked', true);
			jQuery('#marketplace_attachments').prop('checked', true);
		}
		else if (this.value == 'all')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', true);
		}
		else if (this.value == 'none')
		{
			var checkboxes = jQuery('input[name=acpaccess\\[\\]]');
			checkboxes.prop('checked', false);
		}

	})
});
