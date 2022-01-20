<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_MC_Payment_Logger {
    const INFO_LOG = 'money-collect-info';
    const ERROR_LOG = 'money-collect-error';

    public static $logger;
    private $logging;
    private $log_entry = '';

    function __construct(){
        $setting = new WC_MC_Payment_Setting();
        $this->logging = $setting->get_setting('logging') === 'yes'?'yes':'no';
    }

    function info( $title, $message ){
        $this->log( $title, $message);
        self::add('info',self::INFO_LOG);
    }


    function error( $title, $message ){
        $this->log( $title, $message );
        self::add('error',self::ERROR_LOG,true);
    }

    function log( $title, $message ) {

        if ( ! class_exists( 'WC_Logger' ) ) {
            return '';
        }

        if ( empty( self::$logger ) ) {
            self::$logger = wc_get_logger();
        }

        if( is_array($message) ){
            $message = json_encode($message);
        }

        $log_entry = ' ['.$title.'] '.print_r($message,true) .' '."\n";

        $this->log_entry = $log_entry;
    }

    private function add($action,$source,$logging = false){
        if( $this->log_entry != '' && ( $logging == true || $this->logging === 'yes' ) ){
            self::$logger->$action( $this->log_entry, [ 'source' => $source ] );
        }
    }


}
