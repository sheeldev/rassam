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

function init_page()
{
        init_bulk_animation();
}

function update_uploded_staff()
{
        jQuery('#refreshloading').removeClass('hide');
        setTimeout(fetch_post_form(this.parentNode), 500);
        
}
function display_error_message() {
        var message = "" + jQuery('#staffuploaderrormessage').val();
        jQuery.growl.error({title: phrase['_error'], message: message});
}
function fetch_post_form(obj)
{
	
        var parameters = "do=updatebulkstaff" +
        "&id=" + jQuery('#uploadedid').val() +
	"&name=" + jQuery('#uploadedname').val() +
	"&gender=" + jQuery('#uploadedgender').val() +
        "&position=" + jQuery('#uploadedposition').val() +
        "&department=" + jQuery('#uploadeddepartment').val();
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

jQuery(document).ready(function() {
	init_page();
});

