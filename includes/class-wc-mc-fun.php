<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_MC_Payment_Fun  {

    function substr_format($text, $length = 195, $replace = '...', $encoding = 'UTF-8'){
        if( $text && mb_strlen($text,$encoding) >= $length ){
            return mb_substr($text,0,$length,$encoding).$replace;
        }
        return $text;
    }

    function transform_amount($amount,$currency){
        switch ($currency){
            case strpos('CLP,ISK,VND,KRW,JPY',$currency) !== false:
                return (int)$amount;
                break;
            case strpos('IQD,KWD,TND',$currency) !== false:
                return (int)($amount*1000);
                break;
            default:
                return (int)($amount*100);
                break;
        }
    }

    function analysis_url($url,$key = 'basename'){
        if( !$url ){
            return $url;
        }
        $data = pathinfo($url);
        $data[$key];
    }

    function get_status_update($status){
        switch ( $status ){
            case 'succeeded':
                $new_status = 'processing';
                break;
            case 'failed':
                $new_status = 'failed';
                break;
            case 'requires_payment_method':
            case 'requires_confirmation':
            case 'requires_action':
            case 'processing':
                $new_status = 'on-hold';
                break;
            default:
                $new_status = '';
        }
        return $new_status;
    }

    function get_ip(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if(!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = '';
        }

        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = isset($cips[0]) ? $cips[0] : 'unknown';

        unset($cips);
        return $cip;
    }


}