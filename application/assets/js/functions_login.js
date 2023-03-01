function verifynotify(field1, field2, input1, input2)
{
        this.field1 = field1;
        this.field2 = field2;
        this.input1 = input1;
        this.input2 = input2;
        this.check = function()
        {
    		fetch_js_object("pwd_verify").className = 'color-grey';
                if (!this.input1 && !this.input2)
                {
                        return false;
                }
                if (!document.getElementById)
                {
                        return false;
                }
                r1 = fetch_js_object(this.input1);
                if (!r1)
                {
                        return false;
                }
		r2 = fetch_js_object(this.input2);
                if (!r2)
                {
                        return false;
                }
		if (this.field1.value == '' && this.field2.value == '')
                {
			haveerrors = 0;
			fetch_js_object("pwd_verify").className = 'color-grey';
		}
		else if (this.field1.value != '' && this.field1.value == this.field2.value)
                {
			haveerrors = 0;
			fetch_js_object("pwd_verify").className = 'color-green';
                }
                else
                {
                	fetch_js_object("pwd_verify").className = 'color-red';
                }
        }
}
function verify_quick_registration()
{
	haveerrors = 0;
	(fetch_js_object("qusername").value.length < 1) ? showImageInline("qusername", true, false) : showImageInline("qusername", false, false);
	(fetch_js_object("qfullname").value.length < 1) ? showImageInline("qfullname", true, false) : showImageInline("qfullname", false, false);
	if (fetch_js_object("qfullname").value.length > 0)
	{
		var string = fetch_js_object("qfullname").value;
		chars = string.split(' ');
		if (chars.length <= 1)
		{
			alert_js(phrase['_please_enter_full_name_including_space_quick_register']);
			haveerrors = 1;
		}
	}
	(fetch_js_object("qpassword").value.length < 1) ? showImageInline("qpassword", true, false) : showImageInline("qpassword", false, false);
	if (fetch_js_object("qpassword").value.length > 0)
	{
		var xusername = jQuery("#qusername").val();
		var xpassword = jQuery("#qpassword").val();
		if (xpassword.toLowerCase().indexOf(xusername.toLowerCase()) != -1)
		{
			alert_js(phrase['_your_password_cannot_contain_full_name']);
			showImageInline("qpassword", true, false);
			haveerrors = 1;
		}
		else
		{
			showImageInline("qpassword", false, false);
		}
	}
	(fetch_js_object("qemail").value.length < 1) ? showImageInline("qemail", true, false) : showImageInline("qemail", false, false);
	(fetch_js_object("roleid").options[fetch_js_object("roleid").selectedIndex].value == '-1') ? showInlineBorder("role-wrapper", true) : showInlineBorder("role-wrapper", false);
	if (jQuery('#agreecheck').prop("checked") == false && haveerrors == 0)
	{
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_please_agree_to_the_terms_and_conditions_of_the_marketplace'] });
  		haveerrors = 1;
	}
	if (haveerrors)
	{
		jQuery( "#submit" ).effect( "shake", {times: 2, distance: 9}, 550);
	}
	else
	{
		fetch_post_form(this.parentNode);
	}
	return (!haveerrors);
}


function verify_quick_registration_drole()
{
	haveerrors = 0;
	if (fetch_js_object("qusername").value.length < 1) {
		alert_js(phrase['_username_cannot_be_blank_quick_register']);
		showImageInline("qusername", true, false); 
		haveerrors = 1;
		return false;
	}
	else {
		showImageInline("qusername", false, false);
	}
	
	if (fetch_js_object("qpassword").value.length < 1) {
		alert_js(phrase['_password_cannot_be_blank_quick_register']);
		showInlineBorder("qpassword", true, false);
		haveerrors = 1;
		return false;
	}
	else {
		showInlineBorder("qpassword", false, false);
	}
	
	if (fetch_js_object("qpassword").value.length > 0)
	{
		var xusername = jQuery("#qusername").val();
		var xpassword = jQuery("#qpassword").val();
		if (xpassword.toLowerCase().indexOf(xusername.toLowerCase()) != -1)
		{
			alert_js(phrase['_your_password_cannot_contain_full_name']);
			showImageInline("qpassword", true, false);
			haveerrors = 1;
			return false;
		}
		else {
			showImageInline("qpassword", false, false);
		}
	}
	
	if (fetch_js_object("qfullname").value.length < 1) {
		alert_js(phrase['_fullname_cannot_be_blank_quick_register']);
		showImageInline("qfullname", true, false); 
		haveerrors = 1;
		return false;
	}
	else {
		showImageInline("qfullname", false, false);
	}
	
	
	if (fetch_js_object("qfullname").value.length > 0)
	{
		var string = fetch_js_object("qfullname").value;
		chars = string.split(' ');
		if (chars.length <= 1){
			alert_js(phrase['_please_enter_full_name_including_space_quick_register']);
			showInlineBorder("qfullname", true, false);
			haveerrors = 1;
			return false;
		}
		else {
			showInlineBorder("qfullname", false, false);
		}
	}
	
	if (fetch_js_object("qemail").value.length < 1){
		alert_js(phrase['_email_cannot_be_blank_quick_register']);
		showImageInline("qemail", true, false);
		haveerrors = 1;
		return false;
	}
	else {
		showImageInline("qemail", false, false);
	}
	
	if (jQuery('#agreecheck').prop("checked") == false && haveerrors == 0)
	{
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_please_agree_to_the_terms_and_conditions_of_the_marketplace'] });
  		haveerrors = 1;
	}
	if (haveerrors)
	{
		//jQuery( "#submit" ).effect( "shake", {times: 2, distance: 9}, 550);
	}
	else
	{
		fetch_post_form_drole(this.parentNode);
	}
	return (!haveerrors);
}

