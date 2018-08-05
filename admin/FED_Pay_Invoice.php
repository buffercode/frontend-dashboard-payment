<?php

namespace FED_PayPal_Admin;


if ( ! class_exists( 'FED_Pay_Invoice' ) ) {
	/**
	 * Class FED_Pay_Invoice
	 * @package FED_PayPal_Admin
	 */
	class FED_Pay_Invoice {
		public function __construct() {
			add_action( 'wp_ajax_fed_admin_payment_invoice_details', array( $this, 'save_invoice_details' ) );
			add_action( 'wp_ajax_fed_admin_payment_invoice_details', array( $this, 'save_invoice_templates' ) );

		}

		public function save_invoice_details() {
			$request                            = $_REQUEST;
			$invoice_details_options            = get_option( 'fed_admin_settings_payments' );
			$invoice_details_options['invoice'] = array(
				'details' => array(
					'logo'         => isset( $request['invoice']['logo'] ) ? (int) $request['invoice']['logo'] : '',
					'width'        => isset( $request['invoice']['width'] ) ? fed_sanitize_text_field( $request['invoice']['width'] ) : '',
					'height'       => isset( $request['invoice']['height'] ) ? fed_sanitize_text_field( $request['invoice']['height'] ) : '',
					'country'      => isset( $request['invoice']['country'] ) ? fed_sanitize_text_field( $request['invoice']['country'] ) : '',
					'postal_code'  => isset( $request['invoice']['postal_code'] ) ? fed_sanitize_text_field( $request['invoice']['postal_code'] ) : '',
					'state'        => isset( $request['invoice']['state'] ) ? fed_sanitize_text_field( $request['invoice']['state'] ) : '',
					'city'         => isset( $request['invoice']['city'] ) ? fed_sanitize_text_field( $request['invoice']['city'] ) : '',
					'street_name'  => isset( $request['invoice']['street_name'] ) ? fed_sanitize_text_field( $request['invoice']['street_name'] ) : '',
					'door_number'  => isset( $request['invoice']['door_number'] ) ? fed_sanitize_text_field( $request['invoice']['door_number'] ) : '',
					'company_name' => isset( $request['invoice']['company_name'] ) ? fed_sanitize_text_field( $request['invoice']['company_name'] ) : '',
				)
			);

			$new_settings = apply_filters( 'fed_admin_settings_payments_invoice_details_save', $invoice_details_options, $request );

			update_option( 'fed_admin_settings_payments', $new_settings );

			wp_send_json_success( array(
				'message' => __( 'Invoice Details Updated Successfully ' )
			) );

		}

		public function save_invoice_templates() {
			$request                            = $_REQUEST;
			$invoice_details_options            = get_option( 'fed_admin_settings_payments' );
			$invoice_details_options['invoice'] = array(
				'template' => array(
					'logo'         => isset( $request['invoice']['logo'] ) ? (int) $request['invoice']['logo'] : '',
					'width'        => isset( $request['invoice']['width'] ) ? fed_sanitize_text_field( $request['invoice']['width'] ) : '',
					'height'       => isset( $request['invoice']['height'] ) ? fed_sanitize_text_field( $request['invoice']['height'] ) : '',
					'country'      => isset( $request['invoice']['country'] ) ? fed_sanitize_text_field( $request['invoice']['country'] ) : '',
					'postal_code'  => isset( $request['invoice']['postal_code'] ) ? fed_sanitize_text_field( $request['invoice']['postal_code'] ) : '',
					'state'        => isset( $request['invoice']['state'] ) ? fed_sanitize_text_field( $request['invoice']['state'] ) : '',
					'city'         => isset( $request['invoice']['city'] ) ? fed_sanitize_text_field( $request['invoice']['city'] ) : '',
					'street_name'  => isset( $request['invoice']['street_name'] ) ? fed_sanitize_text_field( $request['invoice']['street_name'] ) : '',
					'door_number'  => isset( $request['invoice']['door_number'] ) ? fed_sanitize_text_field( $request['invoice']['door_number'] ) : '',
					'company_name' => isset( $request['invoice']['company_name'] ) ? fed_sanitize_text_field( $request['invoice']['company_name'] ) : '',
				)
			);

			$new_settings = apply_filters( 'fed_admin_settings_payments_invoice_details_save', $invoice_details_options, $request );

			update_option( 'fed_admin_settings_payments', $new_settings );

			wp_send_json_success( array(
				'message' => __( 'Invoice Details Updated Successfully ' )
			) );

		}


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
							'user_value' => isset( $settings['settings']['invoice']['details']['logo'] ) ? $settings['settings']['invoice']['details']['logo'] : '',
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
								'user_value'  => isset( $settings['settings']['invoice']['details']['width'] ) ? $settings['settings']['invoice']['details']['width'] : '',
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
								'input_meta'  => 'invoice[height]',
								'user_value'  => isset( $settings['settings']['invoice']['details']['height'] ) ? $settings['settings']['invoice']['details']['height'] : '',
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
								'user_value'  => isset( $settings['settings']['invoice']['details']['company_name'] ) ? $settings['settings']['invoice']['details']['company_name'] : '',
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
								'user_value'  => isset( $settings['settings']['invoice']['details']['door_number'] ) ? $settings['settings']['invoice']['details']['door_number'] : '',
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
								'user_value'  => isset( $settings['settings']['invoice']['details']['street_name'] ) ? $settings['settings']['invoice']['details']['street_name'] : '',
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
								'user_value'  => isset( $settings['settings']['invoice']['details']['city'] ) ? $settings['settings']['invoice']['details']['city'] : '',
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
								'user_value'  => isset( $settings['settings']['invoice']['details']['state'] ) ? $settings['settings']['invoice']['details']['state'] : '',
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
								'user_value'  => isset( $settings['settings']['invoice']['details']['postal_code'] ) ? $settings['settings']['invoice']['details']['postal_code'] : '',
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
								'user_value'  => isset( $settings['settings']['invoice']['details']['country'] ) ? $settings['settings']['invoice']['details']['country'] : '',
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
			$templates = fed_pay_invoice_templates();
			?>
            <div class="p-20">
                <div class="row">
					<?php foreach ( $templates as $template ) { ?>
                        <div class="col-md-6">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><?php echo $template['name'] . ' ( ' . $template['version'] . ' )' ?></h3>
                                </div>
                                <div class="panel-body">
                                    <a target="_blank" href="<?php echo $template['image_full_url']; ?>">
                                        <img class="img-responsive" src="<?php echo $template['image_thumb_url']; ?>"/>
                                    </a>
                                    <div class="text-center padd_top_20">
                                        <button class="btn btn-secondary"><i class="fa fa-check"></i> Selected</button>
                                    </div>
                                </div>
                            </div>
                        </div>
					<?php } ?>
                </div>
            </div>

			<?php
		}

	}

	new FED_Pay_Invoice();
}