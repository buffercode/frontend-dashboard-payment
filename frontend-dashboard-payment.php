<?php
/**
 * Plugin Name: Frontend Dashboard Payment
 * Plugin URI: https://buffercode.com/plugin/frontend-dashboard-payment
 * Description: Frontend dashboard payment provides easy to do Payment in PayPal.
 * Version: 1
 * Author: vinoth06
 * Author URI: https://buffercode.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: frontend-dashboard
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Version Number
 */
define( 'BC_FED_PAY_PLUGIN_VERSION', '1' );
define( 'BC_FED_PAY_PLUGIN_VERSION_TYPE', 'FREE' );

/**
 * App Name
 */
define( 'BC_FED_PAY_APP_NAME', 'Frontend Dashboard Payment' );

/**
 * DB Name
 */
define('BC_FED_PAY_PAYMENT_TABLE', 'fed_pay_payment');
define('BC_FED_PAY_PAYMENT_PLAN_TABLE', 'fed_pay_payment_plans');

/**
 * Root Path
 */
define( 'BC_FED_PAY_PLUGIN', __FILE__ );
/**
 * Plugin Base Name
 */
define( 'BC_FED_PAY_PLUGIN_BASENAME', plugin_basename( BC_FED_PAY_PLUGIN ) );
/**
 * Plugin Name
 */
define( 'BC_FED_PAY_PLUGIN_NAME', trim( dirname( BC_FED_PAY_PLUGIN_BASENAME ), '/' ) );
/**
 * Plugin Directory
 */
define( 'BC_FED_PAY_PLUGIN_DIR', untrailingslashit( dirname( BC_FED_PAY_PLUGIN ) ) );


require_once BC_FED_PAY_PLUGIN_DIR . '/fed_autoload.php';