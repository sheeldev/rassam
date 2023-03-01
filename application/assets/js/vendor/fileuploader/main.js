/*
* jQuery File Upload Plugin 7.0
* https://github.com/blueimp/jQuery-File-Upload
*
* Copyright 2010, Sebastian Tschan
* https://blueimp.net
*
* Licensed under the MIT license:
* http://www.opensource.org/licenses/MIT
*/
function do_upload()
{
	var resultFiles = {};
	var filesuploaded = 0;
	var existingfilesuploaded = 0;
	var freefiles = 0;
	var listingid = 0;
	var costperupload = 0;
	var maxfiles = 1;
	var attachtype = 'itemphoto';
	var ajaxurl = '';
	var update = false;
	if (parent.top.jQuery("#fee13").length) {
		update = true;
	}
	if (jQuery("#project_id")) {
		listingid = jQuery("#project_id").val();
	}
	if (jQuery("#attachtype")) {
		attachtype = jQuery("#attachtype").val();
	}
	if (jQuery("#maxfiles")) {
		maxfiles = parseInt(jQuery("#maxfiles").val());
	}
	if (jQuery("#ajaxurl")) {
		ajaxurl = jQuery("#ajaxurl").val();
	}
	if (jQuery("#freefiles")) {
		freefiles = parseInt(jQuery("#freefiles").val());
	}
	if (jQuery("#costperupload")) {
		costperupload = parseFloat(jQuery("#costperupload").val());
	}
	jQuery('#fileupload').fileupload({
		maxNumberOfFiles: maxfiles,
		url: ajaxurl + '?do=fileuploader&rfpid=' + listingid + '&attachtype=' + attachtype + '&update=' + update,
		autoUpload: true,
		acceptFileTypes: /(\.|\/)(gif|jpe?g|png|bmp|tif|tiff)$/i
	}).bind('fileuploadstart', function(){
	    	// disable submit
		if (parent.top.jQuery('#submit').length)
		{
			parent.top.jQuery('#submit').prop("disabled", true);
		}
		if (parent.top.jQuery('#saveasdraft').length)
		{
			parent.top.jQuery('#saveasdraft').prop("disabled", true);
		}
		parent.top.jQuery.growl.notice({ title: phrase['_uploading'], message: phrase['_your_upload_in_progress'], duration: 5000 });
	}).bind('fileuploadprogressall', function (e, data) {
		if (data.loaded == data.total){
			// all files have finished uploading, re-enable submit
			if (parent.top.jQuery('#submit').length)
			{
				parent.top.jQuery('#submit').prop("disabled", false);
			}
			if (parent.top.jQuery('#saveasdraft').length)
			{
				parent.top.jQuery('#saveasdraft').prop("disabled", false);
			}
			parent.top.jQuery.growl.notice({ title: phrase['_upload_complete'], message: phrase['_your_upload_has_been_completed'], duration: 5000 });
		}
	}).bind('fileuploaddestroy', function(e, data) {
		// check for conflicts with existing variants using this picture
		if (data.filehash != undefined || data.filehash != NaN || data.filehash != '')
		{
			if (parent.top.jQuery("#filtered_auctiontype").val() == 'fixed' && parent.top.jQuery("#variants_1").is(":checked"))
			{ // variants in use
				if (parent.top.jQuery('#table-variants').contents().find('img.' + data.filehash).prop('class') === data.filehash) {
					parent.top.jQuery('#table-variants').contents().find('img.' + data.filehash).attr('src', parent.top.iL['BASEURL'] + 'application/assets/images/v5/img_nophoto.gif');
					parent.top.jQuery.growl.warning({ title: phrase['_warning'], message: phrase['_the_pic_was_assoc_one_more_variants'], duration: 10000 });
	                                parent.top.smoothscroll('formatfixedprice');
				}
			}
		}
	}).bind('fileuploaddestroyed', function(e, data) {
		if (update) { // update listing
			jQuery.ajax({url: jQuery('#fileupload').fileupload('option', 'url'), dataType: 'json', context: jQuery('#fileupload')[0]}).done(function (result) {
				var length = result.files.length;
				var existingfilesuploaded = 0;
				var filesuploaded = 0;
				for (var i = 0; i < length; i++) {
					if (result.files[i].invoiceid > 0 || result.files[i].invoiceid === '-1') { // paid or exempted
						existingfilesuploaded++;
					}
					else {
						filesuploaded++;
					}
				}
				parent.top.jQuery("#fee12").attr('feetitle', phrase['_pictures'] + ' x ' + existingfilesuploaded);
				parent.top.jQuery("#fee12").attr('uploadcount', existingfilesuploaded);
				parent.top.jQuery("#fee13").attr('feetitle', phrase['_pictures'] + ' x ' + filesuploaded);
				parent.top.jQuery("#fee13").attr('uploadcount', filesuploaded);
				parent.top.jQuery("#fee13").attr('totaluploadcount', (filesuploaded + existingfilesuploaded));
				if (existingfilesuploaded < freefiles) {
					parent.top.jQuery("#fee13").attr('fee', ((filesuploaded > 0) ? parseFloat(costperupload * (filesuploaded - freefiles)).toFixed(2) : '0.00'));
				}
				else {
					parent.top.jQuery("#fee13").attr('fee', ((filesuploaded > 0) ? parseFloat(costperupload * filesuploaded).toFixed(2) : '0.00'));
				}
				jQuery("#uploadscount").html(+filesuploaded + +existingfilesuploaded);
				jQuery("#totaluploadscount").html(+maxfiles);
				jQuery("#totaluploadsleft").html(+maxfiles - (+filesuploaded + +existingfilesuploaded));
				parent.livefeecalculator();
			});
		}
		else { // new listing
			var filesuploaded = +parent.top.jQuery("#fee12").attr('uploadcount');
			filesuploaded--;
			if (filesuploaded < 0) {
				filesuploaded = 0;
			}
			parent.top.jQuery("#fee12").attr('fee', ((filesuploaded > 0) ? parseFloat(costperupload * (filesuploaded - freefiles)).toFixed(2) : '0.00'));
			parent.top.jQuery("#fee12").attr('feetitle', phrase['_pictures'] + ' x ' + filesuploaded);
			parent.top.jQuery("#fee12").attr('uploadcount', filesuploaded);
			jQuery("#uploadscount").html(+filesuploaded + +existingfilesuploaded);
			jQuery("#totaluploadscount").html(+maxfiles);
			jQuery("#totaluploadsleft").html(+maxfiles - (+filesuploaded + +existingfilesuploaded));
			parent.livefeecalculator();
		}
		jQuery("#itempictures").trigger('sortupdate');
		parent.top.jQuery.growl.notice({ title: phrase['_success'], message: phrase['_the_picture_was_deleted_successfully'] });
	}).bind('fileuploaddone', function(e, data) {
		if (update) { // update listing
			var existingfilesuploaded = +parent.top.jQuery("#fee12").attr('uploadcount');
			var filesuploaded = +parent.top.jQuery("#fee13").attr('uploadcount') + 1;
			var length = data.result.files.length;
			for (var i = 0; i < length; i++) {
				if (data.result.files[i].error === '') {}
				else {
					filesuploaded--;
					if (filesuploaded < 0) {
						filesuploaded = 0;
					}
				}
			}
			if (filesuploaded > 0) {
				parent.top.jQuery("#fee13").attr('fee', parseFloat(costperupload * (filesuploaded - ((existingfilesuploaded <= 0) ? freefiles : 0))).toFixed(2));
				parent.top.jQuery("#fee13").attr('feetitle', phrase['_pictures'] + ' x ' + filesuploaded);
				parent.top.jQuery("#fee13").attr('uploadcount', filesuploaded);
				parent.top.jQuery("#fee13").attr('totaluploadcount', +filesuploaded + +existingfilesuploaded);
			}
		}
		else { // new listing
			var existingfilesuploaded = 0;
			var filesuploaded = +parent.top.jQuery("#fee12").attr('uploadcount') + 1;
			var length = data.result.files.length; // 1
			for (var i = 0; i < length; i++) {
				if (data.result.files[i].error === '') {}
				else {
					filesuploaded--;
					if (filesuploaded < 0) {
						filesuploaded = 0;
					}
				}
			}
			if (filesuploaded > 0) {
				parent.top.jQuery("#fee12").attr('fee', parseFloat(costperupload * (filesuploaded - freefiles)).toFixed(2));
				parent.top.jQuery("#fee12").attr('feetitle', phrase['_pictures'] + ' x ' + filesuploaded);
				parent.top.jQuery("#fee12").attr('uploadcount', filesuploaded);
			}
		}
		jQuery("#uploadscount").html(+filesuploaded + +existingfilesuploaded);
		jQuery("#totaluploadscount").html(+maxfiles);
		jQuery("#totaluploadsleft").html(+maxfiles - (+filesuploaded + +existingfilesuploaded));
		jQuery("#itempictures").trigger('sortupdate');
		parent.livefeecalculator();
		if (parent.jQuery("#picturesurl").hasClass('open')) {
			if (parent.jQuery("#pictureurls").val() != '') {
				parent.jQuery("#pictureurls").val('');
			}
			parent.jQuery("#picturesurl").removeClass('open');
			parent.jQuery('#picturesurlbutton').prop('disabled', false);
			parent.jQuery('#picturesurlbutton').removeClass('disabled');
			parent.jQuery('#cancelpicturesurl').addClass('hide');
		}
	});
	// fetch existing photos
	jQuery.ajax({
		url: jQuery('#fileupload').fileupload('option', 'url'),
		dataType: 'json',
		context: jQuery('#fileupload')[0]
	}).done(function(result) {
		if (update) { // update listing
			var length = result.files.length;
			for (var i = 0; i < length; i++) {
				if (result.files[i].invoiceid > 0 || result.files[i].invoiceid === '-1') { // paid or exempted
					existingfilesuploaded++;
				}
				else {
					filesuploaded++;
				}
			}
			parent.top.jQuery("#fee12").attr('feetitle', phrase['_pictures'] + ' x ' + existingfilesuploaded);
			parent.top.jQuery("#fee12").attr('uploadcount', existingfilesuploaded);
			if (existingfilesuploaded < freefiles) {
				parent.top.jQuery("#fee13").attr('fee', parseFloat(costperupload * (filesuploaded - freefiles)).toFixed(2));
			}
			else {
				parent.top.jQuery("#fee13").attr('fee', parseFloat(costperupload * filesuploaded).toFixed(2));
			}
			parent.top.jQuery("#fee13").attr('feetitle', phrase['_pictures'] + ' x ' + filesuploaded);
			parent.top.jQuery("#fee13").attr('uploadcount', filesuploaded);
			parent.top.jQuery("#fee13").attr('totaluploadcount', +filesuploaded + +existingfilesuploaded);
		}
		else { // new listing
			filesuploaded =+ result.files.length;
			if (filesuploaded < 0) {
				filesuploaded = 0;
			}
			if (filesuploaded > 0) {
				parent.top.jQuery("#fee12").attr('fee', parseFloat(costperupload * (filesuploaded - freefiles)).toFixed(2));
				parent.top.jQuery("#fee12").attr('feetitle', phrase['_pictures'] + ' x ' + filesuploaded);
				parent.top.jQuery("#fee12").attr('uploadcount', filesuploaded);
			}
		}
		jQuery("#uploadscount").html(+filesuploaded + +existingfilesuploaded);
		jQuery("#totaluploadscount").html(+maxfiles);
		jQuery("#totaluploadsleft").html(+maxfiles - (+filesuploaded + +existingfilesuploaded));
		jQuery("#itempictures").trigger('sortupdate');
		parent.livefeecalculator();
		jQuery(this).fileupload('option', 'done').call(this, null, {result: result});
	});
	function update_order_indexes() {
		var orderIndex = 1;
		jQuery("#itempictures li").each(function(){
			jQuery(this).find("input[type=hidden]:first").val(orderIndex);
			orderIndex++;
		});
	}
	function post_update_order() {
		jQuery.ajax({
			type: "POST",
			url: ajaxurl + '?do=fileuploadreorder',
			data: jQuery("#itempictures").sortable("serialize")
		});
	}
	jQuery("#itempictures").sortable({
		delay: 100,
		items: 'li',
		opacity: 0.7,
		axis: 'x',
		update: function(event, ui){
			update_order_indexes();
		},
		stop: function(event, ui){
			post_update_order();
		}
	});
	// Bind the update event manually
	jQuery("#itempictures").on("sortupdate", function(event, ui) {
		//console.log('sortupdate');
		update_order_indexes();
	});
	jQuery("#itempictures").on('sortstop', function(event, ui) {
		//console.log('sortstop');
		post_update_order();
	});
}
function cancel_save_pictures_via_url()
{
	parent.jQuery('#picturesurlbutton').prop('disabled', false);
	parent.jQuery('#picturesurlbutton').removeClass('disabled');
	parent.jQuery('#cancelpicturesurl').addClass('hide');
}
function save_pictures_via_url()
{
	var lines = parent.jQuery('#pictureurls').val().split("\n");
	if (lines) {
		var len = lines.length;
		if (lines.length > 25) {
			len = 25;
		}
		if (parent.jQuery('#pictureurls').val() != '') {
			parent.jQuery('#picturesurlbutton').prop('disabled', true);
			parent.jQuery('#picturesurlbutton').addClass('disabled');
			parent.jQuery('#cancelpicturesurl').removeClass('hide');
		}
		for (var i = 0; i < len; i++)
		{
			$.getImageData({
				url: lines[i],
				server_url: parent.top.iL['AJAXURL'] + '?do=fileuploadviaurl',
				success: function(img) {
					var canvas = document.createElement('canvas');
					canvas.width = img.width;
					canvas.height = img.height;
					if (canvas.getContext && canvas.toBlob) {
						canvas.getContext('2d').drawImage(img, 0, 0, img.width, img.height);
						canvas.toBlob(function(blob) {
							if (!blob.name) {
								blob.name = img.filename;
							}
							parent.jQuery.growl.notice({ title: phrase['_success'], message: phrase['_assigning_x_to_your_listing'].replace('[x]', img.filename) });
							jQuery('#fileupload').fileupload('add', {files: [blob]});
						}, img.mime);
					}
				},
				error: function(xhr, text_status) {
					parent.jQuery.growl.error({ title: phrase['_error'], message: phrase['_invalid_image_or_url'] });
					parent.jQuery('#picturesurlbutton').prop('disabled', false);
					parent.jQuery('#picturesurlbutton').removeClass('disabled');
					parent.jQuery('#cancelpicturesurl').addClass('hide');
		    		}
			});
		}
		parent.jQuery('#pictureurls').val('');
		parent.jQuery('#picturesurlbutton').prop('disabled', false);
		parent.jQuery('#picturesurlbutton').removeClass('disabled');
		parent.jQuery('#cancelpicturesurl').addClass('hide');
	}
}
$(function() {
	'use strict';
	do_upload();
});
