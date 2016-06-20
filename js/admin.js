/**
 * Add the "Hidden" column and checkbox to the tax rates tables.
 *
 * Unfortunately the tax display screen doesn't include any filters or actions for us to add our additional field to,
 * so instead we do it via jQuery.
 *
 * Ideally this would all be done via Backbone, but there isn't an easy way to filter/override how WC 2.5+'s tax screen works.
 *
 * @package    WooCommerce Hidden Taxes
 * @author     OM4
 * @since      1.0
 */

jQuery( window ).load( function () {

	// Add header/footer for the new "Hidden" column
	jQuery('table.wc_tax_rates thead tr').append(
			'<th width="8%">' + wc_hidden_taxes.hidden_label + '&nbsp;<span class="tips" data-tip="' + wc_hidden_taxes.hidden_tooltip + '">[?]</span></th>'
	);
	jQuery('table.wc_tax_rates tfoot th').prop('colspan', jQuery('table.wc_tax_rates tfoot th').prop('colspan') + 1);

	/**
	 * Add our "Hidden" checkbox/cell to each tax rate in the tax rates table.
	 */
	function load_tax_rate_hidden_checkboxes() {
		jQuery('table.wc_tax_rates tbody#rates tr').each(function () {
			// Obtain the tax rate's ID from the input name.
			var rate_id = jQuery(this).find('td.country input').prop('name').replace('tax_rate_country[', '').replace(']', '');
			var checked = '';
			if (rate_id in wc_hidden_taxes.hidden_rates) {
				checked = ' checked="checked" ';
			}
			jQuery(this).append(
					'<td class="apply_to_shipping tax_rate_hidden" width="8%">\
					<input type="checkbox" class="checkbox" name="tax_rate_hidden[' + rate_id + ']"' + checked + ' />\
				</td>'
			);
		});
	}


	load_tax_rate_hidden_checkboxes();

	// Whenever the ajax save event is called for the tax rates, also include the hidden checkbox button values so that they can be saved.
	jQuery.ajaxPrefilter(function(options, originalOptions, jqXHR) {
	    if ( options.url.indexOf('tax_rates_save_changes') > -1 && ( originalOptions.type === 'POST' || options.type === 'POST' ) ) {

				options.data = options.data + '&' + jQuery( 'form#mainform input[name^="tax_rate_hidden"]').serialize();

				// Also update the local variable so they checkboxes can be re-added after the AJAX call completes
				wc_hidden_taxes.hidden_rates = new Object;
				jQuery( 'form#mainform input[name^="tax_rate_hidden"]:checked').each(function() {
					var zone_id = jQuery( this ).prop( 'name').replace( "tax_rate_hidden[", "").replace( "]", "" );
					wc_hidden_taxes.hidden_rates[ zone_id ] = true;
				});

	    }
	});

	// After the AJAX tax rate save completes, re-add the hidden checkboxes
	jQuery(document).ajaxComplete(function(event, xhr, settings) {
		if ( settings.url.indexOf('tax_rates_save_changes') > -1 ) {
			load_tax_rate_hidden_checkboxes();
		}
	});

	// Re-add the hidden checkboxes when a new tax rate row is added or removed
	jQuery( 'table.wc_tax_rates a.button.insert, table.wc_tax_rates a.button.remove_tax_rates').on( 'click', function() {
		load_tax_rate_hidden_checkboxes();
	} );

});
