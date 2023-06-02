
jQuery( function( $ ) {
    'use strict';

    try {
        var mc_sdk = window.MoneycollectPay(mc_checkout_params.apiKey);
    }catch( error ) {
        console.log( error );
        return;
    }


    var wc_moneycollect_form = {

        errMsg: '',
        orderPayPage: mc_checkout_params.orderPayPage === '1',
        form: null,

        onSubmit: function () {

            if( $('#payment_method_moneycollect').is( ':checked' ) ){

                wc_moneycollect_form.reset();

                let token = $('input[name=wc-moneycollect-payment-token]:checked').val();

                if( token != undefined && token!== 'new' ){
                    // use token
                    return true
                }
                else{

                    if( wc_moneycollect_form.orderPayPage ){
                        var paymentMethodObj = {
                            "billingDetails": mc_checkout_params.billing
                        }
                    }else {
                        let customerObj = {
                            description: '',
                            email: $( '#billing_email' ).val(),
                            firstName: $( '#billing_first_name' ).val(),
                            lastName: $( '#billing_last_name' ).val(),
                            phone: $( '#billing_phone' ).val()
                        };
                        var paymentMethodObj = {
                            "billingDetails": {
                                "address": {
                                    "city": $( '#billing_city' ).val(),
                                    "country": $( '#billing_country' ).val(),
                                    "line1": $( '#billing_address_1' ).val(),
                                    "line2": $( '#billing_address_2' ).val(),
                                    "postalCode": $( '#billing_postcode' ).val(),
                                    "state": $( '#billing_state' ).val()
                                },
                                "email": customerObj.email,
                                "firstName": customerObj.firstName,
                                "lastName": customerObj.lastName ,
                                "phone": customerObj.phone
                            },
                        };
                    }

                    try {
                        mc_sdk.confirmPaymentMethod({
                            paymentMethod: paymentMethodObj
                        }).then((result) => {
                            if( result.data.code === "success" ){
                                wc_moneycollect_form.form.append(
                                    $( '<input type="hidden" />' )
                                        .addClass( 'mc_add_field' )
                                        .attr( 'name', 'mc_payment_method_id' )
                                        .val( result.data.data.id )
                                );
                                wc_moneycollect_form.form.trigger( 'submit' );
                            }else {
                                wc_moneycollect_form.errMsg = result.data.msg;
                                wc_moneycollect_form.onError();
                            }

                        });

                    }catch (error) {
                        console.log(error);
                        wc_moneycollect_form.errMsg = 'error';
                        wc_moneycollect_form.onError();
                    }
                }

                return false;
            }
        },
        onError: function () {
            if( this.errMsg.trim() !== '' ){
                $('#moneycollect-card-error').html('<div class="woocommerce-error " role="alert">'+ this.errMsg +'</div>');
            }
            this.errMsg = '';
        },
        reset: function () {
            this.errMsg = '';
            $('input[name=mc_payment_method_id]').remove();
            $('#moneycollect-card-error').empty();
        },
        checkoutMcPlace: function () {
            $('#place_order').on('click',this.onSubmit );
        },
        createElements: function () {

            mc_sdk.elementInit("payment_steps",{
                formWrapperId: 'moneycollect-card-element',
                formId: 'moneycollect-card', // 页面表单id
                frameId: 'moneycollect-card-frame', // 生成的IframeId
                mode: mc_checkout_params.mode,
                customerId: '',
                autoValidate:false,
                lang: mc_checkout_params.lang,
                layout: mc_checkout_params.layout
            }).catch((err) => {
                console.log( err)
            });
            wc_moneycollect_form.checkoutMcPlace();

        },
        init: function () {
            // checkout page
            if( wc_moneycollect_form.orderPayPage ){
                this.form = $( '#order_review' );
            }else {
                this.form = $( 'form.woocommerce-checkout' );
            }

            if( this.form === undefined || this.length === 0){
                wc_moneycollect_form.errMsg = 'Cannot find form element';
                wc_moneycollect_form.onError();
                return;
            }

            this.form.on('change', this.reset);
            this.errMsg = '';

            if( this.orderPayPage ){
                wc_moneycollect_form.createElements();
            }else {
                $( document.body ).on( 'updated_checkout', function() {
                    wc_moneycollect_form.createElements();
                });
            }

        },

    };

    wc_moneycollect_form.init();

    window.addEventListener("getErrorMessage", e => {
        wc_moneycollect_form.errMsg = e.detail.errorMessage;
        wc_moneycollect_form.onError();
    });

});
