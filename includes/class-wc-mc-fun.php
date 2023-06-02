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
                return $amount;
            case strpos('IQD,KWD,TND',$currency) !== false:
                return (int)($amount*1000);
            default:
                return (int)($amount*100);
        }
    }

    function reduction_amount($amount,$currency){
        switch ($currency){
            case strpos('CLP,ISK,VND,KRW,JPY',$currency) !== false:
                return $amount;
            case strpos('IQD,KWD,TND',$currency) !== false:
                return round(($amount/1000),4);
            default:
                return round(($amount/100),2);
        }
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
            case 'requires_capture':
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

    function to_locale($wc_locale){

        switch($wc_locale){
            case 'ko_KR':
                return 'ko';
            case 'zh_TW':
            case 'zh_HK':
                return 'ch_TW';
            case 'pt_BR':
                return 'pt_Br';
            case 'pt_PT':
                return 'pt_pt';
            case 'ru_RU':
                return 'ru';
            case 'ja':
                return $wc_locale;
        }

        $base_locale = substr( $wc_locale, 0, 2 );
        switch($base_locale){
            case 'es':
            case 'de':
            case 'fr':
            case 'it':
                return 'pt_'.$base_locale;
        }

        return 'en';

    }
}