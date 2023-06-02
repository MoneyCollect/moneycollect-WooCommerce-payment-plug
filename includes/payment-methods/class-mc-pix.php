<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Pix extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'pix';
    var $method_title = MONEYCOLLECT_NAME.' PIX';

    public function __construct()   {
        parent::__construct('moneycollect_pix');
    }



}
