function submit_sizingrule_form() {
	haserror = false;
	if (jQuery('#code').val() == '') {
		haserror = true;
		jQuery('#code').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a code' });
	}
	if (jQuery('#mcode').val() == '') {
		haserror = true;
		jQuery('#mcode').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a valid Measurement Code' });
	}
	if (jQuery('#mname').val() == '') {
		haserror = true;
		jQuery('#mname').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a valid Measurement Name' });
	}
	if (jQuery('#priority').val() == 0) {
		haserror = true;
		jQuery('#priority').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Priority must be greater than 0' });
	}
	if (!haserror) {
		return true;
	}
	return false;
} (jQuery);

function toggle_add(section) {
	if (fetch_js_object(section).classList.contains('hide')) {
		jQuery('#' + section).removeClass('hide');
	}
	else {
		jQuery('#' + section).addClass('hide');
	}

}
function add_type_line(endpoint) {
	fetch_js_object("savingstatus").innerHTML = "Saving..."
	var needsize = (fetch_js_object('needsize').checked)?'1':'0';
	var querystring = "&code=" + fetch_js_object('code').value + "&needsize=" + needsize + "&gender=" + fetch_js_object('form[gender]').value + "&category=" + fetch_js_object('form[category]').value + "&token=" + iL['TOKEN'];
	try {
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e) {
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function () {
		if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
			var result = JSON.parse(ajaxRequest.responseText);
			if (result.response == '1') {
				fetch_js_object("savingstatus").innerHTML = "Error";
			}
			else{
				fetch_js_object("savingstatus").innerHTML = "Saved.";
				location.replace(endpoint)
			}
		}
	}
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=addtypeline' + querystring, true);
	ajaxRequest.send(null);
}
function update_type_line(fieldname, dbname, recordid) {
	var ajaxRequest;
	var field = fetch_js_object(fieldname);
	var originalvalue = '';
	if (field.type == 'checkbox') {
		if (field.checked) {
			originalvalue = '1';
		}
		else {
			originalvalue = '0';
		}
	}
	else {
		originalvalue = field.value;
	}
	if (field.type == 'text') {
		field.value = "";
	}
	fetch_js_object("savingstatus").innerHTML = "Saving..."
	jQuery('#' + fieldname).addClass('loading');
	try {
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e) {
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function () {
		if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
			var result = JSON.parse(ajaxRequest.responseText);
			if (result.response == '1') {
				jQuery('#' + fieldname).removeClass('loading');
				fetch_js_object("savingstatus").innerHTML = "Error";
				field.value = result.error;
			}
			else{
				jQuery('#' + fieldname).removeClass('loading');
				fetch_js_object("savingstatus").innerHTML = "Saved";
				field.value = result.value;
			}
		}
	}
	var querystring = "&recordid=" + recordid + "&fieldname=" + dbname + "&newvalue=" + originalvalue + "&token=" + iL['TOKEN'];
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=updatetypeline' + querystring, true);
	ajaxRequest.send(null);
}
function add_category_line(endpoint) {
	fetch_js_object("savingstatus").innerHTML = "Saving..."
	var querystring = "&code=" + fetch_js_object('code').value + "&name=" + fetch_js_object('code').value + "&token=" + iL['TOKEN'];
	try {
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e) {
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function () {
		if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
			var result = JSON.parse(ajaxRequest.responseText);
			if (result.response == '1') {
				fetch_js_object("savingstatus").innerHTML = "Error";
			}
			else{
				fetch_js_object("savingstatus").innerHTML = "Saved.";
				location.replace(endpoint)
			}
		}
	}
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=addcategoryline' + querystring, true);
	ajaxRequest.send(null);
}
function update_category_line(fieldname, dbname, recordid) {
	var ajaxRequest;
	var field = fetch_js_object(fieldname);
	var originalvalue = '';
	if (field.type == 'checkbox') {
		if (field.checked) {
			originalvalue = '1';
		}
		else {
			originalvalue = '0';
		}
	}
	else {
		originalvalue = field.value;
	}
	if (field.type == 'text') {
		field.value = "";
	}
	
	fetch_js_object("savingstatus").innerHTML = "Saving..."
	jQuery('#' + fieldname).addClass('loading');
	try {
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e) {
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function () {
		if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
			var result = JSON.parse(ajaxRequest.responseText);
			if (result.response == '1') {
				jQuery('#' + fieldname).removeClass('loading');
				fetch_js_object("savingstatus").innerHTML = "Error";
				field.value = result.error;
			}
			else{
				jQuery('#' + fieldname).removeClass('loading');
				fetch_js_object("savingstatus").innerHTML = "Saved";
				field.value = result.value;
			}
		}
	}
	var querystring = "&recordid=" + recordid + "&fieldname=" + dbname + "&newvalue=" + originalvalue + "&token=" + iL['TOKEN'];
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=updatecategoryline' + querystring, true);
	ajaxRequest.send(null);
}

