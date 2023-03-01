jQuery(document).ready(function()
{
	if (jQuery('#bulkOperations').length)
	{ // admin
		var top = jQuery('#bulkOperations').offset().top - 77;
		//console.log(top);
	}
	jQuery(window).bind('load', function()
	{ // on load
		if (jQuery('#bulkOperations').length)
		{
			//console.log('onload: scrollTop: ' + jQuery(this).scrollTop() + ', top: ' + top);
			if (jQuery(this).scrollTop() > top)
			{
				jQuery('.bulk-actions.bulk-actions--is-visible').addClass("bulk-actions--is-sticky");
				jQuery('.draw-input-wrapper').addClass("bulk-actions__select-all--is-sticky");
				jQuery('.draw-input-wrapper').removeClass("bulk-actions__select-all");
			}
			else
			{
				jQuery('.bulk-actions.bulk-actions--is-visible').removeClass("bulk-actions--is-sticky");
				jQuery('.draw-input-wrapper').removeClass("bulk-actions__select-all--is-sticky");
				jQuery('.draw-input-wrapper').addClass("bulk-actions__select-all");
			}
		}
	});
	jQuery(window).bind('scroll', function()
	{ // on scroll
		//console.log('onscroll: scrollTop: ' + jQuery(this).scrollTop() + ', top: ' + top);
		if (jQuery('.bulk-actions.bulk-actions--is-visible').length)
		{
			if (jQuery(this).scrollTop() > top)
			{
				jQuery('.bulk-actions.bulk-actions--is-visible').addClass("bulk-actions--is-sticky");
				jQuery('.draw-input-wrapper').addClass("bulk-actions__select-all--is-sticky");
				jQuery('.draw-input-wrapper').removeClass("bulk-actions__select-all");
			}
			else
			{
				jQuery('.bulk-actions.bulk-actions--is-visible').removeClass("bulk-actions--is-sticky");
				jQuery('.draw-input-wrapper').removeClass("bulk-actions__select-all--is-sticky");
				jQuery('.draw-input-wrapper').addClass("bulk-actions__select-all");
			}
		}
	});
});
function iL_Inline(varname, cbtype, formobjid, submitname, sheelcookie)
{
	this.varname = varname;
	this.cbtype = cbtype.toLowerCase();
	this.formobj = fetch_js_object(formobjid);
	this.submitname = submitname;
	if (typeof sheelcookie != 'undefined')
	{
		this.sheelcookie = sheelcookie;
	}
	else
	{
		this.sheelcookie = iL['COOKIENAME'] + 'inline';
	}
	if (this.cbtype != '')
	{
		this.list = this.cbtype + '_';
	}
	this.cookieids = null;
	this.cookiearray = new Array();
	this.init = function(elements)
	{
		this.cookiearray = new Array();
		if (this.fetch_ids())
		{
			for (i in this.cookieids)
			{
				if (this.cookieids[i] != '' && this.cookieids[i] != 'deleted')
				{
					if (jQuery('.draw-checkbox').length && jQuery('.bulk-actions').length)
					{ // admin
						if (this.cookieids[i] == jQuery('#' + this.list + "" + this.cookieids[i]).val())
						{
							jQuery('#' + this.list + "" + this.cookieids[i]).prop("checked", true);
							if (jQuery('#tr_selected_' + this.cookieids[i]).length)
							{
								jQuery('#tr_selected_' + this.cookieids[i]).addClass("selected");
							}
						}
						else
						{
							if (jQuery('#tr_selected_' + this.cookieids[i]).length)
							{
								jQuery('#tr_selected_' + this.cookieids[i]).removeClass("selected");
							}
						}
					}
					else if (jQuery('#bulkselectedcount').length)
					{ // client
						if (this.cookieids[i] == jQuery('#' + this.list + "" + this.cookieids[i]).val())
						{
							jQuery('#' + this.list + "" + this.cookieids[i]).prop("checked", true);
						}
					}
					this.cookiearray[this.cookiearray.length] = this.cookieids[i];
				}
			}
		}
		this.set_button_counters();
	}
	this.fetch_ids = function()
	{
		this.cookieids = fetch_js_cookie(this.sheelcookie + this.cbtype);
		if (this.cookieids != null)
		{
			if (this.cookieids.length >= 1)
			{
				this.cookieids = this.cookieids.split('~');
				if (this.cookieids.length > 0)
				{
					return true;
				}
			}
		}
		return false;
	}
	this.uncheck = function(checkbox)
	{
		this.save(checkbox.id.substr(8), false);
		fetch_js_object(checkbox.id).checked = false;
	}
	this.toggle = function(checkbox)
	{
		this.save(checkbox.id.substr(8), checkbox.checked);
	}
	this.save = function(checkboxid, checked)
	{
		this.cookiearray = new Array();
		if (this.fetch_ids())
		{
			for (i in this.cookieids)
			{
				if (this.cookieids[i] != checkboxid && this.cookieids[i] != '')
				{
					this.cookiearray[this.cookiearray.length] = this.cookieids[i];
				}
			}
		}
		if (checked)
		{
			this.cookiearray[this.cookiearray.length] = checkboxid;
			if (jQuery('#tr_selected_' + checkboxid).length)
			{
				jQuery('#tr_selected_' + checkboxid).addClass("selected");
			}
		}
		else
		{
			if (jQuery('#tr_selected_' + checkboxid).length)
			{
				jQuery('#tr_selected_' + checkboxid).removeClass("selected");
				jQuery('#tr_selected_' + checkboxid).removeClass("selected");
			}
		}
		this.set_button_counters();
		this.set_cookie();
		return true;
	}
	this.set_cookie = function()
	{
		expires = new Date();
		expires.setTime(expires.getTime() + 3600000);
		update_js_cookie(this.sheelcookie + this.cbtype, this.cookiearray.join('~'), expires, false, false);
	}
	this.is_cookie_in_list = function(obj)
	{
		return (obj.type == 'checkbox' && obj.id.indexOf(this.list) == 0 && (obj.disabled == false || obj.disabled == 'undefined'));
	}
	this.check_all = function(checked, itemtype, caller)
	{
		this.cookiearray = new Array();
		if (this.fetch_ids())
		{
			for (i in this.cookieids)
			{
				if (jQuery('#' + this.list + "" + this.cookieids[i]).length <= 0)
				{
					this.cookiearray[this.cookiearray.length] = this.cookieids[i]
				}
				else
				{
					if (jQuery('#tr_selected_' + this.cookieids[i]).length)
					{
						jQuery('#tr_selected_' + this.cookieids[i]).removeClass("selected");
					}
				}
			}
		}
		for (var i = 0; i < this.formobj.elements.length; i++)
		{
			if (this.is_cookie_in_list(this.formobj.elements[i]))
			{
				elm = this.formobj.elements[i];
				if (typeof itemtype != 'undefined')
				{
					if (elm.value & itemtype)
					{
						elm.checked = checked;
					}
					else
					{
						elm.checked = !checked;
					}
				}
				else if (checked == 'invert')
				{
					elm.checked = !elm.checked;
				}
				else
				{
					elm.checked = checked;
				}
				if (elm.checked)
				{
					this.cookiearray[this.cookiearray.length] = elm.id.substring(8);
					if (jQuery('#tr_selected_' + elm.id.substring(8)).length)
					{
						jQuery('#tr_selected_' + elm.id.substring(8)).addClass("selected");
					}
				}
			}
		}
		this.set_button_counters();
		this.set_cookie();
		return true;
	}
	this.set_button_counters = function()
	{
		if (jQuery('#bulkselectedcount').length)
		{ // client
			fetch_js_object('bulkselectedcount').innerHTML = this.cookiearray.length;
			if (this.cookiearray.length <= 0)
			{
				if (jQuery('#bulk-actions-widget').length)
				{
					jQuery('#bulk-actions-widget').removeClass('open');
				}
			}
			else
			{
				if (jQuery('#bulk-actions-widget').length)
				{
					jQuery('#bulk-actions-widget').addClass('open');
				}
			}
		}
		else if (jQuery('.draw-checkbox').length && jQuery('.bulk-actions').length)
		{ // admin
			jQuery('#selectedcount').html(this.cookiearray.length);
			if (this.cookiearray.length <= 0)
			{
				jQuery('#checkboxall').prop("checked", false);
				jQuery('.bulk-actions.bulk-actions--is-visible').removeClass("bulk-actions--is-sticky");
				jQuery('.bulk-actions').removeClass('bulk-actions--is-visible');
				jQuery('.draw-input-wrapper').removeClass("bulk-actions__select-all--is-sticky");
				if (jQuery('#unselectalllink').length)
				{
					jQuery('#unselectalllink').addClass('hide');
				}
			}
			else
			{
				jQuery('#checkboxall').prop("checked", true);
				jQuery('.bulk-actions').addClass('bulk-actions--is-visible');
				if (jQuery('#unselectalllink').length)
				{
					jQuery('#unselectalllink').removeClass('hide');
				}
				if (jQuery(window).scrollTop() > top)
				{
					jQuery('.bulk-actions.bulk-actions--is-visible').addClass("bulk-actions--is-sticky");
					jQuery('.draw-input-wrapper').addClass("bulk-actions__select-all--is-sticky");
					jQuery('.draw-input-wrapper').removeClass("bulk-actions__select-all");
				}
			}
		}
	}
	this.init(this.formobj.elements);
}
