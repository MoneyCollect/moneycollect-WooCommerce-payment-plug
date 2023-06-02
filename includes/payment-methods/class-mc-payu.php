<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Payu extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'payu';
    var $method_title = MONEYCOLLECT_NAME.' PayU';

    public function __construct()   {
        parent::__construct('moneycollect_payu');
    }



}
