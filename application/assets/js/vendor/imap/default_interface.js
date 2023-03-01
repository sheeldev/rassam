var myimgmap, props, outputmode, imgroot;
function gui_toggleMore() {
	var parent = $('#more_actions').parent().find('.toggler');
	$('#more_actions').css({
		top: $(parent).offset().top + ($(parent).outerHeight()),
		left: $(parent).offset().left
	});
	$('#more_actions').slideToggle(200, function () {
		if ($(this).css('display') == 'none') {
			$(parent).addClass('toggler_off');
		}
		else {
			$(parent).removeClass('toggler_off');
		}
	});
}
function gui_colorChanged(obj) {
	myimgmap.pic_container.style.backgroundColor = obj.value;
	gui_toggleMore();
}
/**
*	Handles mouseover on props row.
*/
function gui_row_mouseover(e) {
	if (myimgmap.is_drawing) {return;}//exit if in drawing state
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	var obj = (myimgmap.isMSIE) ? window.event.srcElement : e.currentTarget;
	if (typeof obj.aid == 'undefined') {obj = obj.parentNode;}
	myimgmap.highlightArea(obj.aid);
}
/**
*	Handles mouseout on props row.
*/
function gui_row_mouseout(e) {
	if (myimgmap.is_drawing) {return;}//exit if in drawing state
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	var obj = (myimgmap.isMSIE) ? window.event.srcElement : e.currentTarget;
	if (typeof obj.aid == 'undefined') {obj = obj.parentNode;}
	myimgmap.blurArea(obj.aid);
}
/**
*	Handles click on props row.
*/
function gui_row_click(e) {
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	var obj = (myimgmap.isMSIE) ? window.event.srcElement : e.currentTarget;
	if (typeof obj.aid == 'undefined') {obj = obj.parentNode;}
	gui_row_select(obj.aid, false, false);
	myimgmap.currentid = obj.aid;
}
/**
*	Handles click on a property row.
*/
function gui_row_select(id, setfocus, multiple) {
	if (myimgmap.is_drawing) {return;}//exit if in drawing state
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	if (!document.getElementById('img_active_'+id)) {return;}
	gui_cb_unselect_all();
	document.getElementById('img_active_'+id).checked = 1;
	if (setfocus) {
		document.getElementById('img_active_'+id).focus();
	}
	for (var i = 0; i < props.length; i++) {
		if (props[i]) {
			props[i].style.background = '';
		}
	}
	props[id].style.background = '#e7e7e7';
}
/**
*	Handles delete keypress when focus is on the leading checkbox/radio.
*/
function gui_cb_keydown(e) {
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	var key = (myimgmap.isMSIE) ? event.keyCode : e.keyCode;
	if (key == 46) {
		myimgmap.removeArea(myimgmap.currentid);
	}
}
/**
*	Unchecks all checboxes/radios.
*/
function gui_cb_unselect_all() {
	for (var i = 0; i < props.length; i++) {
		if (props[i]) {
			document.getElementById('img_active_'+i).checked = false;
		}
	}
}
/**
*	Handles arrow keys on img_coords input field.
*	Changes the coordinate values by +/- 1 and updates the corresponding canvas area.
*/
function gui_coords_keydown(e) {
	return;
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	var key = event.keyCode;
	var obj = window.event.srcElement;
	if (key == 40 || key == 38) {
		//down or up pressed
		//get the coords
		var coords = obj.value.split(',');
		var s = getSelectionStart(obj);//helper function
		var j = 0;
		for (var i=0, le = coords.length; i<le; i++) {
			j+=coords[i].length;
			if (j > s) {
				//this is the coord we want
				if (key == 40 && coords[i] > 0) {coords[i]--;}
				if (key == 38) {coords[i]++;}
				break;
			}
			//jump one more because of comma
			j++;
		}
		obj.value = coords.join(',');
		if (obj.value != myimgmap.areas[obj.parentNode.aid].lastInput) {
			myimgmap._recalculate(obj.parentNode.aid, obj.value);//contains repaint
		}
		//set cursor back to its original position
		setSelectionRange(obj, s);
		return true;
	}
}
/**
*	Gets the position of the cursor in the input box.
*	@url	http://javascript.nwbox.com/cursor_position/
*/
function getSelectionStart(obj) {
	if (obj.createTextRange) {
		var r = document.selection.createRange().duplicate();
		r.moveEnd('character', obj.value.length);
		if (r.text === '') {return obj.value.length;}
		return obj.value.lastIndexOf(r.text);
	}
	else {
		return obj.selectionStart;
	}
}
/**
*	Sets the position of the cursor in the input box.
*	@link	http://www.codingforums.com/archive/index.php/t-90176.html
*/
function setSelectionRange(obj, start, end) {
	if (typeof end == "undefined") {end = start;}
	if (obj.setSelectionRange) {
		obj.focus(); // to make behaviour consistent with IE
		obj.setSelectionRange(start, end);
	}
	else if (obj.createTextRange) {
		var range = obj.createTextRange();
		range.collapse(true);
		range.moveEnd('character', end);
		range.moveStart('character', start);
		range.select();
	}
}
/**
*	Called when one of the properties change, and the recalculate function
*	must be called.
*/
function gui_input_change(e) {
	if (myimgmap.viewmode === 1) {return;}//exit if preview mode
	if (myimgmap.is_drawing) {return;}//exit if drawing
	var obj = (myimgmap.isMSIE) ? window.event.srcElement : e.currentTarget;
	if (obj.name == 'img_href') {
		var id = obj.parentNode.parentNode.aid;
		myimgmap.areas[id].ahref   = obj.value;
	}
	else if (obj.name == 'img_alt') {
		var id = obj.parentNode.parentNode.aid;
		myimgmap.areas[id].aalt    = obj.value;
	}
	else if (obj.name == 'img_title') {
		var id = obj.parentNode.parentNode.aid;
		myimgmap.areas[id].atitle  = obj.value;
	}
	else if (obj.name == 'img_target') {
		var id = obj.parentNode.parentNode.parentNode.aid;
		myimgmap.areas[id].atarget = obj.value;
	}
	else if (obj.name == 'img_shape') {
		var id = obj.parentNode.parentNode.parentNode.aid;
		if (myimgmap.areas[id].shape != obj.value && myimgmap.areas[id].shape != 'undefined')
		{ // shape changed, adjust coords intelligently inside _normCoords
			var coords = '';
			if (props[id]) {
				coords  =  props[id].getElementsByTagName('input')[2].value;
			}
			else {
				coords = myimgmap.areas[id].lastInput || '' ;
			}
			coords = myimgmap._normCoords(coords, obj.value, 'from'+myimgmap.areas[id].shape);
			if (props[id]) {
				props[id].getElementsByTagName('input')[2].value  = coords;
			}
			myimgmap.areas[id].shape = obj.value;
			myimgmap._recalculate(id, coords);
			myimgmap.areas[id].lastInput = coords;
		}
		else if (myimgmap.areas[id].shape == 'undefined') {
			myimgmap.nextShape = obj.value;
		}
	}
	if (myimgmap.areas[id] && myimgmap.areas[id].shape != 'undefined') {
		myimgmap._recalculate(id, props[id].getElementsByTagName('input')[2].value);
		myimgmap.fireEvent('onHtmlChanged', myimgmap.getMapHTML());//temp ## shouldnt be here
	}
}
/**
*	Called from imgmap when a new area is added.
*/
function gui_addArea(id) {
	props[id] = document.createElement('tr');
	document.getElementById('form_container').appendChild(props[id]);
	props[id].id = 'img_area_' + id;
	props[id].aid = id;
	props[id].className = 'img_area';
	//hook ROW event handlers
	myimgmap.addEvent(props[id], 'mouseover', gui_row_mouseover);
	myimgmap.addEvent(props[id], 'mouseout',  gui_row_mouseout);
	myimgmap.addEvent(props[id], 'click',     gui_row_click);
	var temp = '<td width="14.28%"><input type="text" name="img_id" class="img_id" value="' + id + '" readonly="1" style="width:41px;text-align:center" /></td>';
	temp+= '<td width="14.28%"><input type="radio" name="img_active" class="img_active" id="img_active_'+id+'" value="'+id+'"></td>';
	temp+= '<td width="14.28%"><div class="draw-select__wrapper draw-input--has-content"><select name="img_shape" class="img_shape draw-select">';
	temp+= '<option value="rect">Rectangle</option>';
	if (document.getElementById('dd_output').value != 'css') {
		temp+= '<option value="circle">Circle</option>';
		temp+= '<option value="poly">Polygon</option>';
		temp+= '<option value="bezier1">Bezier</option>';
	}
	temp+= '</select></div></td>';
	temp+= '<td width="14.28%"><input type="text" name="img_coords" class="img_coords draw-input" placeholder="Coordinates"></td>';
	temp+= '<td width="14.28%"><input type="text" name="img_href" class="img_href draw-input" placeholder="URL"></td>';
	temp+= '<td width="14.28%"><input type="text" name="img_alt" class="img_alt draw-input" placeholder="Alternative text"></td>';
	temp+= '<td width="14.28%"><div class="draw-select__wrapper draw-input--has-content"><select name="img_target" class="img_target draw-select">';
	temp+= '<option value="">&lt;not set&gt;</option>';
	temp+= '<option value="_self">this window</option>';
	temp+= '<option value="_blank">new window</option>';
	temp+= '<option value="_top">top window</option>';
	temp+= '</select></div></td>';
	props[id].innerHTML = temp;
	myimgmap.addEvent(props[id].getElementsByTagName('input')[1],  'keydown', gui_cb_keydown);
	myimgmap.addEvent(props[id].getElementsByTagName('input')[2],  'keydown', gui_coords_keydown);
	myimgmap.addEvent(props[id].getElementsByTagName('input')[2],  'change', gui_input_change);
	myimgmap.addEvent(props[id].getElementsByTagName('input')[3],  'change', gui_input_change);
	myimgmap.addEvent(props[id].getElementsByTagName('input')[4],  'change', gui_input_change);
	myimgmap.addEvent(props[id].getElementsByTagName('select')[0], 'change', gui_input_change);
	myimgmap.addEvent(props[id].getElementsByTagName('select')[1], 'change', gui_input_change);
	if (myimgmap.isSafari) {
		myimgmap.addEvent(props[id].getElementsByTagName('select')[0], 'change', gui_row_click);
		myimgmap.addEvent(props[id].getElementsByTagName('select')[1], 'change', gui_row_click);
	}
	if (myimgmap.nextShape) {props[id].getElementsByTagName('select')[0].value = myimgmap.nextShape;}
	gui_row_select(id, true);
}
/**
*	Called from imgmap when an area was removed.
*/
function gui_removeArea(id) {
	if (props[id]) {
		var pprops = props[id].parentNode;
		var lastid = pprops.lastChild.aid;
		pprops.removeChild(props[id]);
		props[id] = null;
		try {
			gui_row_select(lastid, true);
			myimgmap.currentid = lastid;
		}
		catch (err) {}
	}
}
/**
*	Called from imgmap when mode changed to a given value (preview or normal)
*/
function gui_modeChanged(mode) {
	var nodes, i;
	if (mode == 1) {
		// preview mode
		if (document.getElementById('html_container')) {
			document.getElementById('html_container').disabled = true;
		}
		// disable form elements (inputs and selects)
		nodes = document.getElementById('form_container').getElementsByTagName("input");
		for (i=0; i<nodes.length; i++) {
			nodes[i].disabled = true;
		}
		nodes = document.getElementById('form_container').getElementsByTagName("select");
		for (i=0; i<nodes.length; i++) {
			nodes[i].disabled = true;
		}
		document.getElementById('i_preview').src = imgroot + 'edit.gif';
		document.getElementById('dd_zoom').disabled = true;
		document.getElementById('dd_output').disabled = true;
	}
	else {
		// normal mode
		if (document.getElementById('html_container')) {
			document.getElementById('html_container').disabled = false;
		}
		// enable form elements (inputs and selects)
		nodes = document.getElementById('form_container').getElementsByTagName("input");
		for (i=0; i<nodes.length; i++) {
			nodes[i].disabled = false;
		}
		nodes = document.getElementById('form_container').getElementsByTagName("select");
		for (i=0; i<nodes.length; i++) {
			nodes[i].disabled = false;
		}
		document.getElementById('i_preview').src = imgroot + 'zoom.gif';
		document.getElementById('dd_zoom').disabled = false;
		document.getElementById('dd_output').disabled = false;
	}
}
/**
*	Called from imgmap with the new html code when changed.
*/
function gui_htmlChanged(str) {
	var out = document.getElementById('dd_output').value;
	if (document.getElementById('html_container')) {
		document.getElementById('html_container').value = str;
	}
}
/**
*	Called from imgmap with new status string.
*/
function gui_statusMessage(str) {
	if (document.getElementById('status_container')) {
		document.getElementById('status_container').innerHTML = str;
	}
	window.defaultStatus = str;//for IE
}
function gui_areaChanged(area) {
	var id = area.aid;
	if (props[id]) {
		if (area.shape) {props[id].getElementsByTagName('select')[0].value = area.shape;}
		if (area.lastInput) {props[id].getElementsByTagName('input')[2].value  = area.lastInput;}
		if (area.ahref) {props[id].getElementsByTagName('input')[3].value  = area.ahref;}
		if (area.aalt) {props[id].getElementsByTagName('input')[4].value  = area.aalt;}
		if (area.atarget) {props[id].getElementsByTagName('select')[1].value = area.atarget;}
	}
}
/**
*	Called when the grand HTML code loses focus, and the changes must be reflected.
*/
function gui_htmlBlur() {
	var elem = document.getElementById('html_container');
	var oldvalue = elem.getAttribute('oldvalue');
	if (oldvalue != elem.value && document.getElementById('dd_output').value == 'imagemap') {
		myimgmap.setMapHTML(elem.value);
	}
}
/**
*	Called when the optional html container gets focus.
*	We need to memorize its old value in order to be able to
*	detect changes in the code that needs to be reflected.
*/
function gui_htmlFocus() {
	var elem = document.getElementById('html_container');
	elem.setAttribute('oldvalue', elem.value);
	elem.select();
}
function gui_htmlShow() {
	toggleFieldset(document.getElementById('fieldset_html'), 1);
	document.getElementById('html_container').focus();
}
/**
*	Change the labeling mode directly in imgmap config then repaint all areas.
*/
function changelabeling(obj) {
	myimgmap.config.label = obj.value;
	myimgmap._repaintAll();
}
/**
*	Change the bounding box mode straight in imgmap config then relax all areas.
*	(Relax just repaints the borders and opacity.)
*/
function toggleBoundingBox(obj) {
	obj.checked = !obj.checked;
	obj.innerHTML = '&nbsp; bounding box';
	if (obj.checked) {
		obj.innerHTML = '&raquo; bounding box';
	}
	myimgmap.config.bounding_box = obj.checked;
	myimgmap.relaxAllAreas();
	gui_toggleMore();
}
/**
*	Toggles fieldset visibility by changing the className.
*	External css needed with the appropriate classnames.
*/
function toggleFieldset(fieldset, on) {
	if (fieldset) {
		if (fieldset.className == 'fieldset_off' || on == 1) {
			fieldset.className = '';
		}
		else {
			fieldset.className = 'fieldset_off';
		}
	}
}
function gui_selectArea(obj) {
	gui_row_select(obj.aid, true, false);
}
function gui_zoom() {
	var scale = document.getElementById('dd_zoom').value;
	var pic = document.getElementById('pic_container').getElementsByTagName('img')[0];
	if (typeof pic == 'undefined') {return false;}
	if (typeof pic.oldwidth == 'undefined' || !pic.oldwidth) {
		pic.oldwidth = pic.width;
	}
	if (typeof pic.oldheight == 'undefined' || !pic.oldheight) {
		pic.oldheight = pic.height;
	}
	pic.width  = pic.oldwidth * scale;
	pic.height = pic.oldheight * scale;
	myimgmap.scaleAllAreas(scale);
}
function print_hero_picture_info(src, mode, folder) {
	xmldata = new AJAX_Handler(true);
	xmldata.src = urlencode(src);
	xmldata.mode = urlencode(mode);
	xmldata.folder = urlencode(folder);
	xmldata.id = urlencode(fetch_js_object('source_url3_id').value);
	xmldata.cid = urlencode(fetch_js_object('source_url3_cid').value);
	xmldata.onreadystatechange(function() {
		if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200)
		{
			if (xmldata.handler.responseText != '')
			{
				var response;
				response = xmldata.handler.responseText.split('|'); // sort | imagemap
				if (xmldata.mode == 'load')
				{
					myimgmap.loadImage(iL['IMGUPLOADSCDN'] + xmldata.folder + '/' + xmldata.src, response[4], response[5]);
					fetch_js_object('pic_displayorder').innerHTML = '<input type="text" name="sort" id="sort" value="' + response[0] + '" class="draw-input" /><input type="hidden" name="img_width" id="img_width" value="' + response[4] + '"><input type="hidden" name="img_height" id="img_height" value="' + response[5] + '">';
					fetch_js_object('html_container').value = response[1];
					fetch_js_object('pic_categoryid').innerHTML = response[3];
					fetch_js_object('pic_styleid').innerHTML = response[6];
					if (response[4] >= 1200)
					{
						document.getElementById('dd_zoom').value = '0.75';
					}
					else
					{
						document.getElementById('dd_zoom').value = '1';
					}
					if (response[1] != '')
					{
						gui_htmlBlur();
					}
					gui_zoom();
				}
				else if (xmldata.mode == 'insert')
				{
					myimgmap.loadImage(iL['IMGUPLOADSCDN'] + xmldata.folder + '/' + xmldata.src, response[4], response[5]);
					fetch_js_object('pic_displayorder').innerHTML = '<input type="text" name="sort" id="sort" value="' + (parseInt(response[0]) + parseInt(10)) + '" class="draw-input" /><input type="hidden" name="img_width" id="img_width" value="' + response[4] + '"><input type="hidden" name="img_height" id="img_height" value="' + response[5] + '">';
					fetch_js_object('html_container').value = '';
					fetch_js_object('pic_categoryid').innerHTML = response[3];
					fetch_js_object('pic_styleid').innerHTML = response[6];
					if (response[4] >= 1200)
					{
						document.getElementById('dd_zoom').value = '0.75';
					}
					else
					{
						document.getElementById('dd_zoom').value = '1';
					}
					gui_zoom();
				}
			}
			xmldata.handler.abort();
		}
	});
	xmldata.send(iL['AJAXURL'], 'do=heropicture&id=' + xmldata.id + '&cid=' + xmldata.cid + '&filename=' + xmldata.src + '&mode=' + xmldata.mode + '&folder=' + xmldata.folder + '&token=' + iL['TOKEN']);
}
function gui_loadImage(src, mode, folder) {
	var pic = document.getElementById('pic_container').getElementsByTagName('img')[0];
	if (typeof pic != 'undefined')
	{
		pic.parentNode.removeChild(pic);
		delete myimgmap.pic;
	}
	if (mode == 'load')
	{ // load existing hero picture for update
		toggle_show('savebutton');
		toggle_hide('insertbutton');
	}
	else if (mode == 'insert')
	{ // insert new hero picture for addition to existing heros
		toggle_show('insertbutton');
		toggle_hide('savebutton');
	}
	print_hero_picture_info(src, mode, folder);
}
function gui_outputChanged() {
	var temp, i;
	var clipboard_enabled = (window.clipboardData || typeof air == 'object');
	var output = document.getElementById('dd_output').value;
	temp = 'This is the generated image map HTML code. ';
	temp+= 'Click into the textarea below and press Ctrl+C to copy the code to your clipboard. ';
	if (clipboard_enabled) {
		temp+= 'Alternatively you can use the clipboard icon on the right. ';
		temp+= '<img src="example1_files/clipboard.gif" onclick="gui_toClipBoard()" style="float: right; margin: 4px; cursor: pointer;"/>';
	}
	temp+= 'Please note, that you have to attach this code to your image, via the usemap property ';
	temp+= '(<a href="http://www.htmlhelp.com/reference/html40/special/map.html">read more</a>). ';
	myimgmap.setMapHTML(myimgmap.getMapHTML());
	outputmode = output;
	return true;
}
/**
*	Tries to copy imagemap output or text parameter to the clipboard.
*	If in special environment (eg AIR), use specific functions.
*	@param	text	Text to copy, otherwise html_container will be used.
*/
function gui_toClipBoard(text) {
	if (typeof text == 'undefined') {text = document.getElementById('html_container').value;}
	try {
		if (window.clipboardData) {
			// IE send-to-clipboard method.
			window.clipboardData.setData('Text', text);
		}
		else if (typeof air == 'object') {
			air.Clipboard.generalClipboard.clear();
			air.Clipboard.generalClipboard.setData("air:text", text, false);
		}
	}
	catch (err) {
		myimgmap.log("Unable to set clipboard data", 1);
	}
}
myimgmap = new imgmap({
	mode : "editor",
	custom_callbacks : {
		'onStatusMessage' : function(str) {gui_statusMessage(str);}, // to display status messages on gui
		'onHtmlChanged'   : function(str) {gui_htmlChanged(str);}, // to display updated html on gui
		'onModeChanged'   : function(mode) {gui_modeChanged(mode);}, // to switch normal and preview modes on gui
		'onAddArea'       : function(id)  {gui_addArea(id);}, // to add new form element on gui
		'onRemoveArea'    : function(id)  {gui_removeArea(id);}, // to remove form elements from gui
		'onAreaChanged'   : function(obj) {gui_areaChanged(obj);},
		'onSelectArea'    : function(obj) {gui_selectArea(obj);} // to select form element when an area is clicked
	},
	pic_container: document.getElementById('pic_container'),
	bounding_box : false
});
props = [];
imgroot = iL['IMGUPLOADS'] + 'vendor/imap/';
outputmode = 'imgmap';
gui_outputChanged();
myimgmap.addEvent(document.getElementById('html_container'), 'blur',  gui_htmlBlur);
myimgmap.addEvent(document.getElementById('html_container'), 'focus', gui_htmlFocus);
