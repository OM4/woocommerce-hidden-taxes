/**
 * Add the "Hidden" column and checkbox to the tax rates tables.
 *
 * Unfortunately the tax display screen doesn't include any filters or actions for us to add our additional field to,
 * so instead we do it via jQuery.
 *
 * @package    WooCommerce Hidden Taxes
 * @author     OM4
 * @since      1.0
 */

jQuery( window ).load( function () {
	// Add the new column to the table head.
	jQuery( 'table.wc_tax_rates thead tr' ).append(
		'<th width="8%">' + wc_hidden_taxes.hidden_label + '&nbsp;<span class="tips" data-tip="' + wc_hidden_taxes.hidden_tooltip + '">[?]</span></th>'
	);
	// Add the new checkbox/cell to each tax rate.
	jQuery( 'table.wc_tax_rates tbody#rates tr' ).each( function () {
		// Obtain the tax rate's ID from the input name.
		var rate_id = jQuery( this ).find( 'td.country input' ).prop( 'name' ).replace( 'tax_rate_country[', '' ).replace( ']', '' );
		var checked = '';
		if (rate_id in wc_hidden_taxes.hidden_rates) {
			checked = ' checked="checked" ';
		}
		jQuery( this ).append(
			'<td class="apply_to_shipping tax_rate_hidden" width="8%">\
			<input type="checkbox" class="checkbox" name="tax_rate_hidden[' + rate_id + ']"' + checked + ' />\
			</td>'
		);
	});
	// Increment the footer colspan by 1.
	jQuery( 'table.wc_tax_rates tfoot th' ).prop( 'colspan', jQuery( 'table.wc_tax_rates tfoot th' ).prop( 'colspan' ) + 1 );
});
