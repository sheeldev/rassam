var input = document.getElementById( 'uploadimage' );
var infoArea = document.getElementById( 'file-upload-filename' );

input.addEventListener( 'change', showFileName );

function showFileName( event ) {
  var input = event.srcElement;
  var fileName = input.files[0].name;
  infoArea.textContent = 'File name: ' + fileName;
}

function submit_bc_form() {
	haserror = false;
	jQuery('#subscriptionid').removeClass('error');
	jQuery('#subscription-wrapper').removeClass('redborder');

	if (jQuery('#subscriptionid').val()== 0)
	{
		jQuery('#subscription-wrapper').addClass('redborder');
		jQuery('#subscriptionid').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Select Membership for this Customer'});
		return false;
	}

	var files = jQuery("#uploadimage").val();
	if (files.length <= 0)
	{
		haserror = true;
		jQuery.growl.error({title: phrase['_error'], message: 'Please select a valid image to upload'});
		return false;
	}

	if (!haserror)
	{
		return true;
	}
	return false;
}(jQuery);

function verify_acp_customer(mode)
{
//	console.log(jQuery('#fileuploader_iframe').contents().find('.files .template-download .preview').html());
	

	var haserror = false;
	jQuery('#subscription-wrapper').removeClass('redborder');
	jQuery('#subscriptionid').removeClass('error');
	jQuery('#customeraccount').removeClass('error');
	jQuery('#customername').removeClass('error');
	jQuery('#customeremail').removeClass('error');
	jQuery('#customerphone').removeClass('error');
	jQuery('#vehicle_year').removeClass('error');
	jQuery('#request_date').removeClass('error');
	jQuery('#vehicle_value').removeClass('error');
	jQuery('#user_account').removeClass('error');
	jQuery('#usedfor').removeClass('error');

	if (jQuery('#subscriptionid').val()== 0)
	{
		jQuery('#subscription-wrapper').addClass('redborder');
		jQuery('#subscriptionid').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Select Membership for this Customer'});
		return false;
	}

	if (jQuery('#customeraccount').val().length <= 0)
	{
		haserror = true;
		jQuery('#customeraccount').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Account is required'});
		return false;
	}
	if (jQuery('#customername').val().length <= 0)
	{
		haserror = true;
		jQuery('#customername').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Name is required'});
		return false;
	}
	
	if (jQuery('#customerphone').val().length <= 0)
	{
		haserror = true;
		jQuery('#customerphone').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Phone is required'});
		return false;
	}
	
	if (jQuery('#customeremail').val().length <= 0)
	{
		haserror = true;
		jQuery('#customeremail').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Email is required'});
		return false;
	}


	
	if (jQuery('#imagename_old').length)
	{
		if (jQuery('#imagename_old').val() == '')
		{
			haserror = true;
			jQuery.growl.error({title: phrase['_error'], message: 'Please select a valid image to upload'});
			return false;
		}
	}
	else
	{ // check if image exists (adding new announcement)
		var files = jQuery("#uploadimage").val();
		if (files.length <= 0)
		{
			haserror = true;
			jQuery.growl.error({title: phrase['_error'], message: 'Please select a valid image to upload'});
			return false;
		}
	}



	if (!haserror)
	{
		return true;
	}
	return false;
}

