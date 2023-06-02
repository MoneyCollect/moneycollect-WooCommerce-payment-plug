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

            if( isset($result['type'])  ){

                $data = $result['data'];

                if( strpos($result['type'],'payment') !== false ){

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

                        if( $rs === false ){
                            throw new Exception('Unknown order status');
                        }
                    }else{
                        throw new Exception('Unknown Payment id '.$payment_id);
                    }
                }

                if( $result['type'] == 'refund.succeeded' ){

                    $refund_id = $data['description'];

                    if( empty($refund_id) || !wc_get_order($refund_id) ){

                        global $wpdb;

                        $query = $wpdb->prepare("SELECT post_id From {$wpdb->prefix}postmeta WHERE meta_key = '_transaction_id' and meta_value = '{$data['paymentId']}'" );

                        if ( $result = $wpdb->get_results($query) ) {
                            $post_id = $result[0]->post_id;
                            $order = wc_get_order($post_id);
                            if( $order && !$order->has_status('refunded') ){
                                $refund_amount = $this->fun->reduction_amount($data['amount'],$data['currency']);

                                $refund = wc_create_refund(array(
                                    'amount' => $refund_amount,
                                    'reason' => $data['reason'],
                                    'order_id' => $order->get_id(),
                                    'refund_payment' => false
                                ));

                                if (is_wp_error($refund)) {
                                    $error_message = $refund->get_error_message();
                                    $this->logger->error('webhook refund error',$error_message);
                                } else {
                                    $order->add_order_note(sprintf(__('Refund received from MoneyCollect, Refund Amount: %1$s - Refund ID: %2$s '),($data['currency'].$refund_amount),$data['id']));
                                }
                            }
                        }
                    }

                }

                if( $result['type'] == 'refund.failed' ){
                    $refund_id = $data['description'];
                    $refund = wc_get_order($refund_id);
                    if( $refund ){
                        $order_id = $refund->get_parent_id();
                        $refund->delete( true );
                        $order = wc_get_order($order_id);
                        $order->add_order_note(sprintf(__('%1$s Refund failed - failureReason: %1$s'),$data['id'],$data['failureReason']));
                        do_action( 'woocommerce_refund_deleted', $refund_id, $order_id );
                    }
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
