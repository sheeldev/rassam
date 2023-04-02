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

