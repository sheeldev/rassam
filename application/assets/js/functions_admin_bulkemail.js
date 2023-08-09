function send_mail(from, subject, body, bodyhtml, testmode, testemail, subscriptionid)
{
	xhr = new AJAX_Handler(true);
	xhr.from = from;
	xhr.subject = subject;
	xhr.body = body;
	xhr.bodyhtml = bodyhtml;
	xhr.testmode = testmode;
	xhr.testemail = testemail;
	xhr.subscriptionid = subscriptionid;
	xhr.onreadystatechange(function() {
		if (xhr.handler.readyState == 4 && xhr.handler.status == 200)
		{
			if (xhr.handler.responseText != '')
			{
				xhr.response = xhr.handler.responseText;
				var result = JSON.parse(xhr.response);
				if (result.response == '1')
				{
					jQuery('.email-is--sending').addClass('hide');
					jQuery('.email-is--completed').removeClass('hide');
				}
				else
				{
					jQuery('.email-is--sending').addClass('hide');
					jQuery('.email-is--failed').removeClass('hide');
				}
			}
			xhr.handler.abort();
		}
	});
	xhr.send(iL['AJAXURL'], 'do=bulkmailer&from=' + encodeURIComponent(from) + '&subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body) + '&bodyhtml=' + encodeURIComponent(bodyhtml) + '&testmode=' + encodeURIComponent(testmode) + '&testemail=' + encodeURIComponent(testemail) + '&subscriptionid=' + encodeURIComponent(subscriptionid) + '&token=' + iL['TOKEN']);
}
function verify_email_bulksend()
{
	jQuery('#from').removeClass('error');
	jQuery('#subject').removeClass('error');
	jQuery('#body_plain').removeClass('error');
	if (jQuery('#from').val().length <= 0)
	{
		jQuery('#from').addClass('error');
		return false;
	}
	if (jQuery('#subject').val().length <= 0)
	{
		jQuery('#subject').addClass('error');
		return false;
	}
	if (jQuery('#body_plain').val().length <= 0)
	{
		jQuery('#body_plain').addClass('error');
		return false;
	}
	if (jQuery('#testyes').is(':checked'))
	{
		if (jQuery('#testemail').val().length <= 0)
		{
			jQuery('#testemail').addClass('error');
			return false;
		}
	}
	show_modal('', '', jQuery('#modal_dispatch').html());
	send_mail(jQuery('#from').val(), jQuery('#subject').val(), jQuery('#body_plain').val(), jQuery('#body_html').val(), jQuery('#testyes').is(':checked'), jQuery('#testemail').val(), jQuery("input[name=form_who]:checked", "#send_bulk_mail").val());
}
