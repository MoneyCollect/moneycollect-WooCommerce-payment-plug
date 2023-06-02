<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Gcash extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'gcash';
    var $method_title = MONEYCOLLECT_NAME.' GCash';

    public function __construct()   {
        parent::__construct('moneycollect_gcash');
    }



}