function fetch_post_form_drole(obj)
{
	var marketing = 1;
	var agreeterms = 1;
	if (jQuery('#marketing').prop("checked") == false)
	{
		marketing = 0;
	}
	if (jQuery('#agreecheck').prop("checked") == false)
	{
		agreeterms = 0;
	}
        var parameters = "do=quickregister" +
	"&qusername=" + encodeURI(fetch_js_object("qusername").value) +
	"&qfullname=" + encodeURI(fetch_js_object("qfullname").value) +
    "&qpassword=" + encodeURI(fetch_js_object("qpassword").value) +
    "&qemail=" + encodeURI(fetch_js_object("qemail").value) +
	"&qcc=" + encodeURI(fetch_js_object("qcc").value) +
	"&agreeterms=" + encodeURI(agreeterms) +
	"&emailnotify=" + encodeURI(marketing) +
	"&roleid=" + encodeURI(fetch_js_object("roleid").value);
        do_post_request(iL['AJAXURL'], parameters);
}

function fetch_post_form(obj)
{
	var marketing = 1;
	var agreeterms = 1;
	if (jQuery('#marketing').prop("checked") == false)
	{
		marketing = 0;
	}
	if (jQuery('#agreecheck').prop("checked") == false)
	{
		agreeterms = 0;
	}
        var parameters = "do=quickregister" +
	"&qusername=" + encodeURI(fetch_js_object("qusername").value) +
	"&qfullname=" + encodeURI(fetch_js_object("qfullname").value) +
        "&qpassword=" + encodeURI(fetch_js_object("qpassword").value) +
        "&qemail=" + encodeURI(fetch_js_object("qemail").value) +
	"&qcc=" + encodeURI(fetch_js_object("qcc").value) +
	"&agreeterms=" + encodeURI(agreeterms) +
	"&emailnotify=" + encodeURI(marketing) +
	"&roleid=" + encodeURI(fetch_js_object("roleid").options[fetch_js_object("roleid").selectedIndex].value);
        do_post_request(iL['AJAXURL'], parameters);
}
function do_post_request(url, parameters)
{
        http_request = false;
        if (window.XMLHttpRequest) {
                http_request = new XMLHttpRequest();
                if (http_request.overrideMimeType)
                {
                        http_request.overrideMimeType('text/html');
                }
        }
        else if (window.ActiveXObject) {
                try {
                        http_request = new ActiveXObject("Msxml2.XMLHTTP");
                }
                catch (e) {
                        try {
                                http_request = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                        catch (e) {}
                }
        }
        if (!http_request) {
                alert_js('Cannot create XMLHTTP instance');
                return false;
        }
        http_request.onreadystatechange = function() {
		if (http_request.readyState == 4 && http_request.status == 200) {
			if (http_request.responseText == '1')
	                {
				location.href = iL['BASEURL'] + 'preferences/profile/?note=way'; // who are you
	                }
			else if (http_request.responseText == '2')
			{
				location.href = iL['BASEURL'] + 'register/verification/';
			}
			else if (http_request.responseText == '3')
			{
				location.href = iL['BASEURL'] + 'register/moderation/';
			}
	                else
	                {
				jQuery.growl.error({ title: phrase['_error'], message: http_request.responseText });
				jQuery( "#submit" ).effect( "shake", {times: 2, distance: 9}, 550);
	                }
	        }
	}
        http_request.open('POST', url, true);
        http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        http_request.send(parameters);
}
function validate_email(f)
{
        haveerrors = 0;
        (f.email.value.search("@") == -1 || f.email.value.search("[.*]") == -1) ? showImageInline("email", true, false) : showImageInline("email", false, false);
        return (!haveerrors);
}
function validate_signin()
{
	haveerrors = 0;
	if (jQuery('#username').length > 0 && jQuery('#password').length > 0)
	{
		if (jQuery('#username').val() == '')
		{
			showImageInline("username", true, false);
			jQuery.growl.error({ title: phrase['_error'], message: phrase['_please_enter_correct_username'] });
			haveerrors = 1;
		}
		else
		{
			showImageInline("username", false, false);
		}
		if (jQuery('#password').val() == '')
		{
			showImageInline("password", true, false);
			jQuery.growl.error({ title: phrase['_error'], message: phrase['_please_enter_correct_password'] });
			haveerrors = 1;
		}
		else
		{
			showImageInline("password", false, false);
		}
	}
	else
	{
		haveerrors = 1;
	}
	if (haveerrors == 1)
	{
  		return false;
	}
	return true;
}
jQuery(document).ready(function(){});