function update_rule_line(fieldname, dbname, recordid) {
	var ajaxRequest;
	var field = fetch_js_object(fieldname);
	var originalvalue = field.value;
	field.value = "";
	fetch_js_object("savingstatus").innerHTML = "Saving..."
	jQuery('#' + fieldname).addClass('loading');
	try {
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e) {
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function () {
		if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
			var result = JSON.parse(ajaxRequest.responseText);
			if (result.response == '1') {
				jQuery('#' + fieldname).removeClass('loading');
				fetch_js_object("savingstatus").innerHTML = "Error";
				field.value = result.error;
			}
			else{
				jQuery('#' + fieldname).removeClass('loading');
				fetch_js_object("savingstatus").innerHTML = "Saved";
				field.value = result.value;
			}
		}
	}
	var querystring = "&recordid=" + recordid + "&fieldname=" + dbname + "&newvalue=" + originalvalue + "&token=" + iL['TOKEN'];
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=updateruleline' + querystring, true);
	ajaxRequest.send(null);
}
function print_types(fieldname, genderfieldname, divtypeid) {
	var ajaxRequest;
	try {
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e) {
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function () {
		if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '') {
			var allChkBox = document.getElementsByName(fieldname);
			for (var i = 0, len = allChkBox.length; i < len; i++) {
				allChkBox[i].disabled = false;
			}
			var ajaxDisplay = fetch_js_object(divtypeid);
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
		}
	}
	var allChkBox = document.getElementsByName(fieldname);
	for (var i = 0, len = allChkBox.length; i < len; i++) {
		allChkBox[i].disabled = true;
	}
	var gendername = fetch_js_object(genderfieldname).options[fetch_js_object(genderfieldname).selectedIndex].value;
	var querystring = "&gendername=" + gendername + "&fieldname=" + fieldname + "&token=" + iL['TOKEN'];
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=showtypes' + querystring, true);
	ajaxRequest.send(null);
}

function print_impactvalues(fieldname, imapctvaluename, divimpactvalueid) {
	var ajaxRequest;
	try {
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e) {
		try {
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function () {
		if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '') {
			fetch_js_object(imapctvaluename).disabled = false;
			var ajaxDisplay = fetch_js_object(divimpactvalueid);
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
		}
	}
	fetch_js_object(imapctvaluename).disabled = true;
	var impactname = fetch_js_object(fieldname).options[fetch_js_object(fieldname).selectedIndex].value;
	var querystring = "&impactname=" + impactname + "&fieldname=" + fieldname + "&token=" + iL['TOKEN'];
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=showimpactvalues' + querystring, true);
	ajaxRequest.send(null);
}

function add_rule(fieldname) {
	var ajaxRequest;
	var rulenumber = parseInt(fetch_js_object('active_rules').value, 10) + 1;
	var divruleid = "rule-" + rulenumber;
	jQuery('#' + divruleid).removeClass('hide');
	if (rulenumber > 10) {
		jQuery.growl.error({ title: phrase['_error'], message: 'Maximum 10 Rules allowed' });
	}
	else {
		try {
			ajaxRequest = new XMLHttpRequest();
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (e) {
				try {
					ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (e) {
					return false;
				}
			}
		}
		ajaxRequest.onreadystatechange = function () {
			if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '') {
				var ajaxDisplay = fetch_js_object(divruleid);
				ajaxDisplay.innerHTML = ajaxRequest.responseText;
			}
		}
		fetch_js_object('impactdisabled').value = fetch_js_object(fieldname).value;
		fetch_js_object(fieldname).disabled = true;
		fetch_js_object('active_rules').value = rulenumber;
		var impactname = fetch_js_object(fieldname).options[fetch_js_object(fieldname).selectedIndex].value;
		var querystring = "&impactname=" + impactname + "&rulenumber=" + rulenumber + "&token=" + iL['TOKEN'];
		ajaxRequest.open('GET', iL['AJAXURL'] + '?do=showrule' + querystring, true);
		ajaxRequest.send(null);
	}

}

function remove_rule(fieldname) {
	var rulenumber = parseInt(fetch_js_object('active_rules').value, 10);
	var divruleid = "rule-" + rulenumber;
	if (rulenumber == 1) {
		jQuery.growl.error({ title: phrase['_error'], message: 'Cannot Remove First Rule' });
	}
	else {
		if (rulenumber - 1 == 1) {
			fetch_js_object(fieldname).disabled = false;
			fetch_js_object('impactdisabled').value = '';
		}
		try {
			ajaxRequest = new XMLHttpRequest();
		}
		catch (e) {
			try {
				ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (e) {
				try {
					ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (e) {
					return false;
				}
			}
		}
		ajaxRequest.onreadystatechange = function () {
			if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '') {
				var ajaxDisplay = fetch_js_object(divruleid);
				ajaxDisplay.innerHTML = ajaxRequest.responseText;
			}
		}
		ajaxRequest.open('GET', iL['AJAXURL'] + '?do=showrule&action=reset', true);
		ajaxRequest.send(null);
		jQuery('#' + divruleid).addClass('hide');
		fetch_js_object('active_rules').value = rulenumber - 1;

	}
}