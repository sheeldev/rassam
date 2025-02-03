function check_ai(staffcode, prompt, companycode) {
	var ajaxDisplay = fetch_js_object("ai_status");
	var loadingDisplay = fetch_js_object("spinnerai").innerHTML;
	ajaxDisplay.innerHTML = loadingDisplay;
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
			if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '') {
				ajaxDisplay.innerHTML = ajaxRequest.responseText;
			}
		}
	}
	var querystring = "&staffcode=" + staffcode + "&prompt=" + prompt + "&companycode=" + companycode + "&token=" + iL['TOKEN'];
	ajaxRequest.open('GET', iL['AJAXURL'] + '?do=getaiprompt' + querystring, true);
	ajaxRequest.send(null);
	jQuery('#popupshow').removeClass('hide');
    var element = document.querySelector("#ai_details");
    if (!element.classList.contains("open")) {
        jQuery('#ai_details').addClass('open');
    }
}

function closeaidetails() {
	var ajaxDisplay = fetch_js_object("ai_status");
	ajaxDisplay.innerHTML = '';
	jQuery('#ai_details').removeClass('open'); 
	jQuery('#popupshow').addClass('hide')
}