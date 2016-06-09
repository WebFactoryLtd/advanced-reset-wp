;(function($, window, document, undefined) {
    "use strict";

    $(document).ready(function() {
        var type = $('.arwp-type'),
            input = $('#arwp-input'),
            submit = $('#arwp-button'),
            post_class = $('.post-class'),
            re_install_class = $('.re-install');


        var hideElement = function() {
            post_class.hide();
            re_install_class.hide();
        };
        hideElement();

        type.click(function() {
            var val = $(this).val();

            switch (val) {
                case 're-install':
                    re_install_class.show();
                    post_class.hide();
                    break;
                case 'post-clear':
                    post_class.show();
                    re_install_class.hide();
                    break;
                default:
                    hideElement();
            }

            //if ($(this).val() == 'post-clear') {
            //   post_class.show();
            //} else {
            //   post_class.hide();
            //}
        });

        submit.click(function() {
            var message = 'Вы уверены, что хотите удалить данные?';
            if (confirm(message)) {
                submit.submit();
            } else {
                input.val('');
                return false;
            }
        });
    });

})(jQuery, window, document);