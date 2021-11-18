let $ = jQuery;
if( $ ){
    $(function () {
        var test_model = $("#woocommerce_moneycollect_test_model");

        verificationModel(test_model.is(":checked"));

        test_model.click(function () {
            verificationModel($(this).is(":checked"));
        });

        function verificationModel(bool) {
            $("#woocommerce_moneycollect_publishable_key").attr('readonly',bool);
            $("#woocommerce_moneycollect_secret_key").attr('readonly',bool);
            $("#woocommerce_moneycollect_test_publishable_key").attr('readonly',!bool);
            $("#woocommerce_moneycollect_test_secret_key").attr('readonly',!bool);
        }

        var checkout_model = $("#woocommerce_moneycollect_checkout_model");
        var from_style_tr = $("#woocommerce_moneycollect_form_style").parents("tr");
        var save_card_tr = $("#woocommerce_moneycollect_save_card").parents("tr");

        verificationCheckout(checkout_model.val());

        checkout_model.change(function () {
            verificationCheckout(checkout_model.val());
        });

        function verificationCheckout(val) {
            console.log(val);
            if( val === '1' ){
                from_style_tr.show();
                save_card_tr.show();
            }else {
                from_style_tr.hide();
                save_card_tr.hide();
            }
        }
    });
}

