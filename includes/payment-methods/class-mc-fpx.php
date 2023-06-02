<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Fpx extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'fpx';
    var $method_title = MONEYCOLLECT_NAME.' FPX';

    public function __construct()   {
        parent::__construct('moneycollect_fpx');
    }



}
