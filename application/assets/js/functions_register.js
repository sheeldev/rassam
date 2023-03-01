function location_validation()
{
	haveerrors = 0;
	(jQuery('#name').val() == '') ? showImageInline("name", true, false) : showImageInline("name", false, false);
	(jQuery('#address').val() == '') ? showImageInline("address", true, false) : showImageInline("address", false, false);
	if (!jQuery('#showcountrystatecity').hasClass('hide'))
	{
		(fetch_js_object("country").options[fetch_js_object("country").selectedIndex].value < 1) ? showInlineBorder("country-wrapper", true) : showInlineBorder("country-wrapper", false);
		(fetch_js_object("state").options[fetch_js_object("state").selectedIndex].value < 1) ? showInlineBorder("state-wrapper", true) : showInlineBorder("state-wrapper", false);

		if (jQuery('#city-wrapper').length > 0)
		{
			(fetch_js_object("city").options[fetch_js_object("city").selectedIndex].value < 1) ? showInlineBorder("city-wrapper", true) : showInlineBorder("city-wrapper", false);
		}
		else
		{
			(jQuery('#city').val() == '') ? showImageInline("city", true, false) : showImageInline("city", false, false);
		}
	}
	(jQuery('#zipcode').val() == '') ? showImageInline("zipcode", true, false) : showImageInline("zipcode", false, false);
	if (jQuery('#phone').length > 0)
	{
		(jQuery('#phone').val() == '') ? showImageInline("phone", true, false) : showImageInline("phone", false, false);
	}
	if (jQuery('#email').length > 0)
	{
		var email = jQuery('#email').val();
		(email == '' || !(email).match(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,})?$/)) ? showImageInline("email", true, false) : showImageInline("email", false, false);
	}
	if (jQuery('#turing').length > 0)
	{
		(jQuery('#turing').val() == '') ? showImageInline("turing", true, false) : showImageInline("turing", false, false);
	}
	return (!haveerrors);
}
function registration()
{
	haveerrors = 0;
	if (jQuery('#month').length > 0 && jQuery('#day').length > 0 && jQuery('#year').length > 0)
	{
		if (jQuery('#month').prop('selectedIndex') == 0) {
			jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_select_the_month_you_were_born'] });
			showInlineBorder("month", true); 
			return false;
		}
		else {
			showInlineBorder("month", false); 
		}
		if (jQuery('#day').prop('selectedIndex') == 0) {
			jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_select_the_day_you_were_born'] });
			showInlineBorder("day", true); 
			return false;
		}
		else {
			showInlineBorder("day", false); 
		}
		
		if (jQuery('#year').prop('selectedIndex') == 0) {
			jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_please_enter_the_proper_year_you_were_born_yyyy'] });
			showInlineBorder("year", true);
			return false;
		}
		else {
			showInlineBorder("year", false);
		}
	}
	if (jQuery('#agreecheck').prop("checked") == false)
	{
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_please_agree_to_the_terms_and_conditions_of_the_marketplace'] });
  		return false;
	}
	return (!haveerrors);
}
function register1()
{
	haveerrors = 0;
	var email = jQuery('#email').val();
	
	if (fetch_js_object("roleid").value.length < 1){
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_select_your_role'] });
		return false;
	}
	
	
	
	if (fetch_js_object("username").value.length < 1){
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_username_cannot_be_blank_quick_register'] });
		showImageInline("username", true, false); 
		return false;
	}
	else {
		showImageInline("username", false, false); 
	}
	
	if (email == '' || !(email).match(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,})?$/)) {
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_invalid_email'] });
		showImageInline("email", true, false); 
		return false;
	}
	else {
		showImageInline("email", false, false);
	}
	
	if (fetch_js_object("password").value.length < 1) {
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_password_cannot_be_blank_quick_register'] });
		showInlineBorder("password", true, false); 
		return false;
	}
	else {
		showInlineBorder("password", false, false);
	}
	
	if (fetch_js_object("password2").value.length < 1)
	{
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_password_cannot_be_blank_quick_register'] });
		showInlineBorder("password2", true, false); 
		return false;
	}
	else {
		showInlineBorder("password2", false, false);
	}
	
    
	if (jQuery('#password').length > 0)
	{
		var xusername = jQuery("#username").val();
		var xpassword = jQuery("#password").val();
		if (xpassword.toLowerCase().indexOf(xusername.toLowerCase()) != -1)
		{
			alert_js(phrase['_your_password_cannot_contain_username']);
			haveerrors = 1;
		}
	}
	
	if (fetch_js_object("turing").value.length < 1) {
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_security_verification_blank'] });
		showImageInline("turing", true, false); 
		return false;
	}
	else {
		showImageInline("turing", false, false); 
	}
	
    if (typeof validatecustomform === 'function')
	{
		validatecustomform(true);
	}
	if (haveerrors)
	{
		//jQuery( "#register1-button" ).effect( "shake", {times: 2, distance: 15}, 550);
	}
        return (!haveerrors);
}
function register2()
{
        haveerrors = 0;
    	if (fetch_js_object("first_name").value.length < 1) {
    		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_firstname_cannot_be_blank'] });
    		showImageInline("first_name", true, false); 
    		return false;
    	}
    	else {
    		showImageInline("first_name", false, false);
    	}
    	
    	if (fetch_js_object("last_name").value.length < 1) {
    		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_lastname_cannot_be_blank'] });
    		showImageInline("last_name", true, false); 
    		return false;
    	}
    	else {
    		showImageInline("last_name", false, false);
    	}
    	
    	if (fetch_js_object("phone").value.length < 1) {
    		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_phone_cannot_be_blank'] });
    		showImageInline("phone", true, false); 
    		return false;
    	}
    	else {
    		showImageInline("phone", false, false);
    	}
    	
    	if (fetch_js_object("address").value.length < 1) {
    		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_address_cannot_be_blank'] });
    		showImageInline("address", true, false);
    		return false;
    	}
    	else {
    		showImageInline("address", false, false);
    	}

		if (jQuery('#country').length && jQuery('#state').length)
		{
			if (fetch_js_object("country").options[fetch_js_object("country").selectedIndex].value < 1) {
				jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_select_country'] }); 
				showInlineBorder("country-wrapper", true); 
				return false; 
			}
	
			if (!jQuery('#showcountrystatecity').hasClass('hide'))
			{
				if (fetch_js_object("state").options[fetch_js_object("state").selectedIndex].value < 1) {
					jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_select_state'] }); 
					showInlineBorder("state-wrapper", true); 
					return false;
				}
				else {
					showInlineBorder("state-wrapper", false); 
				}
				if (jQuery('#city-wrapper').length > 0)
				{
					if (fetch_js_object("city").options[fetch_js_object("city").selectedIndex].value < 1) {
						jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_select_city'] }); 
						showInlineBorder("city-wrapper", true);
						return false;
					}
					else {
						showInlineBorder("city-wrapper", false);
					}
				}
				else
				{
					if (jQuery('#city').val() == '') {
						jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_city_cannot_be_blank'] }); 
						showImageInline("city", true, false);
						return false;
					}
					
					else {
						showImageInline("city", false, false);
					}
					
				}
			}
		}
		
		if (fetch_js_object("zipcode").value.length < 1) {
    		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_zipcode_cannot_be_blank'] });
    		showImageInline("zipcode", true, false);
    		return false;
    	}
    	else {
    		showImageInline("zipcode", false, false);
    	}
		
	if (typeof validatecustomform === 'function')
	{
		validatecustomform(true);
	}
	if (haveerrors)
	{
		//jQuery( "#register2-button" ).effect( "shake", {times: 2, distance: 15}, 550);
	}
        return (!haveerrors);
}
function register3()
{
        haveerrors = 1;
        if (jQuery('#subscriptionplanid').val() > 0)
        {
                haveerrors = 0;
        }
        if (haveerrors)
        {
		jQuery.growl.warning({ title: phrase['_notice'], message: phrase['_you_did_not_select_a_subscription_plan_above'] });
        }
        if (typeof validatecustomform === 'function')
	{
		validatecustomform(true);
	}
	if (haveerrors)
	{
		//jQuery( "#registerbutton" ).effect( "shake", {times: 2, distance: 15}, 550);
	}
        return (!haveerrors);
}

