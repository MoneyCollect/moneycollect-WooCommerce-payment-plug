<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_MC_Payment_Setting  {

    protected $setting;

    public function __construct()
    {
        $this->setting = get_option( 'woocommerce_moneycollect_settings');
    }

    public function get_setting( $key = '' ){
        if( $this->setting === false ){
            return null;
        }
        if( !empty($key) && key_exists($key,$this->setting) ){
            return $this->setting[$key];
        }
        return null;
    }

    public function get_pr_key(){
        if( $this->get_setting('test_model') === 'yes' ){
            return 'Bearer ' . $this->get_setting('test_secret_key');
        }
        return 'Bearer ' . $this->get_setting('secret_key');
    }

    public function get_pu_key(){
        if( $this->get_setting('test_model') === 'yes' ){
            return $this->get_setting('test_publishable_key');
        }
        return $this->get_setting('publishable_key');
    }
}