<?php

namespace FED_PayPal;

use function bcdump;
use Exception;
use function fed_isset;
use function fed_isset_request;
use function fed_isset_sanitize;
use FED_Log;
use function get_option;
use function gettype;
use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Amount;
use PayPal\Api\BillingInfo;
use PayPal\Api\CancelNotification;
use PayPal\Api\ChargeModel;
use PayPal\Api\Cost;
use PayPal\Api\Currency;
use PayPal\Api\Details;
use PayPal\Api\Invoice;
use PayPal\Api\InvoiceAddress as InvoiceAddress;
use PayPal\Api\InvoiceItem;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\MerchantInfo;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Notification;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\PaymentDetail;
use PayPal\Api\PaymentExecution;
use PayPal\Api\PaymentTerm;
use PayPal\Api\Payout;
use PayPal\Api\PayoutItem;
use PayPal\Api\PayoutSenderBatchHeader;
use PayPal\Api\Phone;
use PayPal\Api\Plan;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Refund;
use PayPal\Api\RefundDetail;
use PayPal\Api\Sale;
use PayPal\Api\ShippingAddress;
use PayPal\Api\ShippingInfo;
use PayPal\Api\Tax;
use PayPal\Api\Template;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Common\PayPalModel;
use PayPal\Rest\ApiContext;
use function plugins_url;
use function the_permalink;
use function var_dump;
use function wp_die;
use function wp_redirect;

class FED_PayPal {
	protected $paypal;

	protected $success_url;

	protected $cancel_url;

	protected $billing_success_url;

	protected $billing_cancel_url;

	protected $mode;

	protected $settings;


	public function __construct() {

		$this->settings = get_option( 'fed_admin_settings_payments' );

		if ( $this->settings ) {
			$this->mode                = [
				'mode'           => fed_isset_sanitize( $this->settings['paypal']['api']['type'] ),
				'log.LogEnabled' => true,
				'log.FileName'   => BC_FED_PAY_PLUGIN_DIR . '/log/paypal.log',
				'log.LogLevel'   => 'FINE'
			];
			$this->success_url         = fed_isset_sanitize( get_permalink( $this->settings['paypal']['api']['success_url'] ) );
			$this->cancel_url          = fed_isset_sanitize( get_permalink( $this->settings['paypal']['api']['cancel_url'] ) );
			$this->billing_success_url = fed_isset_sanitize( get_permalink( $this->settings['paypal']['api']['success_url'] ) );
			$this->billing_cancel_url  = fed_isset_sanitize( get_permalink( $this->settings['paypal']['api']['cancel_url'] ) );

			$this->paypal = new ApiContext(
				new OAuthTokenCredential(
					fed_isset_sanitize( $this->settings['paypal']['api']['type'] ) === 'Live' ? fed_isset( $this->settings['paypal']['api']['live_client_id'] ) : fed_isset( $this->settings['paypal']['api']['sandbox_client_id'] ),
					fed_isset_sanitize( $this->settings['paypal']['api']['type'] ) === 'Live' ? fed_isset( $this->settings['paypal']['api']['live_secrete_id'] ) : fed_isset( $this->settings['paypal']['api']['sandbox_secrete_id'] )
				)
			);

			$this->paypal->setConfig( $this->mode );
		}
	}

	public function is_true() {
		return $this->settings === false ? false : true;
	}

