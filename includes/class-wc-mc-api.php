<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_MC_Payment_Api  {

    const ENV_PRO = 'https://api.moneycollect.com/api/services/v1';
    const ENV_TEST = 'https://sandbox.moneycollect.com/api/services/v1';
    const JSSDK = 'https://static.moneycollect.com/jssdk/js/MoneyCollect.js';

    static $header = [
        "Content-type" => "application/json",
    ];

    public static function create_session ($data) {
        $api = self::get_env().'/checkout/session/create';
        //self::add_header('Idempotency-Key',$data['orderNo']);
        return self::request($data,$api);
    }

    public static function create_customer ($data) {
        $api = self::get_env().'/customers/create';
        return self::request($data,$api);
    }

    public static function retrieves_customer($id){
        $api = self::get_env().'/customers/'.$id;
        return self::request([],$api,'GET');
    }

    public static function create_payment ($data) {
        $api = self::get_env().'/payment/create';
        return self::request($data,$api);
    }

    public static function get_payment ($id = '') {
        $api = self::get_env().'/payment/'.$id;
        return self::request([],$api,'GET');
    }

    public static function up_payment($id,$data){
        $api = self::get_env().'/payment_methods/'.$id.'/update';
        return self::request($data,$api);
    }

    public static function get_payment_methods ($customer_id,$payment_method_id = '') {
        if( $payment_method_id === '' ){
            $api = self::get_env().'/payment_methods/list/'.$customer_id;
        }else{
            $api = self::get_env().'/payment_methods/'.$payment_method_id;
        }
        return self::request([],$api,'GET');
    }

    public static function upd_payment_method ($payment_method_id,$data) {
        $api = self::get_env().'/payment_methods/'.$payment_method_id.'/update';
        return self::request($data,$api);
    }

    public static function del_payment_method ($payment_id) {
        $api = self::get_env().'/payment_methods/'.$payment_id.'/detach';
        return self::request([],$api,'POST');
    }

    protected function add_pr_key(){
        $setting = new WC_MC_Payment_Setting();
        self::add_header('Authorization',$setting->get_pr_key());
    }

    protected function get_env(){

        $setting = new WC_MC_Payment_Setting();
        if( $setting->get_setting('test_model') === 'yes' ){
            return self::ENV_TEST;
        }else{
            return self::ENV_PRO;
        }

    }

    protected function add_header($key,$val){
        self::$header[$key] = $val;
    }

    protected function request ($data,$api,$method = 'POST') {

        self::add_pr_key();

        $result = wp_remote_head( $api, array(
            'method' => $method, // Request method. Accepts 'GET', 'POST', 'DELETE'
            'timeout' => '60', // How long the connection should stay open in seconds.
            //'blocking' => false,
            'httpversion' => '1.1',
            'sslverify' => true,
            'headers' => self::$header,
            'body' => json_encode($data) ) );

        if( is_object($result) && property_exists($result,'errors') ){
            return $result->errors['http_request_failed'][0];
        }


        if( isset($result['body']) && !empty($result['body']) ){
            //请求成功
            return json_decode( $result['body'],true);
        }else if ( isset($result['response']['code']) ){
            return $result['response']['code'].' '.$result['response']['message'];
        }else {
            return false;
        }

    }

}