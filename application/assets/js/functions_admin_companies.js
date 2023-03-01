function verify_acp_company(mode)
{
//	console.log(jQuery('#fileuploader_iframe').contents().find('.files .template-download .preview').html());
	

	var haserror = false;
	jQuery('#subscription-wrapper').removeClass('redborder');
	jQuery('#subscriptionid').removeClass('error');
	jQuery('#companyaccount').removeClass('error');
	jQuery('#companyname').removeClass('error');
	jQuery('#companyemail').removeClass('error');
	jQuery('#companyphone').removeClass('error');
	jQuery('#vehicle_year').removeClass('error');
	jQuery('#request_date').removeClass('error');
	jQuery('#vehicle_value').removeClass('error');
	jQuery('#user_account').removeClass('error');
	jQuery('#usedfor').removeClass('error');

	if (jQuery('#subscriptionid').val()== 0)
	{
		jQuery('#subscription-wrapper').addClass('redborder');
		jQuery('#subscriptionid').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Select Membership for this Company'});
		return false;
	}

	if (jQuery('#companyaccount').val().length <= 0)
	{
		haserror = true;
		jQuery('#companyaccount').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Account is required'});
		return false;
	}
	if (jQuery('#companyname').val().length <= 0)
	{
		haserror = true;
		jQuery('#companyname').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Name is required'});
		return false;
	}
	
	if (jQuery('#companyphone').val().length <= 0)
	{
		haserror = true;
		jQuery('#companyphone').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Phone is required'});
		return false;
	}
	
	if (jQuery('#companyemail').val().length <= 0)
	{
		haserror = true;
		jQuery('#companyemail').addClass('error');
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

