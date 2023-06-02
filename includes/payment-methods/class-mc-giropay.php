<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Giropay extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'giropay';
    var $method_title = MONEYCOLLECT_NAME.' Giropay';

    public function __construct()   {
        parent::__construct('moneycollect_giropay');
    }



}
