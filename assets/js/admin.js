jQuery(document).ready(function ($) {

    $('#guardwp-unlock').on('click', function () {

        $.post(guardwp_ajax.ajax_url, {
            action: 'guardwp_unlock',
            nonce: guardwp_ajax.nonce
        }, function (response) {
            alert(response.data);
        });

    });

});