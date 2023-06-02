<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Payeasy extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'pay_easy';
    var $method_title = MONEYCOLLECT_NAME.' Pay-easy';

    public function __construct()   {
        parent::__construct('moneycollect_payeasy');
    }



}
