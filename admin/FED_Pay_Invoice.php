<?php

namespace FED_PayPal_Admin;


if ( ! class_exists( 'FED_Pay_Invoice' ) ) {
	/**
	 * Class FED_Pay_Invoice
	 * @package FED_PayPal_Admin
	 */
	class FED_Pay_Invoice {


		public function fed_pay_admin_invoice_details_tab( $settings ) {
			$array = array(
				'form'  => array(
					'method' => '',
					'class'  => 'fed_admin_menu fed_ajax',
					'attr'   => '',
					'action' => array( 'url' => '', 'action' => 'fed_admin_payment_invoice_details' ),
					'nonce'  => array( 'action' => '', 'name' => '' ),
					'loader' => '',
				),
				'input' => array(
					'Company Logo' => array(
						'col'          => 'col-md-12',
						'name'         => __( 'Company Logo', 'frontend-dashboard-payment' ),
						'input'        => fed_get_input_details( array(
							'input_meta' => 'invoice[logo]',
							'user_value' => isset( $settings['settings']['invoice']['logo'] ) ? $settings['settings']['invoice']['logo'] : '',
							'input_type' => 'file'
						) ),
						'help_message' => fed_show_help_message( array( 'content' => "Company Logo" ) )
					),
					'Logo Width'   => array(
						'col'          => 'col-md-6',
						'name'         => __( 'Logo Width (px)', 'frontend-dashboard-payment' ),
						'input'        =>
							fed_get_input_details( array(
								'placeholder' => __( 'Logo Width in Pixel', 'frontend-dashboard-payment' ),
								'input_meta'  => 'invoice[width]',
								'user_value'  => isset( $settings['settings']['invoice']['width'] ) ? $settings['settings']['invoice']['width'] : '',
								'input_type'  => 'single_line'
							) ),
						'help_message' => fed_show_help_message( array( 'content' => "Logo Width in Pixel" ) )
					),
					'Logo Height'  => array(
						'col'          => 'col-md-6',
						'name'         => __( 'Logo Height (px)', 'frontend-dashboard-payment' ),
						'input'        =>
							fed_get_input_details( array(
								'placeholder' => __( 'Logo Height in Pixel', 'frontend-dashboard-payment' ),
								'input_meta'  => 'invoice[width]',
								'user_value'  => isset( $settings['settings']['invoice']['height'] ) ? $settings['settings']['invoice']['height'] : '',
								'input_type'  => 'single_line'
							) ),
						'help_message' => fed_show_help_message( array( 'content' => "Logo Height in Pixel" ) )
					),
					'Company Name' => array(
						'col'          => 'col-md-12',
						'name'         => __( 'Company Name', 'frontend-dashboard-payment' ),
						'input'        =>
							fed_get_input_details( array(
								'placeholder' => __( 'Company Name', 'frontend-dashboard-payment' ),
								'input_meta'  => 'invoice[company_name]',
								'user_value'  => isset( $settings['settings']['invoice']['company_name'] ) ? $settings['settings']['invoice']['company_name'] : '',
								'input_type'  => 'single_line'
							) ),
						'help_message' => fed_show_help_message( array( 'content' => "Company Name" ) )
					),
					'Door Number'  => array(
						'col'          => 'col-md-6',
						'name'         => __( 'Door Number', 'frontend-dashboard-payment' ),
						'input'        =>
							fed_get_input_details( array(
								'placeholder' => __( 'Door Number', 'frontend-dashboard-payment' ),
								'input_meta'  => 'invoice[door_number]',
								'user_value'  => isset( $settings['settings']['invoice']['door_number'] ) ? $settings['settings']['invoice']['door_number'] : '',
								'input_type'  => 'single_line'
							) ),
						'help_message' => fed_show_help_message( array( 'content' => "Door Number" ) )
					),
					'Street Name'  => array(
						'col'          => 'col-md-6',
						'name'         => __( 'Street Name', 'frontend-dashboard-payment' ),
						'input'        =>
							fed_get_input_details( array(
								'placeholder' => __( 'Street Name', 'frontend-dashboard-payment' ),
								'input_meta'  => 'invoice[street_name]',
								'user_value'  => isset( $settings['settings']['invoice']['street_name'] ) ? $settings['settings']['invoice']['street_name'] : '',
								'input_type'  => 'single_line'
							) ),
						'help_message' => fed_show_help_message( array( 'content' => "Street Name" ) )
					),
					'City'         => array(
						'col'          => 'col-md-6',
						'name'         => __( 'City', 'frontend-dashboard-payment' ),
						'input'        =>
							fed_get_input_details( array(
								'placeholder' => __( 'City', 'frontend-dashboard-payment' ),
								'input_meta'  => 'invoice[city]',
								'user_value'  => isset( $settings['settings']['invoice']['city'] ) ? $settings['settings']['invoice']['city'] : '',
								'input_type'  => 'single_line'
							) ),
						'help_message' => fed_show_help_message( array( 'content' => "City" ) )
					),
					'State'        => array(
						'col'          => 'col-md-6',
						'name'         => __( 'State', 'frontend-dashboard-payment' ),
						'input'        =>
							fed_get_input_details( array(
								'placeholder' => __( 'State', 'frontend-dashboard-payment' ),
								'input_meta'  => 'invoice[state]',
								'user_value'  => isset( $settings['settings']['invoice']['state'] ) ? $settings['settings']['invoice']['state'] : '',
								'input_type'  => 'single_line'
							) ),
						'help_message' => fed_show_help_message( array( 'content' => "State" ) )
					),
					'Postal Code'  => array(
						'col'          => 'col-md-6',
						'name'         => __( 'Postal Code', 'frontend-dashboard-payment' ),
						'input'        =>
							fed_get_input_details( array(
								'placeholder' => __( 'Postal Code', 'frontend-dashboard-payment' ),
								'input_meta'  => 'invoice[postal_code]',
								'user_value'  => isset( $settings['settings']['invoice']['postal_code'] ) ? $settings['settings']['invoice']['postal_code'] : '',
								'input_type'  => 'single_line'
							) ),
						'help_message' => fed_show_help_message( array( 'content' => "Postal Code" ) )
					),
					'Country'      => array(
						'col'          => 'col-md-6',
						'name'         => __( 'Country', 'frontend-dashboard-payment' ),
						'input'        =>
							fed_get_input_details( array(
								'placeholder' => __( 'Country', 'frontend-dashboard-payment' ),
								'input_value' => fed_get_country_code(),
								'input_meta'  => 'invoice[country]',
								'user_value'  => isset( $settings['settings']['invoice']['country'] ) ? $settings['settings']['invoice']['country'] : '',
								'input_type'  => 'select'
							) ),
						'help_message' => fed_show_help_message( array( 'content' => "Country" ) )
					),

				),
			);

			$new_value = apply_filters( 'fed_pay_admin_payment_invoice_details', $array, $settings );
			fed_common_simple_layout( $new_value );
		}

		public function fed_pay_admin_invoice_templates_tab( $settings ) {

		}

	}

	new FED_Pay_Invoice();
}