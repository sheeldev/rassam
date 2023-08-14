function submit_announcement_form() {
	haserror = false;
	if (jQuery('#form_content').val() == '')
	{ // check content
		haserror = true;
		jQuery('#form_content').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Please enter the content'});
	}
	if (jQuery('#form_date').val() == '')
	{ // check date
		haserror = true;
		jQuery('#form_date').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Please enter a valid date'});
	}
	if (jQuery('#imagename_old').length)
	{
		if (jQuery('#imagename_old').val() == '')
		{
			haserror = true;
			jQuery.growl.error({title: phrase['_error'], message: 'Please select a valid image to upload'});
		}
	}
	else
	{ // check if image exists (adding new announcement)
		var files = jQuery("#uploadimage").val();
		if (files.length <= 0)
		{
			haserror = true;
			jQuery.growl.error({title: phrase['_error'], message: 'Please select a valid image to upload'});
		}
	}
	if (!haserror)
	{
		return true;
	}
	return false;
}(jQuery);