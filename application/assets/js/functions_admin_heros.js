if (jQuery('#uploader').length)
{
	var uploader = fetch_js_object('uploader');
	upclick({
		element: uploader,
		dataname: 'upload',
		action: iL['PAGEURL'] + 'upload/',
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
				jQuery("#source_url4")[0].options.add( new Option(response.text, response.value) );
				jQuery.growl.notice({ title: phrase['_success'], message: response.note, duration: 5000, size: 'large' });
			}
			toggle_id('uploading');
		}
	});
}
jQuery(document).ready(function () {
	jQuery('#heros-picture').addClass('hide');
	jQuery('#heros-editor').addClass('hide');
	jQuery('#heros-map').addClass('hide');
	jQuery('#heros-location').addClass('hide');
	jQuery('#heros-displayorder').addClass('hide');
	jQuery('#heros-theme').addClass('hide');
	jQuery("#source_url3").change(function(){
		if (this.value != '' && this.value != '{_select_a_picture}')
		{
			jQuery('#inactivatehero').removeClass('disabled');
			jQuery('#loadactivehero').removeClass('disabled');
			fetch_js_object('source_url3_id').value = this.options[this.selectedIndex].getAttribute('id');
			fetch_js_object('source_url3_folder').value = this.options[this.selectedIndex].getAttribute('folder');
		}
		else
		{
			jQuery('#inactivatehero').addClass('disabled');
			jQuery('#loadactivehero').addClass('disabled');
		}
	});
	jQuery("#source_url4").change(function() {
		if (this.value != '' && this.value != '{_select_a_picture}')
		{
			jQuery('#deletehero').removeClass('disabled');
			jQuery('#loadhero').removeClass('disabled');
		}
		else
		{
			jQuery('#deletehero').addClass('disabled');
			jQuery('#loadhero').addClass('disabled');
		}
	});
	jQuery("#loadhero").click(function() {
		jQuery('#heros-picture').removeClass('hide');
		jQuery('#heros-editor').removeClass('hide');
		jQuery('#heros-map').removeClass('hide');
		jQuery('#heros-location').removeClass('hide');
		jQuery('#heros-displayorder').removeClass('hide');
		jQuery('#heros-theme').removeClass('hide');
		gui_loadImage(document.getElementById('source_url4').value, 'insert', 'heros');
	});
	jQuery("#loadactivehero").click(function() {
		jQuery('#heros-picture').removeClass('hide');
		jQuery('#heros-editor').removeClass('hide');
		jQuery('#heros-map').removeClass('hide');
		jQuery('#heros-location').removeClass('hide');
		jQuery('#heros-displayorder').removeClass('hide');
		jQuery('#heros-theme').removeClass('hide');
		gui_loadImage(document.getElementById('source_url3').value, 'load', document.getElementById('source_url3_folder').value);
	});
});
