<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Alipayhk extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'alipay_hk';
    var $method_title = MONEYCOLLECT_NAME.' Alipay HK';

    public function __construct()   {
        parent::__construct('moneycollect_alipay_hk');
    }



}
