<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Alipay extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'Alipay';
    var $method_title = MONEYCOLLECT_NAME.' Alipay';

    public function __construct()   {
        parent::__construct('moneycollect_alipay');
    }



}
