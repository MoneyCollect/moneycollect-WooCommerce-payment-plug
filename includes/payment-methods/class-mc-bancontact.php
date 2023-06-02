<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Bancontact extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'bancontact';
    var $method_title = MONEYCOLLECT_NAME.' Bancontact';

    public function __construct()   {
        parent::__construct('moneycollect_bancontact');
    }



}
