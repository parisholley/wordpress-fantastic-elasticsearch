(function ($) {
    function done(failed) {
        $("#reindex").removeAttr('disabled').addClass('complete');
        $('#progress').hide();

        if (failed) {
            $('#error').show().find('.msg').text(failed);
        } else {
            $.ajax({
                url: window.indexing.ajaxurl,
                type: 'POST',
                data: {
                    'action': 'esswap',
                },
                error: function (xhr) {
                    $('#error').show().find('.msg').text('Unable to swap indexes.');
                },
                success: function (indexed) {
                    $('.finished').text(0);
                    $('#complete').show();
                }
            });
        }
    }

    function index(page) {
        $.ajax({
            url: window.indexing.ajaxurl,
            type: 'POST',
            data: {
                'action': 'esreindex',
                'page': page
            },
            error: function (xhr) {
                done(xhr.responseText);
            },
            success: function (indexed) {
                var indexed = parseInt(indexed);

                var total = $('.finished');

                total.text(parseInt(total.text()) + indexed);

                if (indexed == window.indexing.perpage) {
                    index(page + 1);
                } else {
                    done();
                }
            }
        });
    }

    $(function () {
        $('.total').text(window.indexing.total);

        $("#reindex").click(function () {
            $(this).attr('disabled', 'disabled');
            $('#progress').show();
            $('#complete').hide();
            $('#error').hide();

            index(1);

            return false;
        });
    });
})(jQuery);
