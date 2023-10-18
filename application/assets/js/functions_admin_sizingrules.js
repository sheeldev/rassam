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
	jQuery('#'+divruleid).removeClass('hide');
	if (rulenumber > 10) {
		jQuery.growl.error({title: phrase['_error'], message: 'Maximum 10 Rules allowed'});
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
		jQuery.growl.error({title: phrase['_error'], message: 'Cannot Remove First Rule'});
	}
	else {
		if (rulenumber - 1 == 1) {
			fetch_js_object(fieldname).disabled = false;
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
		jQuery('#'+divruleid).addClass('hide');
		fetch_js_object('active_rules').value = rulenumber - 1;

	}
}