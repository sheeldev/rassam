function add_staff_measurement(staffcode, endpoint) {
	fetch_js_object("savingstatus").innerHTML = "Saving..."
	var querystring = "&staffcode=" + staffcode + "&mcategory=" + fetch_js_object('mcategories').value + "&uom=" + fetch_js_object('uoms').value + "&mvalue=" + fetch_js_object('mvalue').value + "&token=" + iL['TOKEN'];
	alert (querystring);
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
                jQuery.growl.error({title: phrase['_error'], message: result.error});
				fetch_js_object("savingstatus").innerHTML = "Error";
			}
			else{
				fetch_js_object("savingstatus").innerHTML = "Saved.";
				location.replace(endpoint)
			}
		}
	}
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=addmeasurement' + querystring, true);
	ajaxRequest.send(null);
}

function update_staff_details(fieldname, dbname, recordid, company, etagparam) {
	var ajaxRequest;
	var field = fetch_js_object(fieldname);
    var etag = fetch_js_object('etag_'+ recordid);
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
        errorclass='error';
	}
    else {
        errorclass='redborder';  
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
                jQuery('#' + fieldname).addClass(errorclass);

                jQuery.growl.error({title: phrase['_error'], message: result.error});
				fetch_js_object("savingstatus").innerHTML = "Save";
				field.value =originalvalue;
			}
			else{
				fetch_js_object("savingstatus").innerHTML = "Saved";
                etag.value = result.etag;
				field.value = result.value;
			}
		}
	}
	var querystring = "&recordid=" + recordid + "&fieldname=" + dbname + "&newvalue=" + originalvalue + "&company=" + company + "&etag=" + etag.value + "&token=" + iL['TOKEN'];
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=updatestaffdetails' + querystring, true);
	ajaxRequest.send(null);
}