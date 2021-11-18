<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wc_Mc_Payment_Customer
{
    /** MC customer ID */
    private $id = '';
    /** WP User ID */
    private $user_id = 0;
    private static $mate_key = '_moneycollect_id';

    public function __construct( $user_id = 0 ) {
        if ( $user_id ) {
            $this->user_id = absint($user_id);
            $this->set_id( $this->get_id_from_meta( $user_id ) );
        }
    }

    public function get_id(){
        return $this->id;
    }

    public function set_id( $id ) {
        // Backwards compat for customer ID stored in array format. (Pre 3.0)
        if ( is_array( $id ) && isset( $id['customer_id'] ) ) {
            $id = $id['customer_id'];
            $this->update_id_in_meta( $id );
        }
        $this->id = wc_clean( $id );
    }

    public function has_customer()
    {
        if( $this->id == '' ){
            return false;
        }else{
            return true;
        }
    }

    public function create_customer( $id ) {
        $this->set_id(['customer_id' => $id]);
    }

    public function delete_customer(){
        delete_user_option($this->user_id,self::$mate_key,false);
        $this->id = '';
    }

    public function get_id_from_meta( $user_id ) {

        $cur_id = get_user_option( self::$mate_key, $user_id );
        if( $cur_id ){
            $rs = WC_MC_Payment_Api::retrieves_customer($cur_id);
            if( $rs['code'] !== 'success' ){
                $cur_id  = false;
                delete_user_option($this->user_id, self::$mate_key, false);
            }
        }
        return $cur_id;
    }

    public function update_id_in_meta( $id ) {
        update_user_option( $this->user_id, self::$mate_key, $id, false );
    }

    /*
     * payment methods
     */

    public function get_payment_methods($gateway_id){

        $methods = get_transient( $gateway_id . '_' . $this->get_id() );

        if( empty($methods) ) $methods = false;

        if( $this->get_id() === ''  ) $this->clear_cache($gateway_id);


        if( $this->get_id() !== '' && $methods === false ){

            $result = Wc_Mc_Payment_Api::get_payment_methods($this->get_id());

            if( isset($result['code']) && $result['code'] === 'success' ){
                $methods = $result['data'];
            }
            set_transient( $gateway_id . '_' . $this->get_id(), $methods, DAY_IN_SECONDS );
        }

        return $methods;
    }

    public function update_payment_method($payment_method_id,$data){
        $result = WC_MC_Payment_Api::get_payment_methods('',$payment_method_id);
        if( $result['code'] === 'success' ){
            if( !($data == $result['data']['billingDetails']) ){
                WC_MC_Payment_Api::upd_payment_method($payment_method_id,$data);
            }
        }

    }

    public function delete_payment_method($payment_method_id){
        try{
            $result = WC_MC_Payment_Api::del_payment_method($payment_method_id);
            if( $result['code'] === 'success' ){
                $this->clear_cache();
            }else{
                throw new Exception($result['msg']);
            }
        }catch (\Exception $e){
            wc_add_notice( $e->getMessage(), 'error' );
        }
    }

    public function clear_cache($gateway_id = 'moneycollect'){
        delete_transient( $gateway_id . '_' . $this->get_id() );
    }



}