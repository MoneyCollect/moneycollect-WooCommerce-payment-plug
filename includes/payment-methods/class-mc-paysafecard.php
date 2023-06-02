<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Paysafecard extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'paysafecard';
    var $method_title = MONEYCOLLECT_NAME.' Paysafecard';

    public function __construct()   {
        parent::__construct('moneycollect_paysafecard');
    }



}
