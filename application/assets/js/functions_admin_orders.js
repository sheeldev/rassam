function showOrderDetails(orderno, customerno) {  
    fetch_js_object("toggleOrder_"+orderno).innerHTML = '<img src="' + iL['CDNIMG'] + 'v5/ico_working.gif" width="13" height="13" alt="{_loading}" />';
    var querystring = "&orderno=" + orderno + "&customerno=" + customerno + "&token=" + iL['TOKEN'];
    var ajaxDisplay = fetch_js_object("orders_status");
    ajaxDisplay.innerHTML ='';
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
            ajaxDisplay.innerHTML = ajaxRequest.responseText;
            fetch_js_object("toggleOrder_"+orderno).innerHTML = '<i class="fa fa-ellipsis-h" onclick="showOrderDetails(\'' + orderno + '\',\'' + customerno + '\')" style="cursor: pointer;"></i>';
        }
    }
    ajaxRequest.open('GET', iL['AJAXURL'] + '?do=getorderdetails' + querystring, true);
    ajaxRequest.send(null);
    var element = document.querySelector("#order_details");
    if (!element.classList.contains("open")) {
        jQuery('#order_details').addClass('open');
    }
}

function showAssemblyDetails(orderno, customerno) {
    var querystring = "&orderno=" + orderno + "&customerno=" + customerno + "&token=" + iL['TOKEN'];
    var ajaxDisplay = fetch_js_object("orders_status");
    ajaxDisplay.innerHTML ='';
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
            ajaxDisplay.innerHTML = ajaxRequest.responseText;
        }
    }
    ajaxRequest.open('GET', iL['AJAXURL'] + '?do=getassemblydetails' + querystring, true);
    ajaxRequest.send(null);
    var element = document.querySelector("#order_details");
    if (!element.classList.contains("open")) {
        jQuery('#order_details').addClass('open');
    }

}
function showAssemblyScans(assemblyno, orderno, customerno) {
    var querystring = "&assemblyno=" + assemblyno + "&orderno=" + orderno + "&customerno=" + customerno + "&token=" + iL['TOKEN'];
    var ajaxDisplay = fetch_js_object("orders_status");
    ajaxDisplay.innerHTML ='';
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
            ajaxDisplay.innerHTML = ajaxRequest.responseText;
        }
    }
    ajaxRequest.open('GET', iL['AJAXURL'] + '?do=getassemblyscans' + querystring, true);
    ajaxRequest.send(null);
    var element = document.querySelector("#order_details");
    if (!element.classList.contains("open")) {
        jQuery('#order_details').addClass('open');
    }
}

function display_info_message(type, message) {
    if (type == 'critical') {
        jQuery.growl.error({ title: phrase['_error'], message: message });
    }
    else {
        jQuery.growl.warning({ title: phrase['_notice'], message: message });
    }

}