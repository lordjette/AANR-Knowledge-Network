/**
 * Consent
 *
 * @copyright: Copyright (C) 2005-2018  Media A-Team, Inc. - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
define(['jquery', 'fab/fabrik'], function (jQuery, Fabrik) {
	'use strict';
	var Acymailing = new Class({

		options: {
			'renderOrder': ''
		},


		/**
		 * Initialize
		 * @param {object} options
		 */
		initialize: function (options) {
			var self = this;
			this.options = jQuery.extend(this.options, options);
			this.form = Fabrik.getBlock('form_' + this.options.formid);

			Fabrik.addEvent('fabrik.form.submit.failed', function (form, event, btn) {
				if (form === this.form) {
                    jQuery('.acymailingError').removeClass('fabrikHide');
                    this.form.showMainError(this.form.options.error);
                }
			}.bind(this));

            Fabrik.addEvent('fabrik.form.submitted', function (form, event, btn) {
            	if (form === this.form) {
                    jQuery('.acymailingError').addClass('fabrikHide');
                }
            }.bind(this));
		}
	});

	return Acymailing;
});