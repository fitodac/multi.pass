jQuery(document).on( 'click', '.bloom-woocommerce-notice .notice-dismiss', function() {

    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'bloom_dismiss_woocommerce_notice'
        }
    })

})