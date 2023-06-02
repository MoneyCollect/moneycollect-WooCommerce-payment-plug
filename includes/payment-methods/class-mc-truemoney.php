<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Truemoney extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'truemoney';
    var $method_title = MONEYCOLLECT_NAME.' TrueMoney';

    public function __construct()   {
        parent::__construct('moneycollect_truemoney');
    }



}
