function get_card_icon_by_type(card_type) {
    const icon_list = {
        '': 'card-gray.svg',
        'Unknown': 'card-highlight.svg',
        'Visa': 'VISA.svg',
        'Mastercard': 'MasterCard.svg',
    }
    if (typeof icon_list[card_type] == 'undefined') {
        return 'card.svg'
    }

    return icon_list[card_type]
}

//card number change event listener
function format_card_number(obj) {
    let $ = jQuery
    var value = $(obj).val().replace(/\D/g, '');
    value = value.substring(0, 16)
    let image_icon
    if (value == '') {
        image_icon = get_card_icon_by_type('')
    } else {
        const card_type = get_card_type(value)
        image_icon = get_card_icon_by_type(card_type)
    }


    let image_dom = $('.antom-cards-container-item-line > .card-cover').find('img')
    let image_assets = image_dom.data('host')
    image_dom.attr('src', image_assets + image_icon)

    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    $(obj).val(value);
}

// get card type by card number
function get_card_type(cardNumber) {
    // card regex
    var visaReg = /^4[0-9]{12}(?:[0-9]{3})?$/;
    var mastercardReg = /^5[1-5][0-9]{14}$/;

    // get card brand
    if (visaReg.test(cardNumber)) {
        return 'Visa';
    } else if (mastercardReg.test(cardNumber)) {
        return 'Mastercard';
    } else {
        return 'Unknown';
    }
}

function format_expire_date(object, e) {
    let $ = jQuery
    let value = $(object).val()

    value = value.replace(/\D/g, '');

    // 限制长度为4位数字
    value = value.substring(0, 4);

    // 处理月份：如果输入的第一个数字是2-9，自动补0
    if (value.length === 1 && parseInt(value) >= 2) {
        value = '0' + value;
    }

    // 如果长度大于2，在中间插入斜杠
    if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }

    // 验证月份是否有效 (01-12)
    if (value.length >= 2) {
        const month = parseInt(value.substring(0, 2));
        if (month > 12) {
            value = '12' + value.substring(2);
        }
        if (month === 0) {
            value = '01' + value.substring(2);
        }
    }

    $(object).val(value)
}

function format_cvc(obj) {
    let $ = jQuery
    let value = $(obj).val()
    value = value.replace(/\D/g, '')
    value = value.substring(0, 4)
    $(obj).val(value.slice(0, 4))
}


