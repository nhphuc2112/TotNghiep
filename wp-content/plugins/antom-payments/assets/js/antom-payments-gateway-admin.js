function antomCopyToClipboard(text, success_callback, failed_callback) {
    var textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    document.body.appendChild(textarea);
    textarea.select();
    try {
        var successful = document.execCommand("copy");
        if (successful){
            success_callback && success_callback()
        }else{
            failed_callback && failed_callback()    
        }
    } catch (error) {
        failed_callback && failed_callback()
    }
    document.body.removeChild(textarea);
}
(function ($) {
    $(document).ready(function () {

        $('.copy-icon').on('click', function () {
            const href = $(this).parent('a').data('clipboard-text')
            antomCopyToClipboard(href, function () {
                $('.copy-result').html('The notify url is successfully copied to clipboard.').fadeIn(100)
                setTimeout(function () {
                    $('.copy-result').fadeOut(1000)
                }, 3000)
            }, function () {
                $('.copy-result').html('The notify url copied failed, please copy it by your self.').fadeIn(100)
                setTimeout(function () {
                    $('.copy-result').fadeOut(1000)
                }, 3000)
            })
        })

        $('.antom-test-mode-checkbox').on('change', function () {
            let is_test_mode = $(this).is(':checked') === true
            if (!is_test_mode) {
                $('.prod-test-hide').each(function () {
                    $(this).parents('tr').hide()
                })
            } else {
                $('.prod-test-hide').each(function () {
                    $(this).parents('tr').show()
                })
            }
        })

        $('#woocommerce__prod_currency').on('change', function (e) {
            let value = $(this).val()
            if (value === '') {
                $('.prod-currency-not-selected-warning-text').html(antom_common_setting.settlement_required_text)
            } else {
                $('.prod-currency-not-selected-warning-text').html(antom_common_setting.settlement_warning_text)
            }
        })

        $('.woocommerce-save-button').on('click', function (e) {
            e.preventDefault()
            if ($('.antom-test-mode-checkbox').length > 0) {
                let is_test_mode = $('.antom-test-mode-checkbox').is(':checked') === true
                if (!is_test_mode) {
                    let currency = $('.antm-prod-currency').val()
                    if (currency === '') {
                        setTimeout(function () {
                            $('.woocommerce-save-button').removeClass('is-busy')
                        }, 200)
                        $('.prod-currency-not-selected-warning-text').html(antom_common_setting.settlement_required_text)
                        return false
                    }
                }

                //valid live mode client id cannot use sandbox client id
                let client_id = $('#woocommerce__clientid').val()
                if (client_id.startsWith("SANDBOX")) {
                    $('.live-mode-cannot-use-test-client-id').removeClass('hide')
                    setTimeout(function () {
                        $('.woocommerce-save-button').removeClass('is-busy')
                    }, 200)
                    return false
                } else {
                    $('.live-mode-cannot-use-test-client-id').addClass('hide')
                }

            }

            $(this).parents('form').submit()
        })

        //get section from url
        function getQueryParam(url, param) {
            const queryString = url.split('?')[1];
            if (!queryString) {
                return null;
            }
            const params = queryString.split('&');
            for (let i = 0; i < params.length; i++) {
                const pair = params[i].split('=');
                if (decodeURIComponent(pair[0]) === param) {
                    return decodeURIComponent(pair[1] || '');
                }
            }
            return null;
        }

        const section = getQueryParam(window.location.href, 'section');
        if (section === 'antom-payment-gateway') {
            $('.woocommerce-save-button').parents('p.submit').append('<a target="_blank" class="visit-antom" href="' + antom_common_setting.antom_web_site + '">' + antom_common_setting.visit_text + ' <img src="' + antom_common_setting.visit_logo + '" /></a>')
        }
    });
})(jQuery);