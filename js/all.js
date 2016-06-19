;(function($, window, document, undefined) {
    "use strict";

    $(document).ready(function() {
        var type = $('.arwp-type'),
            form = $('#arwp_form'),
            input = $('#arwp-input'),
            result = $('#result'),
            submit = $('#arwp-button'),
            post_class = $('.post-class');


        post_class.hide();
        type.click(function() {
            var val = $(this).val();
            var info = '.' + val + '-info';

            if (val == 'post-clear') {
                post_class.show();
            } else {
                post_class.hide();
            }

            $('.arwp-form-info > p').hide();
            $('.arwp-form-info').find(info).fadeIn('fast');
        });


        submit.click(function(e) {
            var message = 'Are you sure you want to delete the data?';

            if (!type.prop('checked') && !input.val()) {
                return true;
            }

            if (confirm(message)) {
                e.preventDefault();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'arwp_ajax',
                        nonce: arwp_ajax.nonce,
                        my_form: form.serialize()
                    },
                    beforeSend: function() {
                        $('#loader, .overflow').fadeIn('slow');
                    },
                    success: function(data) {
                        result.find('p').remove();
                        result.append(data);
                        $('#loader, .overflow').fadeOut('slow');
                    }
                });
                return true;
            } else {
                input.val('');
                return false;
            }
        });
    });

})(jQuery, window, document);