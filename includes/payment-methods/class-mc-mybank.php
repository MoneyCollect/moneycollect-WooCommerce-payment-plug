<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Mybank extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'myBank';
    var $method_title = MONEYCOLLECT_NAME.' MyBank';

    public function __construct()   {
        parent::__construct('moneycollect_mybank');
    }



}
