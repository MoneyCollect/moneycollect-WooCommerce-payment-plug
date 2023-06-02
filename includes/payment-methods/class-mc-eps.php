<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Eps extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'eps';
    var $method_title = MONEYCOLLECT_NAME.' EPS';

    public function __construct()   {
        parent::__construct('moneycollect_eps');
    }



}
