
jQuery( function( $ ) {
    'use strict';

    try {
        var mc_sdk = window.MoneycollectPay(mc_checkout_params.apiKey);
    }catch( error ) {
        console.log( error );
        return;
    }

    var wc_moneycollect_form = {
        onSubmit: function () {

            if( $('#payment_method_moneycollect').is( ':checked' ) ){

                wc_moneycollect_form.reset();

                let token = $('input[name=wc-moneycollect-payment-token]:checked').val();

                if( token != undefined && token!== 'new' ){
                    // use token
                    return true
                }else{

                    let customerObj = {
                        description: '',
                        email: $( '#billing_email' ).val(),
                        firstName: $( '#billing_first_name' ).val(),
                        lastName: $( '#billing_last_name' ).val(),
                        phone: $( '#billing_phone' ).val()
                    };
                    let paymentMethodObj = {
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
                $('#mc-card-error').html('<div class="woocommerce-error " role="alert">'+ this.errMsg +'</div>');
            }
            this.errMsg = '';
        },
        reset: function () {
            this.errMsg = '';
            $('input[name=mc_payment_method_id]').remove();
            $('#mc-card-error').empty();
        },
        checkoutMcPlace: function () {
            $('#place_order').on('click',this.onSubmit );
        },
        createElements: function () {
            if ( 'yes' === mc_checkout_params.is_checkout ) {
                $( document.body ).on( 'updated_checkout', function() {
                    mc_sdk.elementInit("payment_steps",{
                        formId: 'mc-card-element', // 页面表单id
                        frameId: 'mc-card-frame', // 生成的IframeId
                        mode: mc_checkout_params.mode,
                        customerId: '',
                        autoValidate:false,
                        layout: mc_checkout_params.layout
                    }).catch((err) => {
                        console.log( err)
                    });
                    wc_moneycollect_form.checkoutMcPlace();
                });
            }
        },
        init: function () {
            // checkout page
            if ( $( 'form.woocommerce-checkout' ).length ) {
                this.form = $( 'form.woocommerce-checkout' );
            }
            this.form.on('change', this.reset);
            this.errMsg = '';
            this.createElements()
        },

    };

    wc_moneycollect_form.init();

    window.addEventListener("getErrorMessage", e => {
        wc_moneycollect_form.errMsg = e.detail.errorMessage;
        wc_moneycollect_form.onError();
    });

});