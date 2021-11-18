<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_MC_Payment_Logger {
    const PAYMENT_LOG = 'money-collect-payment';
    const WEBHOOK_LOG = 'money-collect-webhook';
    const ERROR_LOG = 'money-collect-error';

    public static $logger;
    private $logging;
    private $log_entry = '';

    function __construct(){
        $setting = new WC_MC_Payment_Setting();
        $this->logging = $setting->get_setting('logging') === 'yes'?'yes':'no';
    }

    function payment( $message ){
        $this->log($message);
        self::add('info',self::PAYMENT_LOG);
    }

    function webhook( $message ){
        $this->log($message);
        self::add('info',self::WEBHOOK_LOG);
    }

    function error( $message ){
        $this->log($message);
        self::add('error',self::ERROR_LOG,true);
    }

    function log( $message ) {

        if ( ! class_exists( 'WC_Logger' ) ) {
            return '';
        }

        if ( empty( self::$logger ) ) {
            self::$logger = wc_get_logger();
        }

        if( is_array($message) ){
            $message = json_encode($message);
        }

        $log_entry = '==== Start Log : '.print_r($message,true) .' : End Log ===='."\n";

        $this->log_entry = $log_entry;
    }

    private function add($action,$source,$logging = false){
        if( $this->log_entry != '' && ( $logging == true || $this->logging === 'yes' ) ){
            self::$logger->$action( $this->log_entry, [ 'source' => $source ] );
        }
    }


}
