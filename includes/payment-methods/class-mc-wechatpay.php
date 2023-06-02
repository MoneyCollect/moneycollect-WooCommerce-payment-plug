<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Wechatpay extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'wechatpay';
    var $method_title = MONEYCOLLECT_NAME.' Wechat Pay';

    public function __construct()   {
        parent::__construct('moneycollect_wechatpay');
    }



}
