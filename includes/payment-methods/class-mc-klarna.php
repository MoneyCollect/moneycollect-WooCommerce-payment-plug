<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Klarna extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'klarna';
    var $method_title = MONEYCOLLECT_NAME.' Klarna';

    public function __construct()   {
        parent::__construct('moneycollect_klarna');
    }



}
