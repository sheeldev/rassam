/**
 *  jQuery.SelectListActions
 *  https://github.com/esausilva/jquery.selectlistactions.js
 *
 *  (c) http://esausilva.com
 */

(function ($) {
    $.fn.moveToListAndDelete = function(sourceList, destinationList) {
        var opts = $(sourceList + ' option:selected');
        if (opts.length == 0) {
                alert_js("No items have been selected to move.");
        }
        $(opts).remove();
        $(destinationList).append($(opts).clone());
        $("#lots > option").each(function(index, element) {
                var newtext = element.text.replace(/\[(\w+)[^\]]*]/g, '');
                $(this).val(element.value).text('[LOT ' + (index + 1) + '] ' + newtext);
        });
        $("#items > option").each(function(index, element) {
                var newtext = element.text.replace(/\[(\w+)[^\]]*]/g, '');
                $(this).val(element.value).text(newtext);
        });
        if ($("#items > option").length == 0) {
                jQuery('#btnAdd').addClass('disabled');
                jQuery('#add-additional-lots_new').removeClass('hide');
        }
        else
        {
                jQuery('#btnAdd').removeClass('disabled');
                jQuery('#items-wrapper').removeClass('redborder');
                jQuery('#add-additional-lots_new').addClass('hide');
        }
        if (jQuery("#lots > option").length == 0) {
                jQuery('#btnRemove').addClass('disabled');
                jQuery('#lots-wrapper').addClass('redborder');
                jQuery('#btnUp').addClass('disabled');
                jQuery('#btnDown').addClass('disabled');
                jQuery('#no-lots-assigned').removeClass('hide');
                jQuery('#lots-arrangement').addClass('hide');
        }
        else
        {
                jQuery('#btnRemove').removeClass('disabled');
                jQuery('#lots-wrapper').removeClass('redborder');
                jQuery('#no-lots-assigned').addClass('hide');
                jQuery('#lots-arrangement').removeClass('hide');
                if (jQuery("#lots > option").length > 1)
                {
                        jQuery('#btnUp').removeClass('disabled');
                        jQuery('#btnDown').removeClass('disabled');
                }
                else
                {
                        jQuery('#btnUp').addClass('disabled');
                        jQuery('#btnDown').addClass('disabled');
                }
        }
    };
    $.fn.moveUpDown = function(list, btnUp, btnDown) {
        var opts = $(list + ' option:selected');
        if (opts.length == 0) {
                alert_js("No items have been selected to move.");
        }
        if (btnUp) {
                opts.first().prev().before(opts);
        }
        else if (btnDown) {
                opts.last().next().after(opts);
        }
        $("#lots > option").each(function(index, element) {
                var newtext = element.text.replace(/\[(\w+)[^\]]*]/g, '');
                $(this).val(element.value).text('[LOT ' + (index + 1) + '] ' + newtext);
        });
    };
})(jQuery);
