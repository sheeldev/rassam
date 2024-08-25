function verify_acp_user(mode)
{
	jQuery('#user_username').removeClass('error');
	jQuery('#user_first_name').removeClass('error');
	jQuery('#user_last_name').removeClass('error');
	jQuery('#user_dob').removeClass('error');
	jQuery('#user_email').removeClass('error');
	jQuery('#user_phone').removeClass('error');
	jQuery('#user_address1').removeClass('error');
	jQuery('#user_zip').removeClass('error');
	if (jQuery('#city-wrapper').length)
	{
		jQuery('#city-wrapper').removeClass('redborder');
	}
	jQuery('#city').removeClass('error');
	jQuery('#role-wrapper').removeClass('redborder');
	jQuery('#form_roleid').removeClass('error');
	jQuery('#customer-wrapper').removeClass('redborder');
	jQuery('#form_customerid').removeClass('error');
	jQuery('#country-wrapper').removeClass('redborder');
	jQuery('#country').removeClass('error');
	jQuery('#state-wrapper').removeClass('redborder');
	jQuery('#state').removeClass('error');
	if (mode == 'add')
	{
		jQuery('#user_password').removeClass('error');
		jQuery('#user_password2').removeClass('error');
	}
	if (jQuery('#user_username').val().length <= 0)
	{
		jQuery('#user_username').addClass('error');
		return false;
	}
	if (jQuery('#user_first_name').val().length <= 0)
	{
		jQuery('#user_first_name').addClass('error');
		return false;
	}
	if (jQuery('#user_last_name').val().length <= 0)
	{
		jQuery('#user_last_name').addClass('error');
		return false;
	}
/* 	if (jQuery('#user_dob').val().length <= 0)
	{
		jQuery('#user_dob').addClass('error');
		return false;
	}
	else
	{
		if (!check_dob(jQuery('#user_dob').val()))
		{
			jQuery.growl.error({title: phrase['_error'], message: 'The user date of birth does not appear valid.  Format: YYYY-MM-DD.  Minimum year is 1900.'});
			jQuery('#user_dob').addClass('error');
			return false;
		}
	} */

	if (jQuery('#user_email').val().length <= 0)
	{
		jQuery('#user_email').addClass('error');
		return false;
	}
	if (jQuery('#user_email').length > 0)
	{
		var email = jQuery('#user_email').val();
		if (email == '' || !(email).match(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,})?$/))
		{
			jQuery('#user_email').addClass('error');
			return false;
		}
		else
		{
			 jQuery('#user_email').removeClass('error');
		}
	}

	if (jQuery('#form_roleid').val() <= 0)
	{
		jQuery('#role-wrapper').addClass('redborder');
		jQuery('#form_roleid').addClass('error');
		return false;
	}


