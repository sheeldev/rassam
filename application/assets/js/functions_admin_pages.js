function submit_pages_form() {
	haserror = false;
	if (jQuery('#form_title').val() == '')
	{ // check title
		haserror = true;
		jQuery('#form_title').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Please enter the title'});
	}
	if (jQuery('#form_seourl').val() == '')
	{ // sef url
		haserror = true;
		jQuery('#form_seourl').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Please enter a valid SEF URL'});
	}
	if (jQuery('#form_keywords').val() == '')
	{ // check keywords
		haserror = true;
		jQuery('#form_keywords').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Please enter the keywords'});
	}
	if (jQuery('#form_content').val() == '')
	{ // check keywords
		haserror = true;
		jQuery('#form_content').addClass('error');
		jQuery.growl.error({title: phrase['_error'], message: 'Please enter the page content'});
	}
	if (!haserror)
	{
		return true;
	}
	return false;
}
(function($){
	$.fn.init_sefurls = function() {
		prefix = jQuery('#form_seourl').val();
		jQuery('#form_title').friendurl({id : 'form_seourl', divider: '-', transliterate: false, prefix: prefix});
	};
})(jQuery);
jQuery(document).ready(function() {
	(function() {
		jQuery().init_sefurls();
	}());
});
