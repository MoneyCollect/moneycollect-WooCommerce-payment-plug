<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Tng extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'tng';
    var $method_title = MONEYCOLLECT_NAME.' TNG';

    public function __construct()   {
        parent::__construct('moneycollect_tng');
    }



}