/* 	if (jQuery('#form_customerid').val() < 0)
	{
		jQuery('#customer-wrapper').addClass('redborder');
		jQuery('#form_customerid').addClass('error');
		return false;
	} */
	
	if (jQuery('#user_phone').val().length <= 0)
	{
		jQuery('#user_phone').addClass('error');
		return false;
	}
	if (jQuery('#user_address1').val().length <= 0)
	{
		jQuery('#user_address1').addClass('error');
		return false;
	}
	if (jQuery('#country').val().length <= 0)
	{
		jQuery('#country-wrapper').addClass('redborder');
		jQuery('#country').addClass('error');
		return false;
	}
	if (jQuery('#state').val().length <= 0)
	{
		jQuery('#state-wrapper').addClass('redborder');
		jQuery('#state').addClass('error');
		return false;
	}
	if (jQuery('#city').val().length <= 0)
	{
		if (jQuery('#city-wrapper').length)
		{
			jQuery('#city-wrapper').addClass('redborder');
		}
		jQuery('#city').addClass('error');
		return false;
	}
	if (jQuery('#user_zip').val().length <= 0)
	{
		jQuery('#user_zip').addClass('error');
		return false;
	}

	if (mode == 'add')
	{
		if (jQuery('#user_password').val().length <= 0)
		{
			jQuery('#user_password').addClass('error');
			return false;
		}
		if (jQuery('#user_password2').val().length <= 0)
		{
			jQuery('#user_password2').addClass('error');
			return false;
		}
		if (jQuery('#user_password').val() != jQuery('#user_password2').val())
		{
			jQuery.growl.error({title: phrase['_error'], message: phrase['_passwords_do_not_match']});
			return false;
		}
	}
	return true;
}
function check_dob(str)
{
	// STRING FORMAT yyyy-mm-dd
	if (str=="" || str==null){return false;}

	// m[1] is year 'YYYY' * m[2] is month 'MM' * m[3] is day 'DD'
	var m = str.match(/(\d{4})-(\d{2})-(\d{2})/);

	// STR IS NOT FIT m IS NOT OBJECT
	if ( m === null || typeof m !== 'object'){return false;}

	// CHECK m TYPE
	if (typeof m !== 'object' && m !== null && m.size!==3){return false;}

	var ret = true;
	var thisYear = new Date().getFullYear();
	var minYear = 1900;

	// YEAR CHECK
	if ( (m[1].length < 4) || m[1] < minYear || m[1] > thisYear){ret = false;}
	// MONTH CHECK
	if ( (m[2].length < 2) || m[2] < 1 || m[2] > 12){ret = false;}
	// DAY CHECK
	//if( (m[3].length < 2) || m[3] < 1 || m[3] > 31){ret = false;}
	// DAY CHECK
	if ( (m[3].length < 2) || m[3] < 1 || ((["04","06","09","11"].indexOf(m[2]) > -1 && m[3] > 30 ) || m[3] > 31)){ret = false;}
	// FEBRUARY CHECK
	if ( (m[2] == 2 && m[3] > 29) || (m[2] == 2 && m[3] > 28 && m[1]%4 != 0)){ret = false;}
	return ret;
}
function check_username(username)
{
	xhr = new AJAX_Handler(true);
	xhr.username = username;
	xhr.onreadystatechange(function() {
		if (xhr.handler.readyState == 4 && xhr.handler.status == 200)
		{
			if (xhr.handler.responseText != '')
			{
				xhr.response = xhr.handler.responseText;
				var result = JSON.parse(xhr.response);
				if (result.response == '1')
				{
					jQuery('#user_username').addClass('error');
					jQuery.growl.error({title: phrase['_error'], message: result.error});
				}
				else
				{
					jQuery('#user_username').removeClass('error');
					jQuery('#user_username').addClass('success');
				}
			}
			xhr.handler.abort();
		}
	});
	xhr.send(iL['AJAXURL'], 'do=acpcheckusername&username=' + encodeURIComponent(username) + '&token=' + iL['TOKEN']);
}
function check_email(email)
{
	xhr = new AJAX_Handler(true);
	xhr.email = email;
	xhr.onreadystatechange(function() {
		if (xhr.handler.readyState == 4 && xhr.handler.status == 200)
		{
			if (xhr.handler.responseText != '')
			{
				xhr.response = xhr.handler.responseText;
				var result = JSON.parse(xhr.response);
				if (result.response == '1')
				{
					jQuery('#user_email').addClass('error');
					jQuery.growl.error({title: phrase['_error'], message: result.error});
				}
				else
				{
					jQuery('#user_email').removeClass('error');
					jQuery('#user_email').addClass('success');
				}
			}
			xhr.handler.abort();
		}
	});
	xhr.send(iL['AJAXURL'], 'do=acpcheckemail&email=' + encodeURIComponent(email) + '&token=' + iL['TOKEN']);
}
function new_user_listener()
{
	jQuery('#user_username').on('blur paste', function() {
		var self = this;
		var username = jQuery(self).val();
		if (username != '')
		{
			check_username(username);
		}
	});
	jQuery('#user_email').on('blur paste', function() {
		var self = this;
		var email = jQuery(self).val();
		if (email != '')
		{
			check_email(email);
		}
	});
}
function update_user_listener()
{
	jQuery('#user_username').on('blur paste', function() {
		var self = this;
		var username = jQuery(self).val();
		var oldusername = jQuery('#oldusername').val();
		if (username != '' && username != oldusername)
		{
			check_username(username);
		}
		else
		{
			jQuery('#user_username').removeClass('error');
		}
	});
	jQuery('#user_email').on('blur paste', function() {
		var self = this;
		var email = jQuery(self).val();
		var oldemail = jQuery('#oldemail').val();
		if (email != '' && email != oldemail)
		{
			check_email(email);
		}
		else
		{
			jQuery('#user_email').removeClass('error');
		}
	});
}
function newapikey()
{
	var strVal = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
        var strMD5 = hex_md5(str_to_ent(strVal));
        jQuery('#user_apikey').val(strMD5);
}