function choose_plan(id)
{
	fetch_js_object('subscriptionplanid').value = id;
	jQuery('.cptext').html(phrase['_select']);
	fetch_js_object('cptext_' + id).innerHTML = phrase['_selected'];
	jQuery('img.notdefault').each(function(){
		jQuery(this).attr('src', iL['CDNIMG'] + 'v5/ico_checkmark_grey_circle.png');
	});
	fetch_js_object('planicon_' + id).src = iL['CDNIMG'] + 'v5/ico_checkmark_green_circle.png';
}
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
jQuery(document).ready(function()
{
	jQuery("#register-button").click(function(e)
	{
		if (registration() == false)
		{
			//jQuery('#register-button').effect('shake', {times: 2, distance: 9}, 550);
			return false;
		}
		else
		{
			jQuery("#register").submit();
		}
		e.preventDefault();
	});
	jQuery("#register1-button").click(function(e)
	{
		haveerrors = 0;
		if (!register1())
		{
			haveerrors = 1;
		}
		if (haveerrors === 1)
		{
			//jQuery('#register1-button').effect('shake', {times: 2, distance: 9}, 550);
			return false;
		}
		else
		{
			jQuery("#register1").submit();
		}
		e.preventDefault();
	});
	jQuery("#register2-button").click(function(e)
	{
		haveerrors = 0;
		if (!register2())
		{
			haveerrors = 1;
		}
		if (haveerrors === 1)
		{
			//jQuery('#register2-button').effect('shake', {times: 2, distance: 9}, 550);
			return false;
		}
		else
		{
			jQuery("#register2").submit();
		}
		e.preventDefault();
	});
	jQuery("#register3-button").click(function(e)
	{
		jQuery('#refreshloading').removeClass('hide');
		haveerrors = 0;
		if (!register3())
		{
			haveerrors = 1;
		}
		if (haveerrors === 1)
		{
			jQuery('#refreshloading').addClass('hide');
			//jQuery('#register3-button').effect('shake', {times: 2, distance: 9}, 550);
			return false;
		}
		else
		{
			jQuery('#register3-button').attr('disabled', true);
			jQuery('#register3-button').addClass('disabled');
			setTimeout(function() {
				jQuery("#register3").submit();
			}, 2000);
		}
		e.preventDefault();
	});
	jQuery("#save-shipping-button").click(function(e)
	{
		jQuery('#refreshloading').removeClass('hide');
		haveerrors = 0;
		if (!location_validation())
		{
			haveerrors = 1;
		}
		if (haveerrors === 1)
		{
			jQuery('#refreshloading').addClass('hide');
			jQuery('#save-shipping-button').effect('shake', {times: 2, distance: 9}, 550);
			return false;
		}
		else
		{
			jQuery('#save-shipping-button').attr('disabled', true);
			jQuery('#save-shipping-button').addClass('disabled');
			setTimeout(function() {
				jQuery("#location-form").submit();
			}, 2000);
		}
		e.preventDefault();
	});
	jQuery("#update-shipping-button").click(function(e)
	{
		haveerrors = 0;
		if (!location_validation())
		{
			haveerrors = 1;
		}
		if (haveerrors === 1)
		{
			jQuery('#update-shipping-button').effect('shake', {times: 2, distance: 9}, 550);
			return false;
		}
		else
		{
			jQuery("#location-form").submit();
		}
		e.preventDefault();
	});
	jQuery("#save-billing-button").click(function(e)
	{
		haveerrors = 0;
		if (!location_validation())
		{
			haveerrors = 1;
		}
		if (haveerrors === 1)
		{
			jQuery('#save-billing-button').effect('shake', {times: 2, distance: 9}, 550);
			return false;
		}
		else
		{
			jQuery("#location-form").submit();
		}
		e.preventDefault();
	});
	(function() {
		jQuery("#phone").mask('(000) 000-0000', {placeholder: "(___) ___-____"});
	}());
});
