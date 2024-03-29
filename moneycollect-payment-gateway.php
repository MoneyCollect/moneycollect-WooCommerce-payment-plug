<?php
/**
 * Plugin Name: Moneycollect Payment Gateway for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/monetcollect-payments-gateway/
 * Description: Moneycollect Payment
 * Version: 1.2.00
 * Tested up to: 5.8
 * Required PHP version: 7.0
 * Author: MoneyCollect
 * Author URI:
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.zh-cn.html
 */


if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

const MONEYCOLLECT_NAME = 'Moneycollect';
const MONEYCOLLECT_VERSION = '1.2.00';
const MONEYCOLLECT_METHOD = [
    'alipay',
    'alipayhk',
    'bancontact',
    'boleto',
    'creditcard',
    'dana',
    'enets',
    'eps',
    'fpx',
    'gcash',
    'giropay',
    'ideal',
    'kakaopay',
    'klarna',
    'konbini',
    'mybank',
    'payeasy',
    'paysafecard',
    'payu',
    'pix',
    'poli',
    'przelewy24',
    'sofort',
    'tng',
    'truemoney',
    'wechatpay',
];
define('MONEYCOLLECT_DIR',rtrim(plugin_dir_path(__FILE__),'/'));
define('MONEYCOLLECT_URL',rtrim(plugin_dir_url(__FILE__),'/'));

add_action( 'init', 'moneycollect_init' );
function moneycollect_init(){
    load_plugin_textdomain( 'moneycollect', false,   MONEYCOLLECT_DIR . '/languages/'  );
    foreach (MONEYCOLLECT_METHOD as $key => $value){
        require_once(MONEYCOLLECT_DIR.'/includes/payment-methods/class-mc-'.$value.'.php');
    }
}

add_action( 'plugins_loaded', 'moneycollect_loaded' );
function moneycollect_loaded(){
    require_once (MONEYCOLLECT_DIR.'/includes/class-wc-mc-gateway.php');
    require_once (MONEYCOLLECT_DIR.'/includes/class-wc-mc-setting.php');
    require_once (MONEYCOLLECT_DIR.'/includes/class-wc-mc-api.php');
    require_once (MONEYCOLLECT_DIR.'/includes/class-wc-mc-fun.php');
    require_once (MONEYCOLLECT_DIR.'/includes/class-wc-mc-logger.php');
    require_once (MONEYCOLLECT_DIR.'/includes/class-wc-mc-customer.php');
    require_once (MONEYCOLLECT_DIR.'/includes/class-wc-mc-return.php');
    require_once (MONEYCOLLECT_DIR.'/includes/class-wc-mc-webhook.php');
    require_once (MONEYCOLLECT_DIR.'/includes/class-wc-mc-token.php');
}

add_filter('woocommerce_payment_gateways','moneycollect_add_gateway',10,1);
function moneycollect_add_gateway($methods){
    foreach (MONEYCOLLECT_METHOD as $key => $value){
        $val = ucfirst($value);
        $methods[] = 'WC_Gateway_Mc_'.$val;
    }
    return $methods;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'moneycollect_plugin_edit_link' );
function moneycollect_plugin_edit_link( $links ){
    return array_merge(
        array(
            'settings' => '<a href="'.admin_url( 'admin.php?page=wc-settings&tab=checkout&section=moneycollect').'">'.__( 'Settings', 'moneycollect' ).'</a>'
        ),
        $links
    );
}

add_action( 'woocommerce_thankyou', 'moneycollect_thankyou_page'  );

function moneycollect_thankyou_page( $order_id ) {
    $order = wc_get_order( $order_id );
    if( $order->get_payment_method() === 'moneycollect' ){
        wc_print_notices();
    }
}
