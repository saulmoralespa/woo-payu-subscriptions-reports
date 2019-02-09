(function($){
    $(function(){
        $("#woo-payu-subscriptions-reports").submit(function(e){
            e.preventDefault();

            $.ajax({
                    type: 'POST',
                    url:  ajaxurl,
                    dataType: 'json',
                    data: $(this).serialize() + '&action=woo_payu_subscriptions_reports',
                    beforeSend: function(){
                        swal.fire({
                            title: woo_payu_subscriptions_reports.msjGenerating,
                            onOpen: () => {
                                swal.showLoading()
                            },
                            allowOutsideClick: false
                        });
                    },
                    success: function(r){
                        if (r.status){
                            Swal.close();
                            window.location.replace(r.url);
                        }else{
                            swal.fire(
                                woo_payu_subscriptions_reports.msjNotRegisters,
                                woo_payu_subscriptions_reports.msjErrorNotRegisters,
                                'warning'
                            );
                        }
                    }
                }
            );
        });

    });
})(jQuery);