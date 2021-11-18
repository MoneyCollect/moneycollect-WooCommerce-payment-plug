<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class WC_MC_Payment_Gateway extends WC_Payment_Gateway_CC {

    var $id;
    var $method_description;
    protected $logger;
    protected $setting;
    protected $customer;
    protected $fun;
    protected $order;
    protected $complete_status = ['succeeded','successful','pending','uncaptured'];

    function __construct($id){
        $this->id = esc_attr(strtolower($id));
        $this->title = $this->get_option ( 'title' );
        $this->description = $this->get_option ( 'description' );

        $this->logger = new WC_MC_Payment_Logger();
        $this->setting = new WC_MC_Payment_Setting();
        $this->customer = new Wc_Mc_Payment_Customer(get_current_user_id());
        $this->fun = new WC_MC_Payment_Fun();

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
                'default' => 'Credit Card',
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

    function payment_fields(){
        echo apply_filters( 'wc_'.$this->id.'_description', wpautop( wp_kses_post( $this->description ) ), $this->id );
    }

    function process_payment($order_id){

        $this->order = new WC_Order( $order_id );
        $data = array_merge($this->base_data(),$this->order_data());

        $result = WC_MC_Payment_Api::create_session($data);

        if( is_array($result) && isset($result['code']) ){

            $this->logger->payment($result);

            if( $result['code'] === 'success' ){
                return [
                    'result' => 'success',
                    'redirect' => $result['data']['url']
                ];
            }else{
                throw new Exception($result['msg']);
            }
        }else{
            $this->logger->error($result);
            throw new Exception(  __ ( 'There was an accident', 'moneycollect' ) );
        }

    }

    protected function order_data(){

        global $product;

        $order = $this->order;

        $currency = $order->get_currency();

        $order_data  = $order->get_items( [ 'line_item', 'fee' ] ) ;

        $line_items = [];

        foreach ($order_data as $key => $item) {

            $data = array_values((array)$item);
            $product = wc_get_product( $data[1]['product_id'] );

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

        return [
            'customer' => $customer,
            'customerEmail' => $customer_email,
            'cancelUrl' => wc_get_checkout_url(),
            'returnUrl' => $this->get_return_url($order),
            'notifyUrl' => $this->get_webhook_url(),
            'preAuth' => $this->get_option('pre_auth') === 'yes' ? 'y' : 'n',
            'statementDescriptor' => $this->setting->get_setting('statement_descriptor')?:$this->fun->analysis_url(get_home_url()),
            'website' => get_home_url()
        ];
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
        $note .= '<b>' . __('Type','moneycollect') . '</b> : '. $data['paymentMethodDetails']['type'];
        if( $data['paymentMethodDetails']['type'] === 'card' ){
            $note .= '('. $data['paymentMethodDetails']['card']['brand'] .')';
        }
        $note .= "\r\n";

        $note .= '<b>' . __('Transaction','moneycollect') . '</b> : '.$data['id'] ."\r\n";
        $note .= '<b>' . __('Status','moneycollect') . '</b> : '.$data['status'] ."\r\n";

        if( $data['errorMessage'] ){
            $note .= '<b>' . __('Message','moneycollect') . '</b> : '.$data['errorMessage'] ."\r\n";
        }

        $this->order->add_order_note($note);

        $new_status = $this->fun->get_status_update($data['status']);

        if( empty($new_status) ){
            $this->logger->error('get now status is null :'.$data['status']);
            return false;
        }

        $this->order->update_status($new_status);

        if( $new_status === 'processing' ){
            $this->order->payment_complete($data['id']);
        }

        return true;

    }

}