<?php
/**
 * Created by Buffercode.
 * User: M A Vinoth Kumar
 */

/**
 * Append the Version -- Pages
 */
add_filter( 'fed_plugin_versions', function ( $version ) {
	return array_merge( $version, array( 'payment' => 'Payment' ) );
} );


add_action( 'wp_ajax_fedp_pay_payment_page', 'fedp_payment_page' );

function fedp_payment_page() {
	echo 'hello';
}

/**
 * @return array
 */
function fed_pay_invoice_templates() {
	$templates = array(
		'template_1' => array(
			'name'            => 'Default',
			'version'         => '1.0',
			'image_full_url'  => plugins_url('assets/images/template_1.png',BC_FED_PAY_PLUGIN),
			'image_thumb_url' => plugins_url('assets/images/template_1_thumb.png',BC_FED_PAY_PLUGIN),
			'type'            => 'one_time'
		)
	);
	$templates = apply_filters( 'fed_pay_invoice_templates_filter', $templates );

	return $templates;
}