(function ($) {
    function generateAESKey() {
        const chars = '1234567890';
        let key = '';
        for (let i = 0; i < 31; i++) {
            const index = Math.floor(Math.random() * chars.length);
            key += chars[index];
        }
        key = Math.floor(Math.random() * 9 + 1) + key;
        return key;
    }

    function getCipherText(key, card, month, year, holder_name = '', cvv = '') {
        let first_name = ''
        let last_name = ''
        holder_name = holder_name.trim()
        let space_index = holder_name.indexOf(' ')
        if (space_index !== -1) {
            first_name = holder_name.substring(0, space_index)
            last_name = holder_name.slice(space_index).trim()
        } else {
            first_name = holder_name
        }
        if (holder_name.indexOf())

            card = card.replace(/\s/g, '');
        let text = {
            "paymentMethodDetail": {
                "paymentMethodDetailType": "card",
                "card": {
                    "cardNo": card,
                    "expiryMonth": month,
                    "expiryYear": year,
                },
                "instUserName": {
                    "firstName": first_name,
                    "fullName": holder_name,
                    "lastName": last_name,
                    "middleName": ""
                },
            }
        }
        let cipher = CryptoJS.AES.encrypt(JSON.stringify(text), key, {
            mode: CryptoJS.mode.ECB,
            padding: CryptoJS.pad.Pkcs7
        });
        let ciphertext = cipher.toString();
        return ciphertext
    }

    function show_antom_error(error_message, dom_class = '', need_scroll = false) {
        let error_container = $('.woocommerce-notices-wrapper').first()
        let error_text = '<div class="woocommerce-error antom-errors">' + error_message + '</div>'
        error_container.html(error_text)

        if (dom_class) {
            $('.' + dom_class).html(error_message).removeClass('hide-error')
        }
        if (need_scroll) {
            $('html, body').animate({scrollTop: 0}, 500);
        }
    }

    function hide_antom_error(dom_class = '') {
        $('.antom-errors').remove()
        if (dom_class) {
            $('.' + dom_class).addClass('hide-error')
        }

    }

    function antom_is_valid_date(str) {
        const regex = /^(\d{2}\/\d{2}|\d{2} \/ \d{2})$/;
        if (!regex.test(str)) {
            return false;
        }
        const parts = str.split('/');
        const month = parseInt(parts[0], 10);
        const year = parseInt(parts[1], 10);
        const currentYear = new Date().getFullYear() % 100;
        if (year < currentYear) {
            return false;
        }
        if (month < 1 || month > 12) {
            return false;
        }
        const expirationDate = new Date(year + 2000, month, 0);
        const currentDate = new Date();
        if (expirationDate < currentDate) {
            return false;
        }
        return true;
    }

    function antom_is_valid_CVV(str) {
        const regex = /^\d{3}$/;
        return regex.test(str);
    }

    function antom_is_valid_card_number(card_number) {
        card_number = card_number.replace(/\D/g, '')
        const visaRegex = /^4[0-9]{12}(?:[0-9]{3})?$/;
        const mastercardRegex = /^5[1-5][0-9]{14}$/;
        return visaRegex.test(card_number) || mastercardRegex.test(card_number);
    }

    function antom_trim(str) {
        return str.replace(/^\s+|\s+$/g, '');
    }

    function antom_get_current_date_time() {
        var now = new Date();
        var year = now.getFullYear();
        var month = now.getMonth() + 1;
        var day = now.getDate();
        var hour = now.getHours();
        var minute = now.getMinutes();
        var second = now.getSeconds();
        if (month < 10) {
            month = '0' + month;
        }
        if (day < 10) {
            day = '0' + day;
        }
        if (hour < 10) {
            hour = '0' + hour;
        }
        if (minute < 10) {
            minute = '0' + minute;
        }
        if (second < 10) {
            second = '0' + second;
        }
        var dateTime = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
        return dateTime;
    }

    function antom_do_checkout_submit() {

        show_antom_loading_mask(antom_payment_gateways_settings.request_to_antom_payments_gateway)

        $.ajax({
            type: 'POST',
            url: wc_checkout_params.checkout_url,
            data: $('form.checkout').serialize(),
            dataType: 'json',
            beforeSend: function () {
                $('body').trigger('update_checkout');
            },
            success: function (result) {
                try {
                    if (result.result === 'success') {
                        show_antom_loading_mask(antom_payment_gateways_settings.redirect_to_antom_loading_description)
                        if (antom_payment_gateways_settings.antom_is_mobile) {
                            window.location.href = result.redirect_app_url;
                            setTimeout(function () {
                                if (document.hidden || document.webkitHidden) {
                                    $('body').trigger('update_checkout');
                                } else {
                                    window.location.href = result.redirect;
                                }
                            }, 3000)
                        } else {
                            window.location.href = result.redirect;
                        }
                    } else if ('failure' === result.result) {
                        hide_antom_loading_mask()
                        show_antom_error(strip_html_tags(result.messages), '', true);
                        // throw 'Result failure';
                    } else {
                        hide_antom_loading_mask()
                        show_antom_error(strip_html_tags(result.messages), '', true);
                        // throw 'Invalid response';
                    }
                } catch (err) {
                    // Reload page
                    if (true === result.reload) {
                        window.location.reload();
                        return;
                    }

                    // Trigger update in case we need a fresh nonce
                    if (true === result.refresh) {
                        $(document.body).trigger('update_checkout');
                    }

                    hide_antom_loading_mask()

                    // Add new errors
                    if (result.messages) {
                        show_antom_error(strip_html_tags(result.messages), '', true);
                    } else {
                        show_antom_error(wc_checkout_params.i18n_checkout_error);
                    }
                }


            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Detach the unload handler that prevents a reload / redirect
                wc_checkout_form.detachUnloadEventsOnSubmit();

                // This is just a technical error fallback. i18_checkout_error is expected to be always defined and localized.
                var errorMessage = errorThrown;

                if (
                    typeof wc_checkout_params === 'object' &&
                    wc_checkout_params !== null &&
                    wc_checkout_params.hasOwnProperty('i18n_checkout_error') &&
                    typeof wc_checkout_params.i18n_checkout_error === 'string' &&
                    wc_checkout_params.i18n_checkout_error.trim() !== ''
                ) {
                    errorMessage = wc_checkout_params.i18n_checkout_error;
                }

                wc_checkout_form.submit_error(
                    '<div class="woocommerce-error">' + errorMessage + '</div>'
                );
            }
        });
    }

    function strip_html_tags(html) {
        return html.replace(/<\/?[^>]+(>|$)/g, "");
    }

    function formatPublicKey(publicKey) {
        // remove possible space
        const cleanKey = publicKey.replace(/\s/g, '');

        // add header and end str
        const formattedKey =
            "-----BEGIN PUBLIC KEY-----\n    " + cleanKey + "    -----END PUBLIC KEY-----";

        return formattedKey;
    }

    function show_antom_loading_mask(message = '') {
        $('body').css('overflow', 'hidden');
        if (message) {
            $('#antom-loading-mask').find('p').text(message)
        }
        $('#antom-loading-mask').show();
    }

    function hide_antom_loading_mask() {
        $('body').css('overflow', 'auto');
        $('#antom-loading-mask').hide();
    }


    $(document).ready(function () {

        if ($('#antom-loading-mask').length === 0) {

            $('body').append('<div id="antom-loading-mask"><img style="width:30px;" src="' + antom_payment_gateways_settings.redirect_to_antom_loading_image_url + '" alt="Loading..."><p></p></div>');
        }

        $('body').on('change', 'input[name="payment_method"]', function () {
            let payment_method = $(this).val();
            let test_warning_dom = $('.antom-test-mode-warning')
            if (payment_method.startsWith('antom_')) {
                is_antom_payment_gateway = true
                test_warning_dom.show()
            } else {
                test_warning_dom.hide()
            }
        });
        $(document).on('click', 'form.checkout #place_order', function (e) {
            let payment_method = $('input[name="payment_method"]:checked').val();
            let is_antom_payment_gateway = false
            if (payment_method.startsWith('antom_')) {
                is_antom_payment_gateway = true
            }
            if (is_antom_payment_gateway) {
                e.preventDefault();
                antom_do_checkout_submit()
            }

            // if (is_antom_payment_gateway) {
            //     e.preventDefault();

            //     if (payment_method == 'antom_card') {
            //         let antom_dom = $('.antom-cards-container-item')
            //         let card = antom_dom.find('.antom-card-number').val()
            //         let expire = antom_dom.find('.antom-expire-date').val()
            //         let cvv = antom_dom.find('.antom-cvc').val()
            //         let holder_name = antom_dom.find('.antom-holder-name').val()
            //         if (!card) {
            //             show_antom_error(antom_languages.card_empty_error_message, 'antom-card-number-error')
            //             return
            //         }

            //         if (!antom_is_valid_card_number(card)) {
            //             show_antom_error(antom_languages.card_invalid_error_message, 'antom-card-number-error')
            //             return
            //         }

            //         hide_antom_error('antom-card-number-error')

            //         if (!expire) {
            //             show_antom_error(antom_languages.expiry_empty_error_message, 'antom-expire-date-error')
            //             return
            //         }

            //         if (!antom_is_valid_date(expire)) {
            //             show_antom_error(antom_languages.expire_invalid_error_message, 'antom-expire-date-error')
            //             return
            //         }

            //         hide_antom_error('antom-expire-date-error')

            //         if (!cvv) {
            //             show_antom_error(antom_languages.cvv_empty_error_message, 'antom-cvc-error')
            //             return
            //         }

            //         if (!antom_is_valid_CVV(cvv)) {
            //             show_antom_error(antom_languages.cvv_invalid_error_message, 'antom-cvc-error')
            //             return
            //         }

            //         hide_antom_error('antom-cvc-error')

            //         if (holder_name == '') {
            //             show_antom_error(antom_languages.holder_name_required, 'antom-holder-name-error')
            //             return
            //         } else {
            //             hide_antom_error('antom-holder-name-error')
            //         }

            //         let expiry_parts = expire.split('/')
            //         let month = antom_trim(expiry_parts[0])
            //         let year = antom_trim(expiry_parts[1])

            //         hide_antom_error()

            //         const aesKey = generateAESKey();
            //         const key = CryptoJS.enc.Utf8.parse(aesKey);
            //         const ciphertext = getCipherText(key, card, month, year, holder_name, cvv)


            //         const encrypt = new JSEncrypt();

            //         let public_key = formatPublicKey(antom_card_settings.antom_public_key)

            //         encrypt.setPublicKey(public_key);
            //         const encrypted = encrypt.encrypt(aesKey);

            //         show_antom_loading_mask(antom_payment_gateways_settings.request_to_antom_payments_gateway)

            //         $.ajax({
            //             type: 'POST',
            //             url: antom_card_settings.card_token_url,
            //             data: ciphertext,
            //             contentType: 'text/plain',
            //             headers: {
            //                 'client-id': antom_card_settings.client_id,
            //                 'request-time': antom_get_current_date_time(),
            //                 'signature': 'algorithm=RSA256, keyVersion=2, signature=testing_signature',
            //                 'encrypt': 'algorithm=AES256, keyVersion=0, symmetricKey=' + encodeURIComponent(encrypted)
            //             },
            //             success: function (data) {
            //                 if (
            //                     typeof data.paymentMethodDetail != 'undefined' &&
            //                     typeof data.paymentMethodDetail.card != 'undefined' &&
            //                     typeof data.paymentMethodDetail.card.cardToken != 'undefined'
            //                 ) {
            //                     let cardToken = data.paymentMethodDetail.card.cardToken;
            //                     $('#antom_card_token').val(cardToken)
            //                     antom_do_checkout_submit()
            //                 } else if(typeof data.result != 'undefined' && typeof data.result.resultMessage != 'undefined'){
            //                     hide_antom_loading_mask()
            //                     show_antom_error(antom_languages.antom_card_token_fetch_error);
            //                 }else {
            //                     hide_antom_loading_mask()
            //                     show_antom_error(wc_checkout_params.i18n_checkout_error);
            //                 }
            //             },
            //             error: function (xhr, status, error) {
            //                 hide_antom_loading_mask()
            //                 show_antom_error(wc_checkout_params.i18n_checkout_error);
            //             }
            //         })
            //     } else {
            //         antom_do_checkout_submit()
            //     }
            // }


        });
    })
})(jQuery);