function submit_genai_form() {
	haserror = false;
	jQuery('#varname').removeClass('error');
	jQuery('#description').removeClass('error');
	jQuery('#prompt_text').removeClass('error');
	jQuery('#prompt_context').removeClass('error');
	jQuery('#prompt_parameters').removeClass('error');
	jQuery('#response_schema').removeClass('error');
	jQuery('#groupdiv').removeClass('error');
	jQuery('#typediv').removeClass('error');
	jQuery('#adminonlydiv').removeClass('error');
	if (jQuery('#varname').val() == '') {
		haserror = true;
		jQuery('#varname').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a unique Name' });
	}
	if (jQuery('#description').val() == '') {
		haserror = true;
		jQuery('#description').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a description' });
	}
	if (jQuery('#prompt_text').val() == '') {
		haserror = true;
		jQuery('#prompt_text').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a prompt text' });
	}
	if (jQuery('#prompt_context').val() == '') {
		haserror = true;
		jQuery('#prompt_context').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a prompt system context' });
	}
	if (jQuery('#prompt_parameters').val() == '') {
		haserror = true;
		jQuery('#prompt_parameters').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a prompt parameters' });
	}
	if (jQuery('#response_schema').val() == '') {
		haserror = true;
		jQuery('#response_schema').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a responce schema' });
	}
	if (!jQuery('#group:checked').val()) {
		haserror = true;
		jQuery('#groupdiv').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please select a group' });
	}
	if (!jQuery('#type:checked').val()) {
		haserror = true;
		jQuery('#typediv').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please select a type' });
	}
	if (!jQuery('#adminonly:checked').val()) {
		haserror = true;
		jQuery('#adminonlydiv').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please choose if only admin can run this prompt' });
	}

	if (!haserror) {
		return true;
	}
	return false;
} (jQuery);