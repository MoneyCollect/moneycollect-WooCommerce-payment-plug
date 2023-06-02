<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Sofort extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'sofort';
    var $method_title = MONEYCOLLECT_NAME.' Sofort';

    public function __construct()   {
        parent::__construct('moneycollect_sofort');
    }



}
