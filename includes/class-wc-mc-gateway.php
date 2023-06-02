<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class WC_MC_Payment_Gateway extends WC_Payment_Gateway_CC {

    var $id;
    var $method_description;
    var $payment_method;
    protected $logger;
    protected $setting;
    protected $customer;
    protected $fun;
    protected $order;
    protected $complete_status = ['processing','requires_capture','succeeded']; #用于判断支付是否完成

    function __construct($id){
        $this->id = esc_attr(strtolower($id));
        $this->method_description = sprintf( __( 'All other general Moneycollect settings can be adjusted <a href="%s">here</a>', 'moneycollect' ),admin_url( 'admin.php?page=wc-settings&tab=checkout&section=moneycollect') );

        $this->title = $this->get_option ( 'title' );
        $this->description = $this->get_option ( 'description' );

        $this->logger = new WC_MC_Payment_Logger();
        $this->setting = new WC_MC_Payment_Setting();
        $this->customer = new Wc_Mc_Payment_Customer(get_current_user_id());
        $this->fun = new WC_MC_Payment_Fun();
        $this->supports = [ 'products', 'refunds' ];

        // 保存设置
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array ($this,'process_admin_options') );

        if( is_checkout() || is_account_page() ){
            wp_enqueue_style( 'moneycollect_styles', MONEYCOLLECT_URL.'/assets/css/mc_style.css', [], MONEYCOLLECT_VERSION );
        }

        // 加载表单字段
        $this->init_form_fields ();
        // 加载设置
        $this->init_settings ();
    }

    function init_form_fields(){
        $this->form_fields = [
            'enabled' => [
                'title' => __ ( 'Enable/Disable', 'moneycollect' ),
                'type' => 'checkbox',
                'label' => __ ( 'Enable Payment', 'moneycollect' ),
                'default' => 'no'
            ],
            'title' =>[
                'title' => __ ( 'Title', 'moneycollect' ),
                'type' => 'text',
                'description' => __ ( 'This controls the title which the user sees during checkout.', 'moneycollect' ),
                'default' => trim( str_replace(MONEYCOLLECT_NAME,'', $this->method_title) ),
                'desc_tip' => true,
            ],
            'description' =>[
                'title' => __ ( 'Description', 'moneycollect' ),
                'type' => 'text',
                'description' => __ ( 'This controls the description which the user sees during checkout.', 'moneycollect' ),
                'desc_tip' => true,
            ],
        ];
    }

    public function admin_options() {
        wp_enqueue_style( 'mc_styles', MONEYCOLLECT_URL.'/assets/css/mc_admin.css', [], MONEYCOLLECT_VERSION );
        wp_enqueue_script('mc_admin', MONEYCOLLECT_URL.'/assets/js/mc_admin.js', ['jquery'], MONEYCOLLECT_VERSION, true);
        parent::admin_options();
    }

    public function get_icon(){

        if( $this->setting->get_setting('icon') === 'no' ){
            return '';
        }
        $img = '<img class="wc-mc-icon-card" src="'.MONEYCOLLECT_URL.'/assets/images/'.strtolower($this->payment_method).'.png" alt="'.$this->payment_method.'" />';
        return apply_filters( 'woocommerce_gateway_icon', $img, $this->id );
    }

    function payment_fields(){
        echo apply_filters( 'wc_'.$this->id.'_description', wpautop( wp_kses_post( $this->description ) ), $this->id );
    }

    function process_payment($order_id){

        $this->order = new WC_Order( $order_id );
        $data = array_merge($this->base_data(),$this->order_data());

        $this->logger->info('create session',$data);

        $result = WC_MC_Payment_Api::create_session($data);

        if( is_array($result) && isset($result['code']) ){

            $this->logger->info('create session result',$result);

            if( $result['code'] === 'success' ){
                return [
                    'result' => 'success',
                    'redirect' => $result['data']['url']
                ];
            }else{
                throw new Exception($result['msg']);
            }
        }else{
            $this->logger->error('create session', $result);
            throw new Exception(  __ ( 'There was an accident', 'moneycollect' ) );
        }

    }

    public function process_refund($order_id, $amount = null, $reason = '')
    {
        $order = new WC_Order( $order_id );

        if( $amount <= 0 )
            throw new Exception( __( 'This amount must be greater than 0' ) );

        if ( $reason ) {
            if ( strlen( $reason ) > 200 ) {
                $reason = function_exists( 'mb_substr' ) ? mb_substr( $reason, 0, 150 ) : substr( $reason, 0, 150 );
                // Add some explainer text indicating where to find the full refund reason.
                $reason = $reason . '... [See WooCommerce order page for full text.]';
            }
        }

        $id = $order->get_transaction_id();

        if( $order->get_meta('_moneycollect_charge_captured') == 'yes' ){

            $cancellationReason = empty($reason)? 'customer cancel': $reason;

            $result = WC_MC_Payment_Api::cancel_payment($id,$cancellationReason);

            $this->logger->info('cancel result',$result);

            if($result['code'] != 'success'){
                throw new Exception( __( $result['msg'] ) );
            }

            $order->update_status('cancelled',__( 'The authorization was voided and the order cancelled.', 'moneycollect' ));
            throw new Exception( __( 'The authorization was voided and the order cancelled. Click okay to continue, then refresh the page.', 'moneycollect' ) );

        }
        else{

            $refund_items = wc_get_orders( array(
                'type'   => 'shop_order_refund',
                'parent' => $order_id,
                'limit'  => -1,
                'return' => 'ids',
            ) );

            $refund_id = current($refund_items);

            $refund_data = [
                'amount' =>  $this->fun->transform_amount($amount,$order->get_currency()),
                'description' => $refund_id,
                'note' => $reason,
                'paymentId' => $id,
                'reason' => 'requested_by_customer'
            ];

            $this->logger->info('refund request',$refund_data);

            $result = WC_MC_Payment_Api::create_refund($refund_data);

            $this->logger->info('refund result',$result);

            if($result['code'] != 'success'){
                throw new Exception( __( $result['msg'] ) );
            }

            $note = sprintf(__('Refund %1$s via %2$s - Refund ID: %3$s - Reason: %4$s','moneycollect'),
                $order->get_currency().' '.$amount,
                $order->get_payment_method_title(),
                $result['data']['id'],
                $reason
            ) ;

            $order->add_order_note($note);

            return true;

        }
    }

    protected function order_data(){

        global $product;

        $order = $this->order;

        $currency = $order->get_currency();

        $order_data  = $order->get_items( 'line_item' ) ;

        $line_items = [];

        foreach ($order_data as $key => $item) {

            $data = array_values((array)$item);
            $product = wc_get_product( $data[1]['product_id'] );

            if( empty($product) ){
                continue;
            }

            $amount = $data['1']['quantity'] > 0 ? $this->fun->transform_amount( $data['1']['subtotal'] / $data['1']['quantity'],$currency ) : 0;
            $images = wp_get_attachment_url( $product->get_image_id() )?:'';

            $line_items[] = [
                'amount' => $amount,
                'currency' => $currency,
                'description' => $this->fun->substr_format( $product->get_description() ),
                'images' => [$images],
                'name' => $product->get_name(),
                'quantity' =>  $data['1']['quantity']
            ];
        }

        $data =  [
            'orderNo' =>  $order->get_id(),
            'currency' => $currency,
            'amountTotal' => $this->fun->transform_amount($order->get_total(), $currency),
            'billingDetails' => [
                'firstName' => $order->get_billing_first_name(),
                'lastName' => $order->get_billing_last_name(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'address' => [
                    'country' => $order->get_billing_country(),
                    'state'=> $order->get_billing_state(),
                    'city' => $order->get_billing_city(),
                    'line1' => $order->get_billing_address_1(),
                    'line2' => $order->get_billing_address_2(),
                    'postalCode' => $order->get_billing_postcode()
                ]
            ],
            'lineItems' => $line_items,
        ];

        if( $order->has_shipping_address() ){
            $data['shipping'] = [
                'firstName' => $order->get_shipping_first_name(),
                'lastName' => $order->get_shipping_last_name(),
                'phone' => $order->get_billing_phone(),
                'address' => [
                    'country' => $order->get_shipping_country(),
                    'state'=> $order->get_shipping_state(),
                    'city' => $order->get_shipping_city(),
                    'line1' => $order->get_shipping_address_1(),
                    'line2' => $order->get_shipping_address_2(),
                    'postalCode' => $order->get_shipping_postcode()
                ]
            ];
        }

        return $data;

    }

    protected function base_data(){

        $order = $this->order;

        if( $this->customer->has_customer() ){
            $customer = $this->customer->get_id();
            $customer_email = '';
        }else{
            $customer = '';
            $customer_email = $order->get_billing_email();
        }

        $data =  [
            'customer' => $customer,
            'customerEmail' => $customer_email,
            'cancelUrl' => wc_get_checkout_url(),
            'returnUrl' => $this->get_return_url($order),
            'notifyUrl' => $this->get_webhook_url(),
            'preAuth' => $this->get_option('pre_auth') === 'yes' ? 'y' : 'n',
            'statementDescriptor' => $this->setting->get_setting('statement_descriptor'),
            'website' => get_home_url(),
            'paymentMethodTypes' => [$this->payment_method]
        ];

        if( empty($data['statementDescriptor']) ){
            unset($data['statementDescriptor']);
        }

        return $data;
    }

    protected function display_admin_settings_webhook_description(){
        $description = sprintf( __( 'Tick Webhook Endpoints %s will enable you to receive notifications on the charge statuses. <br/>If you cannot receive notifications on the charge statuses，you need to disbale Webhook Endpoints and then browser will process the change of your statuses.', 'moneycollect' ), '<strong style="background-color:#ddd;">&nbsp;'.$this->get_webhook_url().'&nbsp;</strong>' );

        return $description;
    }

    protected function get_webhook_url(){
        return add_query_arg( 'wc-api', 'wc_mc_webhook', trailingslashit( get_home_url() ) );
    }

    protected function checkout_payment($data,$type){

        $note = '<b>' . __('Source','moneycollect') . '</b> : '. $type . "\r\n";
        $note .= '<b>' . __('Payment','moneycollect') . '</b> : '.MONEYCOLLECT_NAME."\r\n";
        if( isset($data['paymentMethodDetails']['type']) ){
            $note .= '<b>' . __('Type','moneycollect') . '</b> : '. $data['paymentMethodDetails']['type'];
        }
        if( $data['paymentMethodDetails']['type'] === 'card' ){
            $note .= '('. $data['paymentMethodDetails']['card']['brand'] .')';
        }
        $note .= "\r\n";
        $note .= '<b>' . __('Transaction','moneycollect') . '</b> : '.$data['id'] ."\r\n";
        $note .= '<b>' . __('Status','moneycollect') . '</b> : '.$data['status'] ."\r\n";


        if( $data['errorMessage'] ){
            $note .= '<b>' . __('Message','moneycollect') . '</b> : '.$data['errorMessage'] ."\r\n";
        }

        $new_status = $this->fun->get_status_update($data['status']);

        if( empty($new_status) ){
            $this->logger->error('get new status is null',$data['status']);
            return false;
        }

        $captured = ($this->setting->get_setting('pre_auth') === 'yes' && $data['status'] == 'requires_capture' ) ? 'yes' : 'no';
        $this->order->update_meta_data('_moneycollect_charge_captured',$captured);

        if( $new_status === 'processing' ){
            $this->order->payment_complete($data['id']);
        }
        elseif($new_status === 'on-hold'){
            $this->order->set_transaction_id( $data['id'] );
            $this->order->update_status($new_status);
        }
        else{
            $this->order->update_status($new_status);
        }

        $this->order->add_order_note($note);
        return $new_status;
    }

}
