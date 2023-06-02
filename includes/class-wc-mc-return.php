<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Mc_Payment_Return extends WC_MC_Payment_Gateway
{

    function __construct(){
        parent::__construct('moneycollect');
        add_action( 'wp', [ $this, 'check_process_order' ] );
    }


    function check_process_order(){

        if ( !is_order_received_page() || empty($_GET['payment_id']) ){
            return;
        }

        try{

            $payment_id = wc_clean( wp_unslash( $_GET['payment_id'] ) );

            $result = WC_MC_Payment_Api::get_payment($payment_id);

            if( $result['code'] === 'success' ){

                $data = $result['data'];

                $this->logger->info('get payment result',$data);

                $order_id = $data['orderNo'];

                $this->order = wc_get_order( $order_id );

                if( !is_object($this->order) ){
                    return;
                }

                if( is_user_logged_in() && !$this->customer->has_customer() && isset($data['customerId']) ){
                    $this->logger->info('create customer',$data['customerId']);
                    $this->customer->create_customer($data['customerId']);
                }

                if ( $this->order->has_status( [ 'processing', 'completed', 'on-hold' ] ) ) {
                    $this->logger->info('return order status',$this->order->get_status());
                    wc_add_notice($data['displayStatus'],'success');
                    return;
                }

                if( $this->setting->get_setting('webhook') === 'no' ){
                    $rs = $this->checkout_payment($data,'Sync');
                    $this->logger->info('checkout payment sync',$rs);
                }

                if( in_array($data['status'] , $this->complete_status) ){
                    global $woocommerce;
                    wc_add_notice($data['displayStatus'],'success');
                    $woocommerce->cart->empty_cart();
                    return;
                }else{
                    if( $data['errorMessage'] ){
                        throw new Exception($data['displayStatus'].': '.$data['errorMessage']);
                    }else{
                        throw new Exception($data['displayStatus']);
                    }
                }

            }
            else{
                $this->logger->error('get payment', $result['msg']);
                throw new Exception($result['msg']);
            }

        }catch (\Exception $e){

            $message = $e->getMessage();
            $this->logger->error('return error',$message);
            wc_add_notice( $message, 'error' );
            wp_safe_redirect( $this->order->get_checkout_payment_url() );
            exit();
        }


    }

}

new WC_Mc_Payment_Return();
