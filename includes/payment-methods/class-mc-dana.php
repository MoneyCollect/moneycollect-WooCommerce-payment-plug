<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Dana extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'dana';
    var $method_title = MONEYCOLLECT_NAME.' DANA';

    public function __construct()   {
        parent::__construct('moneycollect_dana');
    }



}
