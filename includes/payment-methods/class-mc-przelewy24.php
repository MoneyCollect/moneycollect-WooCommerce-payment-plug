<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Przelewy24 extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'przelewy24';
    var $method_title = MONEYCOLLECT_NAME.' Przelewy24';

    public function __construct()   {
        parent::__construct('moneycollect_przelewy24');
    }



}
