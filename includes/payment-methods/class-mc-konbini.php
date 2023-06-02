<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Konbini extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'konbini';
    var $method_title = MONEYCOLLECT_NAME.' Konbini';

    public function __construct()   {
        parent::__construct('moneycollect_konbini');
    }



}
