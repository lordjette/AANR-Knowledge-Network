/**
 * @package    HikaShop for Joomla!
 * @version    3.4.0
 * @author     hikashop.com
 * @copyright  (C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
(function() {

var oldCheckout = window.checkout || null;

var hikashopCheckout = {
	token: "",
	urls: {
		show: "",
		submit: "",
		submitstep: "",
	},
	urlParams: {
		type: 'HIKATYPE',
		cid: 'HIKACID',
		pos: 'HIKAPOS',
		token: 'HIKATOKEN',
	},

	refreshBlock: function(type, step, id) {
		var type_clean = type.replace(/\./g,'-'),
			el_name = "hikashop_checkout_" + type_clean + "_" + step + "_" + id,
			t = this, d = document, w = window, o = w.Oby,
			el = d.getElementById(el_name);
		if(!el || !window.checkout.urls.show)
			return false;
		o.addClass(el, "hikashop_checkout_loading");

		var url = window.checkout.urls.show,
			params = {};
		url = t.handleParams({'type': type, 'cid': step, 'pos': id }, url, params);

		o.xRequest(url, params, function(x,p) {
			el = d.getElementById("hikashop_checkout_" + type_clean + "_" + step + "_" + id);
			o.removeClass(el, "hikashop_checkout_loading");
			o.updateElem(el, x.responseText);
			t.handleEnter(type_clean, step, id);
			o.fireAjax('checkoutBlockRefresh', {'type': type_clean, 'cid': step, 'pos': id});
		});
		return false;
	},
	submitBlock: function(type, step, id, data) {
		var type_clean = type.replace(/\./g,'-'), el_name = "hikashop_checkout_" + type_clean + "_" + step + "_" + id, url = null, formData = null,
			t = this, d = document, w = window, o = w.Oby,
			el = d.getElementById(el_name);

		if(!el)
			return false;

		if(!window.checkout.urls.submit || !window.checkout.token) {
			var f = d.getElementById('hikashop_checkout_form');
			if(!f) return false;
			f.submit();
			return false;
		}

		if(data === undefined || !data) {
			formData = o.getFormData(el);
		} else if(typeof(data) == "string") {
			formData = data;
		} else {
			formData = "";
			for(var k in data) {
				if( formData != "" ) formData += "&";
				formData += encodeURI(k) + "=" + encodeURIComponent(data[k]);
			}
		}
		o.addClass(el, "hikashop_checkout_loading");

		var url = window.checkout.urls.submit,
			params = {mode:"POST", data: formData};
		url = t.handleParams({'type': type, 'cid': step, 'pos': id, 'token': 1 }, url, params);

		o.xRequest(url, params, function(x,p) {
			if(x.responseText == '401')
				window.location.reload(true);
			el = d.getElementById("hikashop_checkout_" + type_clean + "_" + step + "_" + id);
			o.removeClass(el, "hikashop_checkout_loading");
			o.updateElem(el, x.responseText);
			t.handleEnter(type_clean, step, id);
			o.fireAjax('checkoutBlockRefresh', {'type': type_clean, 'cid': step, 'pos': id});
		});
		return false;
	},
	handleParams: function(data, url, req) {
		var t = this, fields = {type: 'blocktask', cid: 'cid', pos: 'blockpos', token: window.checkout.token};
		for(var f in fields) {
			if(!fields.hasOwnProperty(f) || !data[f]) continue;
			url = t.handleParam(fields[f], t.urlParams[f], data[f], url, req);
		}
		return url;
	},
	handleParam: function(key, param, data, url, req) {
		var t = this;
		if(param && param != "" && url.indexOf(param) >= 0) {
			url = url.replace(param, data);
			return url;
		}
		if(!req.data) req.data = "";
		if(req.data != "") req.data += "&";
		req.data += encodeURI(key) + "=" + encodeURIComponent(data);
		req.mode = "POST";
		return url;
	},
	onFormSubmit: function(el) {
		if(el.submit_in_progress)
			return false;
		el.submit_in_progress = true;
		return true;
	},
	isSource: function(params, step, pos) {
		return (params && params.src && typeof(params.src.step) != "undefined" && params.src.step == step && typeof(params.src.pos) != "undefined" && params.src.pos == pos);
	},
	processEvents: function(evts) {
		for(var i = 0; i < evts.length; i++) {
			var evt = evts[i], params = null;
			if(evt && typeof(evt) != "string" && evt[0]) {
				params = evt[1];
				evt = evt[0];
			}
			window.Oby.fireAjax(evt, params);
		}
	},
	handleEnter: function(task, step, pos) {
		var t = this, d = document;

		block = d.getElementById('hikashop_checkout_' + task + '_' + step + '_' + pos);
		if(!block)
			return true;

		els = block.querySelectorAll('input[type=text], input[type=checkbox], input[type=password]');
		if(!els.length)
			return true;

		for(var idx = 0 ; idx < els.length ; idx++) {
			els[idx].addEventListener('keydown', function(e) {
				if(e.key === undefined && e.keyCode === undefined && e.which === undefined)
					return;
				if((e.key !== undefined && e.key != "Enter") || (e.keyCode !== undefined && e.keyCode != 13) || (e.which !== undefined && e.which != 13))
					return;
				e.preventDefault();
				t.submitBlock(task, step, pos);
			});
		}
	}
};

window.checkout = hikashopCheckout;

if(oldCheckout && oldCheckout instanceof Object) {
	for(var attr in oldCheckout) {
		if(oldCheckout.hasOwnProperty(attr) && !window.checkout.hasOwnProperty(attr))
			window.checkout[attr] = oldCheckout[attr];
	}
}

})();
