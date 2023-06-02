let $ = jQuery;
if( $ ){
    $(function () {
        var test_model = $("#woocommerce_moneycollect_test_model");

        var publishable_key = $("#woocommerce_moneycollect_publishable_key").parents("tr");
        var secret_key = $("#woocommerce_moneycollect_secret_key").parents("tr");
        var test_publishable_key = $("#woocommerce_moneycollect_test_publishable_key").parents("tr");
        var test_secret_key = $("#woocommerce_moneycollect_test_secret_key").parents("tr");

        verificationModel(test_model.is(":checked"));

        test_model.click(function () {
            verificationModel($(this).is(":checked"));
        });

        function verificationModel(bool) {

            if(bool){
                publishable_key.hide();
                secret_key.hide();
                test_publishable_key.show();
                test_secret_key.show();
            }else {
                publishable_key.show();
                secret_key.show();
                test_publishable_key.hide();
                test_secret_key.hide();
            }

        }

        var checkout_model = $("#woocommerce_moneycollect_checkout_model");
        var from_style_tr = $("#woocommerce_moneycollect_form_style").parents("tr");
        var save_card_tr = $("#woocommerce_moneycollect_save_card").parents("tr");

        verificationCheckout(checkout_model.val());
        checkout_model.change(function () {
            verificationCheckout(checkout_model.val());
        });
        function verificationCheckout(val) {
            if( val === '1' ){
                from_style_tr.show();
                save_card_tr.show();
            }else {
                from_style_tr.hide();
                save_card_tr.hide();
            }
        }

        var icon = $("#woocommerce_moneycollect_icon");

        verificationIcon(icon.is(":checked"));
        icon.change(function () {
            verificationIcon(icon.is(":checked"));
        })

        function verificationIcon(val){

            let arr = [
                'visa','mastercard','ae','jcb','discover','diners_club','maestro','unionpay'
            ]
            for (let i = 0; i < arr.length; i++){
                if( val ){
                    $("#woocommerce_moneycollect_" + arr[i]).parents("tr").show();
                }
                else {
                    $("#woocommerce_moneycollect_" + arr[i]).parents("tr").hide();
                }
            }
        }


    });
}

