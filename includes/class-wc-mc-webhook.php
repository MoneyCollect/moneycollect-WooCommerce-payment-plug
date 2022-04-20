<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wc_Mc_Payment_Webhook extends WC_MC_Payment_Gateway
{
    function __construct(){
        parent::__construct('moneycollect');
        add_action( 'woocommerce_api_wc_mc_webhook', [ $this, 'check_for_webhook' ] );
    }

    public function check_for_webhook(){

        if ( ! isset( $_SERVER['REQUEST_METHOD'] )
            || ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
            || ! isset( $_GET['wc-api'] )
            || ( $_GET['wc-api'] !== 'wc_mc_webhook')
        ) {
            return;
        }

        try{

            if( $this->setting->get_setting('webhook') !== 'yes' ){
                echo 'success';
                exit();
            }

            $request_body = file_get_contents( 'php://input' );

            $this->logger->info('webhook data',$request_body);

            $result = json_decode($request_body,true);

            if( isset($result['type']) && strpos($result['type'],'payment') !== false ){

                $data = $result['data'];

                $order_id = $data['orderNo'];

                $this->order = wc_get_order( $order_id );

                if( !is_object($this->order) ){
                    throw new Exception('order no "'.$data['orderNo'].'" is not existent');
                }

                if( $this->order->has_status( [ 'processing', 'completed', 'refunded' ]) ){
                    throw new Exception('"'.$data['orderNo'].'" order status is '.$this->order->get_status());
                }

                $payment_id = wc_clean( wp_unslash( $data['id'] ) );

                $result = WC_MC_Payment_Api::get_payment($payment_id);

                if( $result['code'] === 'success' ){

                    $data = $result['data'];

                    $this->logger->info('webhook get payment result ',$data);

                    $rs = $this->checkout_payment($data,'Webhook');

                    $this->logger->info('checkout payment webhook',$rs);

                    if( $rs !== true ){
                        throw new Exception('Unknown order status');
                    }
                }else{
                    throw new Exception('Unknown Payment id '.$payment_id);
                }

            }

            echo 'success';

        }catch (\Exception $e){
            $mes = $e->getMessage();
            $this->logger->error('webhook error', $mes);
            echo 'success';
        }

        exit();

    }

}
new Wc_Mc_Payment_Webhook();
