<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Boleto extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'boleto';
    var $method_title = MONEYCOLLECT_NAME.' Boleto Bancário';

    public function __construct()   {
        parent::__construct('moneycollect_boleto');
    }



}