	public function payment_start() {
// ### Payer
// A resource representing a Payer that funds a payment
// For paypal account payments, set payment method
// to 'paypal'.
		$payer = new Payer();
		$payer->setPaymentMethod( "paypal" );

// ### Itemized information
// (Optional) Lets you specify item wise
// information
//		$format = [
//			'payments' => [
////				'intent'        => '',
////				'payer'         => '',
//				'status'        => '',
////				'redirect_urls' => [
////					'return_url' => '',
////					'cancel_url' => '',
////				],
//				'transactions'  => [
//					'transaction1' => [
//
//
//						'item_list'      => [
//							'item1' => [
//								'name'        => '',
//								'currency'    => '',
//								'description' => '',
//								'quantity'    => '',
//								'url'         => '',
//								'sku'         => '',
//								'price'       => '',
//								'tax'         => '',
//							],
//							'item2' => [
//								'name'        => '',
//								'currency'    => '',
//								'description' => '',
//								'quantity'    => '',
//								'url'         => '',
//								'sku'         => '',
//								'price'       => '',
//								'tax'         => '',
//							]
//						],
//						'amount'         => [
//							'currency' => '',
//							'total'    => '',
//							'details'  => [
//								'shipping'          => '',
//								'tax'               => '',
//								'sub_total'         => '',
//								'handling_fee'      => '',
//								'shipping_discount' => '',
//								'insurance'         => '',
//								'gift_wrap'         => '',
//							],
//						],
//						'description'    => '',
//						'invoice_number' => '',
//						'reference_id'   => '',
//						'note_to_payee'  => '',
//						'purchase_order' => '',
//					]
//				]
//			]
//		];


		$item1 = new Item();
		$item1->setName( 'Ground Coffee 40 oz' )
		      ->setCurrency( 'USD' )
		      ->setDescription( 'This is for Ground Coffee 40' )
		      ->setQuantity( 1 )
		      ->setUrl( 'https://buffercode.com' )
		      ->setSku( "123123" )// Similar to `item_number` in Classic API
		      ->setPrice( 25 );
		$item2 = new Item();
		$item2->setName( 'Granola bars' )
		      ->setCurrency( 'USD' )
		      ->setQuantity( 1 )
		      ->setDescription( 'This is for Granola bars' )
		      ->setUrl( 'https://ifecho.com' )
		      ->setSku( "321321" )// Similar to `item_number` in Classic API
		      ->setPrice( 50 );

		$itemList = new ItemList();
		$itemList->setItems( array( $item1, $item2 ) );

// ### Additional payment details
// Use this optional field to set additional
// payment information such as tax, shipping
// charges etc.
		$details = new Details();
		$details->setShipping( 15 )
		        ->setTax( 15 )
		        ->setSubtotal( 75 );

// ### Amount
// Lets you specify a payment amount.
// You can also specify additional details
// such as shipping, tax.
		$amount = new Amount();
		$amount->setCurrency( "USD" )
		       ->setTotal( 105 )
		       ->setDetails( $details );

// ### Transaction
// A transaction defines the contract of a
// payment - what is the payment for and who
// is fulfilling it.
		$transaction = new Transaction();
		$transaction->setAmount( $amount )
		            ->setItemList( $itemList )
		            ->setDescription( "Payment description" )
		            ->setInvoiceNumber( uniqid( date( 'Ymd-' ), false ) );


//		$payee = new Payee();
//		$payee->setEmail( "vinoyoyo@gmail.com" );

// ### Redirect urls
// Set the urls that the buyer must be redirected to after
// payment approval/ cancellation.
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl( $this->success_url )
		             ->setCancelUrl( $this->cancel_url );

// ### Payment
// A Payment Resource; create one using
// the above types and intent set to 'sale'
		$payment = new Payment();
		$payment->setIntent( "sale" )
		        ->setPayer( $payer )
		        ->setRedirectUrls( $redirectUrls )
		        ->setTransactions( array( $transaction ) );


// For Sample Purposes Only.
		$request = clone $payment;

// ### Create Payment
// Create a payment by calling the 'create' method
// passing it a valid apiContext.
// (See bootstrap.php for more on `ApiContext`)
// The return object contains the state and the
// url to which the buyer must be redirected to
// for payment approval
		try {
			$payment->create( $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( 'Problem in PayPal Payment on function process_paypal at PaypalController' );
			exit( 1 );
		}

// ### Get redirect url
// The API response provides the url that you must redirect
// the buyer to. Retrieve the url from the $payment->getApprovalLink()
// method
		$approvalUrl = $payment->getApprovalLink();

		return wp_redirect( $approvalUrl );
	}

	/**
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function payment_success( Request $request ) {

		if ( ! $request->has( 'paymentId' ) && ! $request->has( 'PayerID' ) ) {
			return $this->payment_cancel();
		}

		$paymentId = $request->paymentId;
		$payment   = Payment::get( $paymentId, $this->paypal );

		$execution = new PaymentExecution();
		$execution->setPayerId( $request->PayerID );

		try {
			$result = $payment->execute( $execution, $this->paypal );
		} catch ( Exception $e ) {
			FED_Log::writeLog( 'Problem in PayPal Payment on function paypal_success at PaypalController' );

			wp_die( 'Something went wrong, please contact admin' );
		}

		return $result;
	}

	public function payment_cancel() {
		return get_permalink( $this->cancel_url );

	}

	/**
	 * Get Payment By Payment ID
	 */
	public function get_payment_by_id( $transaction_id ) {
		try {
			$payment = Payment::get( $transaction_id, $this->paypal )->toArray();
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

		FED_Log::writeLog( $payment );

		return $payment;
	}

	/**
	 * Get payment by count - max 100
	 */
	public function get_payments( $count = 20, $index = 1 ) {
		try {
			$params = array( 'count' => $count, 'start_index' => $index );

			$payments = Payment::all( $params, $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

//		foreach($payments->getPayments() as $payment){
//		    print_r($payment);
//        }
		FED_Log::writeLog( $payments->getPayments() );

		return $payments;
//		return $payments->getPayments();
	}


	/**
	 * Not working
	 */
	public function send_payout() {

		$payouts = new Payout();

		$senderBatchHeader = new PayoutSenderBatchHeader();

		$senderBatchHeader->setSenderBatchId( uniqid( date( 'Ymd-' ), false ) )
		                  ->setEmailSubject( "You have a new Payout!" );
		$senderItem = new PayoutItem();
		$senderItem->setRecipientType( 'Email' )
		           ->setNote( 'Thanks for your patronage!' )
		           ->setReceiver( 'ma.vinothkumar@gmail.com' )
		           ->setSenderItemId( "2014031400023" )
		           ->setAmount( new Currency( '{
                        "value":"20.0",
                        "currency":"USD"
                    }' ) );

		$payouts->setSenderBatchHeader( $senderBatchHeader )
		        ->addItem( $senderItem );

		try {
			$output = $payouts->createSynchronous( $this->paypal );
		} catch ( Exception $ex ) {

			FED_Log::writeLog( $ex );
			exit( 1 );
		}

//		FED_Log::writeLog( $output );
		return $output;
	}

	/**
	 * Refund Sale
	 */
	public function refund_sale() {
		// ### Refund amount
// Includes both the refunded amount (to Payer)
// and refunded fee (to Payee). Use the $amt->details
// field to mention fees refund details.
		$amt = new Amount();
		$amt->setCurrency( 'USD' )
		    ->setTotal( 65 );

// ### Refund object
		$refund = new Refund();
		$refund->setAmount( $amt );

// ###Sale
// A sale transaction.
// Create a Sale object with the
// given sale transaction id.
		$sale = new Sale();
		$sale->setId( '2GJ082985G0547009' );
		try {
			// Create a new apiContext object so we send a new
			// PayPal-Request-Id (idempotency) header for this resource
			//$apiContext = getApiContext( env( 'PAYPAL_CLIENT' ), env( 'PAYPAL_SECRET' ) );

			// Refund the sale
			// (See bootstrap.php for more on `ApiContext`)
			$refundedSale = $sale->refund( $refund, $this->paypal );
		} catch ( Exception $ex ) {
			// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
//		FED_Log::writeLog( $refundedSale );
		return $refundedSale;

	}

	/**
	 * Get sale details by ID
	 */
	public function get_sale_details_by_id() {

		try {
			// ### Retrieve the sale object
			// Pass the ID of the sale
			// transaction from your payment resource.
			$sale = Sale::get( '2GJ082985G0547009', $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

//		FED_Log::writeLog( $sale );
		return $sale;

	}

	/**
	 * Create Plan
	 *
	 * @param $request
	 *
	 * @return Plan
	 */
	public function create_plan( $request ) {
		// Create a new instance of Plan object
		$plan = new Plan();

		$pD = $charge = array();

// # Basic Information
// Fill up the basic information that is required for the plan
		$plan->setName( fed_isset_sanitize( $request['name'] ) )
		     ->setDescription( fed_isset_sanitize( $request['description'] ) )
		     ->setType( fed_isset_sanitize( $request['type'] ) );

		if ( isset( $request['payment_definition'] ) && count( $request['payment_definition'] ) > 0 ) {
			foreach ( $request['payment_definition'] as $index => $payment_definition ) {
				// # Payment definitions for this billing plan.
				$paymentDefinition = new PaymentDefinition();
// The possible values for such setters are mentioned in the setter method documentation.
// Just open the class file. e.g. lib/PayPal/Api/PaymentDefinition.php and look for setFrequency method.
// You should be able to see the acceptable values in the comments.
				$paymentDefinition->setName( fed_isset_sanitize( $payment_definition['name'] ) )
				                  ->setType( fed_isset_sanitize( $payment_definition['type'] ) )
				                  ->setFrequency( fed_isset_sanitize( $payment_definition['frequency'] ) )
				                  ->setFrequencyInterval( fed_isset_sanitize( $payment_definition['freq_interval'] ) )
				                  ->setCycles( fed_isset_sanitize( $payment_definition['cycle'] ) )
				                  ->setAmount( new Currency( array(
					                  'value'    => (int) fed_isset_sanitize( $payment_definition['amount'] ),
					                  'currency' => fed_isset_sanitize( $payment_definition['currency'] )
				                  ) ) );

// Charge Models for Shipping
				if ( isset( $payment_definition['shipping']['type'] ) ) {
					$shipping = new ChargeModel();
					$shipping->setType( 'SHIPPING' )
					         ->setAmount( new Currency( array(
						         'value'    => fed_isset_sanitize( $payment_definition['shipping']['amount'] ),
						         'currency' => fed_isset_sanitize( $payment_definition['shipping']['currency'] )
					         ) ) );
					$charge [] = $shipping;
				}
// Charge Models for Tax
				if ( isset( $payment_definition['tax']['type'] ) ) {
					$tax = new ChargeModel();
					$tax->setType( 'TAX' )
					    ->setAmount( new Currency( array(
						    'value'    => fed_isset_sanitize( $payment_definition['tax']['amount'] ),
						    'currency' => fed_isset_sanitize( $payment_definition['tax']['currency'] )
					    ) ) );
					$charge [] = $tax;
				}

				if ( count( $charge ) > 0 ) {
					$paymentDefinition->setChargeModels( $charge );
				}
				$pD[] = $paymentDefinition;
			}
		}
		$merchantPreferences = new MerchantPreferences();
		$merchantPreferences->setReturnUrl( $this->billing_success_url )
		                    ->setCancelUrl( $this->billing_cancel_url )
		                    ->setAutoBillAmount( fed_isset_request( $request, 'auto_billing', 'NO' ) )
		                    ->setInitialFailAmountAction( fed_isset_request( $request, 'fail_amount_action', 'CONTINUE' ) )
		                    ->setMaxFailAttempts( (int) fed_isset_request( $request, 'max_fail_attempt', 0 ) );
		if ( fed_isset_request( $request, 'setup_fee', false ) && fed_isset_request( $request, 'setup_fee_currency', false ) ) {
			$merchantPreferences->setSetupFee( new Currency( array(
				'value'    => $request['setup_fee'],
				'currency' => $request['setup_fee_currency']
			) ) );
		}

		if ( isset( $request['payment_definition'] ) && count( $request['payment_definition'] ) > 0 ) {
			$plan->setPaymentDefinitions( $pD );
		}
		$plan->setMerchantPreferences( $merchantPreferences );

		$request = clone $plan;

// ### Create Plan
		try {
			$output = $plan->create( $this->paypal );
			$plan   = $output->toArray();
			$this->activate_plan( $plan['id'] );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( array( 'plan_exception' => $ex ) );

			return $ex;
		}

		FED_Log::writeLog( $output );

		return $output;

	}

	/**
	 * Activate plan
	 *
	 * @param $plan_id
	 *
	 * @param string $status
	 *
	 * @return bool
	 */
	public function activate_plan( $plan_id, $status = 'ACTIVE' ) {
		try {
			$option = $status === 'DELETE' ? 'remove' : 'replace';
//			FED_Log::writeLog( $plan_id );
			$patch = new Patch();

			$plan = new Plan();

			$value = new PayPalModel( '{
	       "state":"' . $status . '"
	     }' );

			$patch->setOp( $option )
			      ->setPath( '/' )
			      ->setValue( $value );
			$patchRequest = new PatchRequest();
			$patchRequest->addPatch( $patch );

			$status = $plan->setId( $plan_id )
			               ->update( $patchRequest, $this->paypal );

//			$plans = Plan::get( 'P-424033546V936581NEEXF2OI', $this->paypal );

		} catch ( Exception $ex ) {
//			FED_Log::writeLog( $ex->getData() );
			return $ex;
		}

//		FED_Log::writeLog( $status );

		return $status;
	}

	/**
	 * Get plan by Plan ID
	 */
	public function get_plan_by_id() {
//	    $plan = Plan::get( 'P-424033546V936581NEEXF2OI', $this->paypal );
		$plan = Plan::get( 'P-6LP28471CX649530BSSRSEMI', $this->paypal );

		return $plan;
	}

	/**
	 * Update plan by Payment Definition ID
	 * eg, PD-2VA428172B789120KEAJDNCI
	 */
	public function update_plan() {
		try {
			$patch = new Patch();

			$plan = new Plan();

			$patch->setOp( 'replace' )
			      ->setPath( '/payment-definitions/' . 'PD-8V303585U0198180YEEXF2OI' )
			      ->setValue( json_decode(
				      '{
                    "name": "Updated Payment Definition",
                    "frequency": "Day",
                    "amount": {
                        "currency": "USD",
                        "value": "50"
                    }
            }'
			      ) );
			$patchRequest = new PatchRequest();
			$patchRequest->addPatch( $patch );

			$plan->setId( 'P-424033546V936581NEEXF2OI' )
			     ->update( $patchRequest, $this->paypal );

			/**
			 * Plan ID
			 */
			$plans = Plan::get( 'P-424033546V936581NEEXF2OI', $this->paypal );

		} catch ( Exception $ex ) {
			// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

		return $plans;
	}

	/**
	 * List Billing Plan
	 * status : CREATED, ACTIVE, INACTIVE, DELETED.
	 *
	 * @param string $status
	 *
	 * @return \PayPal\Api\PlanList
	 */
	public function list_plans( $status = 'ACTIVE' ) {
		$params = array( 'status' => $status, 'page_size' => '5', 'total_required' => 'yes' );

		return Plan::all( $params, $this->paypal );
	}

	/**
	 * Create and active billing agreement
	 * part-2 : billing_agreement_success()
	 */
	public function create_active_billing_agreement() {
		$agreement = new Agreement();

		$agreement->setName( 'Buy this plan' )
		          ->setDescription( 'Buy this plan' )
		          ->setStartDate( '2018-07-19T23:45:04Z' );

// Add Plan ID
// Please note that the plan Id should be only set in this case.
		$plan = new Plan();
		$plan->setId( 'P-6LP28471CX649530BSSRSEMI' );
		$agreement->setPlan( $plan );

// Add Payer
		$payer = new Payer();
		$payer->setPaymentMethod( 'paypal' );
		$agreement->setPayer( $payer );

// Add Shipping Address
		$shippingAddress = new ShippingAddress();
		$shippingAddress->setLine1( '111 First Street' )
		                ->setCity( 'Saratoga' )
		                ->setState( 'CA' )
		                ->setPostalCode( '95070' )
		                ->setCountryCode( 'US' );
		$agreement->setShippingAddress( $shippingAddress );

// ### Create Agreement
		try {
			// Please note that as the agreement has not yet activated, we wont be receiving the ID just yet.
			$agreement = $agreement->create( $this->paypal );

			// ### Get redirect url
			// The API response provides the url that you must redirect
			// the buyer to. Retrieve the url from the $agreement->getApprovalLink()
			// method
			$approvalUrl = $agreement->getApprovalLink();

		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

		return wp_redirect( $approvalUrl );

		//Execute agreement

		// ## Approval Status
// Determine if the user accepted or denied the request

	}

	/**
	 * Billing agreement success based on create_active_billing_agreement
	 */
	public function billing_agreement_success() {
		if ( isset( $_GET['token'] ) ) {

			$token     = $_GET['token'];
			$agreement = new Agreement();
			try {
				$agreement->execute( $token, $this->paypal );
			} catch ( Exception $ex ) {
				FED_Log::writeLog( $ex );
				exit( 1 );
			}
			try {
				$agreement = Agreement::get( $agreement->getId(), $this->paypal );
			} catch ( Exception $ex ) {
				FED_Log::writeLog( $ex );
				exit( 1 );
			}

//			FED_Log::writeLog( $agreement );
			return $agreement;

		} else {
			FED_Log::writeLog( 'you have cancelled' );
		}
	}

	/**
	 * Delete Billing Plan by ID
	 *
	 * @return boolean
	 */
	public function delete_billing_by_id( $id ) {
		$plan = new Plan();

		return $plan->setId( $id )->delete( $this->paypal );
	}

	/**
	 * Get Billing Agreement
	 */
	public function get_billing_agreement( $agreement_id ) {
		return Agreement::get( $agreement_id, $this->paypal );
	}

	/**
	 * Update Billing Agreement
	 */
	public function update_billing_agreement( $agreement_id ) {
		$patch = new Patch();

		$agreement = new Agreement();

		$patch->setOp( 'replace' )
		      ->setPath( '/' )
		      ->setValue( json_decode( '{
            "description": "New Description replace by old"}' ) );
		$patchRequest = new PatchRequest();
		$patchRequest->addPatch( $patch );
		try {
			$agreement->setId( $agreement_id )->update( $patchRequest, $this->paypal );
			$agreements = Agreement::get( $agreement_id, $this->paypal );

		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

		return $agreements;
	}

	/**
	 * Suspend Billing Agreement
	 */
	public function suspend_billing_agreement( $agreement_id ) {
		//Create an Agreement State Descriptor, explaining the reason to suspend.
		$agreementStateDescriptor = new AgreementStateDescriptor();
		$agreementStateDescriptor->setNote( "Suspending the agreement due to testing" );

		try {
			$agreement = new Agreement();

			$agreement->setId( $agreement_id )->suspend( $agreementStateDescriptor, $this->paypal );

			// Lets get the updated Agreement Object
			$agreement = Agreement::get( $agreement_id, $this->paypal );

		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

//		FED_Log::writeLog( $agreement );
		return $agreement;
	}

	/**
	 * Reactive Billing Agreement
	 */
	public function reactive_billing_agreement( $agreement_id ) {
		//Create an Agreement State Descriptor, explaining the reason to suspend.
		$agreementStateDescriptor = new AgreementStateDescriptor();
		$agreementStateDescriptor->setNote( "Reactive the agreement due to testing" );

		try {
			$agreement = new Agreement();

			$agreement->setId( $agreement_id )->reActivate( $agreementStateDescriptor, $this->paypal );

			// Lets get the updated Agreement Object
			$agreement = Agreement::get( $agreement_id, $this->paypal );

		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

//		FED_Log::writeLog( $agreement );
		return $agreement;
	}

	/**
	 * Invoice
	 */

	/**
	 * Create new Invoice
	 */
	public function create_new_invoice() {
		$invoice = new Invoice();

// ### Invoice Info
// Fill in all the information that is
// required for invoice APIs
		$invoice
			->setMerchantInfo( new MerchantInfo() )
			->setBillingInfo( array( new BillingInfo() ) )
			->setNote( "Medical Invoice 16 Jul, 2013 PST" )
			->setPaymentTerm( new PaymentTerm() )
			->setShippingInfo( new ShippingInfo() );

// ### Merchant Info
// A resource representing merchant information that can be
// used to identify merchant
		$invoice->getMerchantInfo()
		        ->setEmail( "buffercode-facilitator@gmail.com" )
		        ->setFirstName( "Dennis" )
		        ->setLastName( "Doctor" )
		        ->setbusinessName( "Medical Professionals, LLC" )
		        ->setPhone( new Phone() )
		        ->setAddress( new InvoiceAddress() );

		$invoice->getMerchantInfo()->getPhone()
		        ->setCountryCode( "001" )
		        ->setNationalNumber( "5032141716" );

// ### Address Information
// The address used for creating the invoice
		$invoice->getMerchantInfo()->getAddress()
		        ->setLine1( "1234 Main St." )
		        ->setCity( "Portland" )
		        ->setState( "OR" )
		        ->setPostalCode( "97217" )
		        ->setCountryCode( "US" );

// ### Billing Information
// Set the email address for each billing
		$billing = $invoice->getBillingInfo();
		$billing[0]
			->setEmail( "example@example.com" );

		$billing[0]->setBusinessName( "Jay Inc" )
		           ->setAdditionalInfo( "This is the billing Info" )
		           ->setAddress( new InvoiceAddress() );

		$billing[0]->getAddress()
		           ->setLine1( "1234 Main St." )
		           ->setCity( "Portland" )
		           ->setState( "OR" )
		           ->setPostalCode( "97217" )
		           ->setCountryCode( "US" );

// ### Items List
// You could provide the list of all items for
// detailed breakdown of invoice
		$items    = array();
		$items[0] = new InvoiceItem();
		$items[0]
			->setName( "Sutures" )
			->setQuantity( 100 )
			->setUnitPrice( new Currency() );

		$items[0]->getUnitPrice()
		         ->setCurrency( "USD" )
		         ->setValue( 5 );

// #### Tax Item
// You could provide Tax information to each item.
		$tax = new Tax();
		$tax->setPercent( 1 )->setName( "Local Tax on Sutures" );
		$items[0]->setTax( $tax );

// Second Item
		$items[1] = new InvoiceItem();
// Lets add some discount to this item.
		$item1discount = new Cost();
		$item1discount->setPercent( "3" );
		$items[1]
			->setName( "Injection" )
			->setQuantity( 5 )
			->setDiscount( $item1discount )
			->setUnitPrice( new Currency() );

		$items[1]->getUnitPrice()
		         ->setCurrency( "USD" )
		         ->setValue( 5 );

// #### Tax Item
// You could provide Tax information to each item.
		$tax2 = new Tax();
		$tax2->setPercent( 3 )->setName( "Local Tax on Injection" );
		$items[1]->setTax( $tax2 );

		$invoice->setItems( $items );

// #### Final Discount
// You can add final discount to the invoice as shown below. You could either use "percent" or "value" when providing the discount
		$cost = new Cost();
		$cost->setPercent( "2" );
		$invoice->setDiscount( $cost );

		$invoice->getPaymentTerm()
		        ->setTermType( "NET_45" );

// ### Shipping Information
		$invoice->getShippingInfo()
		        ->setFirstName( "Sally" )
		        ->setLastName( "Patient" )
		        ->setBusinessName( "Not applicable" )
		        ->setPhone( new Phone() )
		        ->setAddress( new InvoiceAddress() );

		$invoice->getShippingInfo()->getPhone()
		        ->setCountryCode( "001" )
		        ->setNationalNumber( "5039871234" );

		$invoice->getShippingInfo()->getAddress()
		        ->setLine1( "1234 Main St." )
		        ->setCity( "Portland" )
		        ->setState( "OR" )
		        ->setPostalCode( "97217" )
		        ->setCountryCode( "US" );

// ### Logo
// You can set the logo in the invoice by providing the external URL pointing to a logo
		$invoice->setLogoUrl( 'https://www.paypalobjects.com/webstatic/i/logo/rebrand/ppcom.svg' );

// For Sample Purposes Only.
		$request = clone $invoice;

		try {
			// ### Create Invoice
			// Create an invoice by calling the invoice->create() method
			// with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
			$invoice->create( $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

		FED_Log::writeLog( $invoice );

		return $invoice;

	}

	/**
	 * Send an Invoice
	 */
	public function send_invoice() {
		try {

			// ### Send Invoice
			// Send a legitimate invoice to the payer
			// with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
			$invoice    = new invoice();
			$sendStatus = $invoice->setId( 'INV2-6XRW-K6WA-EWRF-BMTF' )->send( $this->paypal );
		} catch ( Exception $ex ) {
			var_dump( $ex );
			exit( 1 );
		}

// ### Retrieve Invoice
// Retrieve the invoice object by calling the
// static `get` method
// on the Invoice class by passing a valid
// Invoice ID
// (See bootstrap.php for more on `ApiContext`)
//		try {
//			$invoice = Invoice::get('INV2-6XRW-K6WA-EWRF-BMTF', $this->paypal );
//		} catch ( Exception $ex ) {
//			FED_Log::writeLog($ex );
//			exit( 1 );
//		}

//		FED_Log::writeLog( $sendStatus );
		return $sendStatus;
	}

	/**
	 * Update an Invoice
	 *
	 * Functionality to be added
	 */
	public function update_invoice() {

// ### Update Invoice
// Lets update some information
		$invoice = new Invoice();
		$invoice->setId( 'INV2-6XRW-K6WA-EWRF-BMTF' )->setStatus( 'UNPAID' )->setInvoiceDate( "2014-12-16 PST" );

// ### NOTE: These are the work-around added to the
// sample, to get past the bug in PayPal APIs.
// There is already an internal ticket #PPTIPS-1932 created for it.
		$invoice->setDiscount( null );

		try {
			// ### Update Invoice
			// Update an invoice by calling the invoice->update() method
			// with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
			$invoice->update( $this->paypal );
		} catch ( Exception $ex ) {
			// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

// ### Retrieve Invoice
// Retrieve the invoice object by calling the
// static `get` method
// on the Invoice class by passing a valid
// Invoice ID
// (See bootstrap.php for more on `ApiContext`)
		try {
			$invoice = Invoice::get( $invoice->getId(), $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

		return $invoice;
	}

	/**
	 * Get Invoice
	 */
	public function get_invoice( $invoice_id ) {
		$invoice   = new Invoice();
		$invoiceId = $invoice->setId( $invoice_id )->getId();

// ### Retrieve Invoice
// Retrieve the invoice object by calling the
// static `get` method
// on the Invoice class by passing a valid
// Invoice ID
// (See bootstrap.php for more on `ApiContext`)
		try {
			$invoice = Invoice::get( $invoiceId, $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

//		FED_Log::writeLog( $invoice );
		return $invoice;

	}

	/**
	 * Remind Invoice
	 */
	public function remind_invoice( $invoice_id ) {
		try {
			$notify = new Notification();
			$notify
				->setSubject( "Past due" )
				->setNote( "Please pay soon" )
				->setSendToMerchant( true );

			$invoice = new Invoice();

			$remindStatus = $invoice->setId( $invoice_id )->remind( $notify, $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

//		FED_Log::writeLog( $remindStatus );
		return $remindStatus;

	}

	/**
	 * Cancel Invoice
	 */
	public function cancel_invoice( $invoice_id ) {
		try {

			$notify = new CancelNotification();
			$notify
				->setSubject( "Past due" )
				->setNote( "Canceling invoice" )
				->setSendToMerchant( true )
				->setSendToPayer( true );

			$invoice = new Invoice();

			$cancelStatus = $invoice->setId( $invoice_id )->cancel( $notify, $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

//		FED_Log::writeLog( $cancelStatus );
		return $cancelStatus;
	}

	/**
	 * Delete Invoice
	 */
	public function delete_invoice( $invoice_id ) {
		try {
			$invoice      = new Invoice();
			$deleteStatus = $invoice->setId( $invoice_id )->delete( $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

		FED_Log::writeLog( $deleteStatus );

		return $invoice_id;
	}

	/**
	 * Record Payment
	 */
	public function record_payment( $payment_id ) {
		try {
			$record = new PaymentDetail(
				array( 'method' => 'CASH', 'date' => '2014-07-06 03:30:00 PST', 'note' => 'Cash Received' ) );

			$invoice = new Invoice();

			$recordStatus = $invoice->setId( $payment_id )->recordPayment( $record, $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

		FED_Log::writeLog( $recordStatus );

		return $recordStatus;

	}

	/**
	 * Record Refund
	 */
	public function record_refund( $invoice_id ) {
		try {
			$refund = new RefundDetail( array(
					'date' => '2014-07-06 03:30:00 PST',
					'note' => 'Refund provided by cash.'
				)
			);

			$invoice = new Invoice();

			$refundStatus = $invoice->setId( $invoice_id )->recordRefund( $refund, $this->paypal );
		} catch ( Exception $ex ) {
			FED_Log::writeLog( $ex );
			exit( 1 );
		}

		FED_Log::writeLog( $refundStatus );

		return $refundStatus;
	}

	/**
	 * Get Invoice Template
	 */
	public function get_invoice_template() {
		$invoiceTemplate = new Invoice();
		$templateId      = $invoiceTemplate->setTemplateId( 'TEMP-0RL19888CB7306126' )->getTemplateId();
		FED_Log::writeLog( Template::get( $templateId, $this->paypal ) );

		return Template::get( $templateId, $this->paypal );
	}

}
