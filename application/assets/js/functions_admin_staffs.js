function toggle_section(section) {
	if (fetch_js_object(section).classList.contains('hide')) {
		jQuery('#' + section).removeClass('hide');
	}
	else {
		jQuery('#' + section).addClass('hide');
	}

}

function add_staff_measurement(staffcode, company, customer, position, department, endpoint) {
	fetch_js_object("savingstatus").innerHTML = "Saving..."
	open_backdrop();
	if (fetch_js_object('mvalue').value == '' || fetch_js_object('mvalue').value == '0') { // check content
		haserror = true;
		jQuery('#mvalue').addClass('error');
		jQuery.growl.error({ title: phrase['_error'], message: 'Please enter a value for the measurement' });
	}
	else {
		jQuery('#mvalue').removeClass('error');
		var querystring = "&customer=" + customer + "&position=" + position + "&department=" + department + "&company=" + company + "&staffcode=" + staffcode + "&mcategory=" + fetch_js_object('mcategories').value + "&uom=" + fetch_js_object('uoms').value + "&mvalue=" + fetch_js_object('mvalue').value + "&token=" + iL['TOKEN'];
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
					jQuery.growl.error({ title: phrase['_error'], message: result.error });
					close_backdrop();
					fetch_js_object("savingstatus").innerHTML = "Error";
				}
				else {
					jQuery.growl.notice({ title: phrase['_success'], message: "A Staff Measurement has been successfully added." });
					fetch_js_object("savingstatus").innerHTML = "Saved.";
					location.replace(endpoint)
				}
			}
		}
		ajaxRequest.open('GET', iL['AJAXURL'] + '?do=addmeasurement' + querystring, true);
		ajaxRequest.send(null);
	}

}

function add_staff_size(staffcode, company, customer, position, department, endpoint) {
	fetch_js_object("savingstatus").innerHTML = "Saving..."
	open_backdrop();
	var itemtype = fetch_js_object('itemtypes').value;
	var size = fetch_js_object('sizes').value;
	var fit = fetch_js_object('fits').value;
	var cut = fetch_js_object('cuts').value;
	var bind = (fetch_js_object('bind').checked)?'1':'0';

	var querystring = "&customer=" + customer + "&position=" + position + "&department=" + department + "&company=" + company + "&staffcode=" + staffcode + "&itemtype=" + encodeURIComponent(itemtype) + "&size=" + size + "&fit=" + fit + "&cut=" + cut + "&bind=" + bind + "&token=" + iL['TOKEN'];
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
				jQuery.growl.error({ title: phrase['_error'], message: result.error });
				close_backdrop();
				fetch_js_object("savingstatus").innerHTML = "Error";
			}
			else {
				jQuery.growl.notice({ title: phrase['_success'], message: "Staff Size(s) successfully added." });
				fetch_js_object("savingstatus").innerHTML = "Saved.";
				location.replace(endpoint)
			}
		}
	}
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=addsize' + querystring, true);
	ajaxRequest.send(null);
}

function update_staff_sizes(fieldname, category, dbname, company) {
	var systemids = fetch_js_object('systemids_' + category).value;
	var field = fetch_js_object(fieldname);
	const systemIdsArray = systemids.split('|');
	systemIdsArray.forEach(systemId => {
		update_staff_details(field.value, dbname, systemId, company, '1');
	});

}

function update_staff_details(fieldname, dbname, recordid, company, param) {
	var ajaxRequest;
	var originalvalue = '';
	if (param == '0') {
		var field = fetch_js_object(fieldname);
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
			errorclass = 'error';
		}
		else {
			errorclass = 'redborder';
		}
	}
	else {
		var field = fetch_js_object(dbname + "_" + recordid);
		originalvalue = fieldname;
	}
	var etag = fetch_js_object('etag_' + recordid);


	fetch_js_object("savingstatus").innerHTML = "Saving..."
	if (param == '0') {
		jQuery('#' + fieldname).addClass('loading');
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
		if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
			var result = JSON.parse(ajaxRequest.responseText);
			if (result.response == '1') {
				if (param == '0') {
					jQuery('#' + fieldname).removeClass('loading');
					jQuery('#' + fieldname).addClass(errorclass);
				}
				jQuery.growl.error({ title: phrase['_error'], message: result.error });
				fetch_js_object("savingstatus").innerHTML = "Save";
				field.value = originalvalue;
			}
			else {
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