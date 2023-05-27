function init_bulk_animation()
{
        if (jQuery('#upload').length && jQuery('#refreshloading').length && jQuery('#formbulkupload').length) {
		jQuery('#upload').click(function(e){
                     e.preventDefault();
                     jQuery('#refreshloading').removeClass('hide');
                     setTimeout("jQuery('#formbulkupload').submit();", 500);
		});
	}
}
function display_error_message(id) {
	var message = "" + jQuery('#uploaderrormessage_'+ id).val();
	if (message=='[Auto Suggest]') {
		jQuery.growl.warning({title: phrase['_notice'], message: message});
	}
	else {
		jQuery.growl.error({title: phrase['_error'], message: message});
	}
	
}
function init_page()
{
        init_bulk_animation();
}

function update_uploded_size(uploadid)
{
        jQuery('#refreshloading').removeClass('hide');
        setTimeout(fetch_size_post_form(this.parentNode, uploadid), 500);
        
}
function fetch_size_post_form(obj, id)
{
        var parameters = "do=updatebulksize" +
						"&id=" + jQuery('#uploadedid_'+ id).val() +
						"&staffcode=" + jQuery('#uploadedstaffcode_'+ id).val() +
						"&position=" + jQuery('#uploadedpositioncode_'+ id).val() +
						"&department=" + jQuery('#uploadeddepartmentcode_'+ id).val()+
						"&fit=" + jQuery('#uploadedfit_'+ id).val()+
						"&cut=" + jQuery('#uploadedcut_'+ id).val()+
						"&size=" + jQuery('#uploadedsize_'+ id).val()+
						"&type=" + jQuery('#uploadedtype_'+ id).val();
        xhr = new AJAX_Handler(true);
		xhr.send(iL['AJAXURL'], parameters);
        xhr.onreadystatechange(function() {
		if (xhr.handler.readyState == 4 && xhr.handler.status == 200)
		{
			if (xhr.handler.responseText != '')
			{
				xhr.response = xhr.handler.responseText;
				var result = JSON.parse(xhr.response);
				if (result.error == '1')
				{
                    jQuery('#refreshloading').addClass('hide');
					jQuery.growl.error({title: phrase['_error'], message: result.message});
				}
				else
				{
					jQuery('#refreshloading').addClass('hide');
					jQuery.growl.notice({title: phrase['_success'], message: result.message});
				}
			}
			xhr.handler.abort();
		}
	});
}

function update_uploded_staff(uploadid)
{
        jQuery('#refreshloading').removeClass('hide');
        setTimeout(fetch_staff_post_form(this.parentNode, uploadid), 500);
        
}
function fetch_staff_post_form(obj, id)
{
        var parameters = "do=updatebulkstaff" +
						"&id=" + jQuery('#uploadedid_'+ id).val() +
						"&name=" + jQuery('#uploadedname_'+ id).val() +
						"&gender=" + jQuery('#uploadedgender_'+ id).val() +
						"&position=" + jQuery('#uploadedposition_'+ id).val() +
						"&department=" + jQuery('#uploadeddepartment_'+ id).val();
        xhr = new AJAX_Handler(true);
		xhr.send(iL['AJAXURL'], parameters);
        xhr.onreadystatechange(function() {
		if (xhr.handler.readyState == 4 && xhr.handler.status == 200)
		{
			if (xhr.handler.responseText != '')
			{
				xhr.response = xhr.handler.responseText;
				var result = JSON.parse(xhr.response);
				if (result.error == '1')
				{
                    jQuery('#refreshloading').addClass('hide');
					jQuery.growl.error({title: phrase['_error'], message: result.message});
				}
				else
				{
					jQuery('#refreshloading').addClass('hide');
					jQuery.growl.notice({title: phrase['_success'], message: result.message});
				}
			}
			xhr.handler.abort();
		}
	});
}
function update_uploded_measurement(uploadid)
{
        jQuery('#refreshloading').removeClass('hide');
        setTimeout(fetch_measurement_post_form(this.parentNode, uploadid), 500);
        
}
function fetch_measurement_post_form(obj, id)
{
        var parameters = "do=updatebulkmeasurement" +
						"&id=" + jQuery('#uploadedid_'+ id).val() +
						"&staffcode=" + jQuery('#uploadedstaffcode_'+ id).val() +
						"&measurementcategory=" + jQuery('#uploadedmeasurementcategory_'+ id).val() +
						"&position=" + jQuery('#uploadedpositioncode_'+ id).val() +
						"&department=" + jQuery('#uploadeddepartmentcode_'+ id).val()+
						"&mvalue=" + jQuery('#uploadedvalue_'+ id).val()+
						"&uom=" + jQuery('#uploadeduom_'+ id).val();
        xhr = new AJAX_Handler(true);
		xhr.send(iL['AJAXURL'], parameters);
        xhr.onreadystatechange(function() {
		if (xhr.handler.readyState == 4 && xhr.handler.status == 200)
		{
			if (xhr.handler.responseText != '')
			{
				xhr.response = xhr.handler.responseText;
				var result = JSON.parse(xhr.response);
				if (result.error == '1')
				{
                    jQuery('#refreshloading').addClass('hide');
					jQuery.growl.error({title: phrase['_error'], message: result.message});
				}
				else
				{
					jQuery('#refreshloading').addClass('hide');
					jQuery.growl.notice({title: phrase['_success'], message: result.message});
				}
			}
			xhr.handler.abort();
		}
	});
}
function submit_measurement_form() {
	haserror = false;
	jQuery('#measurements').removeClass('error');
	jQuery('#measurement_value').removeClass('error');
	jQuery('#measurement-wrapper').removeClass('redborder');

	if (jQuery('#measurements').val()== 0)
	{
		jQuery('#measurement-wrapper').addClass('redborder');
		jQuery('#measurements').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Select Measurement Category for this Staff'});
		return false;
	}

	if (jQuery('#measurement_value').val()== '')
	{
		jQuery('#measurement_value').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Measurement Value can\'t be empty.'});
		return false;
	}


	if (!haserror)
	{
		return true;
	}
	return false;
}(jQuery);
jQuery(document).ready(function() {
	init_page();
});

