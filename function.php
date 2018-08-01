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