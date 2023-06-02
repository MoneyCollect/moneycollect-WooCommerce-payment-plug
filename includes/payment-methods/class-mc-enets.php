<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

class WC_Gateway_Mc_Enets extends WC_MC_Payment_Gateway
{

    var $id;
    var $payment_method = 'enets';
    var $method_title = MONEYCOLLECT_NAME.' eNETS';

    public function __construct()   {
        parent::__construct('moneycollect_enets');
    }



}
