<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_ extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'poli';
    var $method_title = MONEYCOLLECT_NAME.' POLi';

    public function __construct()   {
        parent::__construct('moneycollect_poli');
    }



}
