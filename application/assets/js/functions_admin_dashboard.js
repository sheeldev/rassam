function get_events_analysis(category, periodcode, periodname) {
    fetch_js_object("eventanaysis").innerHTML = '<div class="spinnerai"></div>';
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
            var ajaxDisplay = fetch_js_object("eventanaysis");
            ajaxDisplay.innerHTML = ajaxRequest.responseText;
        }
    }
    var querystring = "&category=" + category + "&periodcode=" + periodcode + "&periodname=" + periodname + "&token=" + iL['TOKEN'];
    ajaxRequest.open('GET', iL['AJAXURL'] + '?do=geteventsanalysis' + querystring, true);
    ajaxRequest.send(null);

    //fetch_js_object("eventanalysisheader").innerHTML = phrase['_events_analysis']  + " - " + category;
    
}