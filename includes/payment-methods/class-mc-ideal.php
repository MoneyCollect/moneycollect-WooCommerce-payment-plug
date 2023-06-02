<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Ideal extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'ideal';
    var $method_title = MONEYCOLLECT_NAME.' Ideal';

    public function __construct()   {
        parent::__construct('moneycollect_ideal');
    }



}
