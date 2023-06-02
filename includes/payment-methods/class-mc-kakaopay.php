<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Kakaopay extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'kakao_pay';
    var $method_title = MONEYCOLLECT_NAME.' Kakao Pay';

    public function __construct()   {
        parent::__construct('moneycollect_kakaopay');
    }



}
