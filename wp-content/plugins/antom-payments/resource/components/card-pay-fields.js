import React, { useState, useEffect, useRef } from 'react';
import creditCardType from 'credit-card-type';
import $ from 'jquery';


const CardPayFields = (props) => {
    var request_card_token = ''
    const { eventRegistration, emitResponse, settings } = props;
    const { onCheckoutValidationBeforeProcessing, onPaymentSetup, onCheckoutAfterProcessingWithSuccess, onCheckoutAfterProcessingWithError
    } = eventRegistration;
    const [cardNumber, setCardNumber] = useState('');
    const [cardType, setCardType] = useState('');
    const [expireDate, setExpireDate] = useState('');
    const [prevExpireDate, setPrevExpireDate] = useState('');
    const [isDeleteMod, setIsDeleteMod] = useState(false);
    const [cardCvc, setCardCvc] = useState('')
    const [cardHolderName, setCardHolderName] = useState('')

    const cardNumberInputRef = useRef(null);
    const expireDateInputRef = useRef(null);
    const cvcInputRef = useRef(null);
    const holderNameInputRef = useRef(null);


    const handleCardNumberChange = (event) => {
        let value = event.target.value;
        value = value.replace(/\D/g, '');
        //limit the cardno length
        if (value.length > 19) {
            value = value.slice(0, 19);
        }

        if (value.length > 12) {
            //  use credit-card-type to get card brand
            const cardInfo = creditCardType(value);
            if (cardInfo.length > 0) {
                let card_type = 'Unknown'
                if (typeof cardInfo[0].niceType !== 'undefined') {
                    card_type = cardInfo[0].niceType
                }
                setCardType(cardInfo[0].niceType);
            } else {
                setCardType('');
            }
        }

        if (value.length === 0) {
            setCardType('');
        }

        // format cardNo
        value = value.replace(/(.{4})/g, '$1 ').trim();
        setCardNumber(value);

        setTimeout(() => {
            cardNumberInputRef.current.focus();
        }, 0)


    }


    const handleExpireDateChange = (event) => {
        setPrevExpireDate(expireDate);
        let value = event.target.value;

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


        setExpireDate(value);

        setTimeout(() => {
            expireDateInputRef.current.focus();
        }, 0)
    }


    useEffect(() => {
        if (expireDate.length < prevExpireDate.length) {
            setIsDeleteMod(true)
        } else {
            setIsDeleteMod(false)
        }
    }, [expireDate, prevExpireDate]);

    const handleCardCvcChange = (event) => {
        let value = event.target.value
        value = value.replace(/\D/g, '')
        value = value.substring(0, 4)
        setCardCvc(value)

        setTimeout(() => {
            cvcInputRef.current.focus();
        }, 0)
    }

    const handleCardHolderName = (event) => {
        let value = event.target.value
        setCardHolderName(value)

        setTimeout(() => {
            holderNameInputRef.current.focus();
        }, 0)
    }

    const show_antom_loading_mask = (message = '') => {
        $('body').css('overflow', 'hidden');
        if (message) {
            $('#antom-loading-mask').find('p').text(message)
        }
        $('#antom-loading-mask').show();
    }

    const hide_antom_loading_mask = () => {
        $('body').css('overflow', 'auto');
        $('#antom-loading-mask').hide();
    }

    useEffect(() => {

        const show_error = (class_name, error_message) => {
            const elements = document.getElementsByClassName(class_name);

            if (elements.length > 0) {
                const element = elements[0];

                element.textContent = error_message;

                element.style.display = 'block';
            } else {
                console.warn(`Element with class "${class_name}" not found.`);
            }
        }

        function hide_error(class_name) {

            const elements = document.getElementsByClassName(class_name);


            for (let i = 0; i < elements.length; i++) {

                elements[i].style.display = 'none';
                elements[i].textContent = '';
            }


            if (elements.length === 0) {
                console.warn(`Element with class "${class_name}" not found.`);
            }
        }


        const antom_is_valid_card_number = (card_number) => {
            card_number = card_number.replace(/\D/g, '')
            return /^\d{14,19}$/.test(card_number);
        }

        const antom_is_valid_date = (str) => {
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

        const antom_is_valid_CVV = (str) => {
            const regex = /^\d{3}$/;
            return regex.test(str);
        }

        const antom_trim = (str) => {
            return str.replace(/^\s+|\s+$/g, '');
        }

        const generateAESKey = () => {
            const chars = '1234567890';
            let key = '';
            for (let i = 0; i < 31; i++) {
                const index = Math.floor(Math.random() * chars.length);
                key += chars[index];
            }
            key = Math.floor(Math.random() * 9 + 1) + key;
            return key;
        }

        const getCipherText = (key, card, month, year, holder_name = '', cvv = '') => {
            

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

        const antom_get_current_date_time = () => {
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

        const formatPublicKey = (publicKey) => {

            const cleanKey = publicKey.replace(/\s/g, '');


            const formattedKey =
                "-----BEGIN PUBLIC KEY-----\n    " + cleanKey + "    -----END PUBLIC KEY-----";

            return formattedKey;
        }

        const isAntomCardChecked = () => {
            let id = "radio-control-wc-payment-method-options-" + settings.payment_method
            return $('#' + id).is(':checked')
        }


        const checkoutValidate = onCheckoutValidationBeforeProcessing((event) => {
            if (isAntomCardChecked()) {
                const card_number_input = document.querySelector('input.antom-card-number')
                let card_number = ''
                if (card_number_input) {
                    card_number = card_number_input.value
                }
                if (!card_number) {
                    show_error('antom-card-number-error', settings.card_validate_message.card_empty_error_message)
                    return {
                        type: 'error',
                        errorMessage: settings.card_validate_message.card_empty_error_message,
                    }
                }

                if (!antom_is_valid_card_number(card_number)) {
                    show_error('antom-card-number-error', settings.card_validate_message.card_invalid_error_message)
                    return {
                        type: 'error',
                        errorMessage: settings.card_validate_message.card_invalid_error_message,
                    }
                }

                hide_error('antom-card-number-error')

                const expire_date_input = document.querySelector('input.antom-expire-date')
                let expire_date = ''
                if (expire_date_input) {
                    expire_date = expire_date_input.value
                }

                if (!expire_date) {
                    show_error('antom-expire-date-error', settings.card_validate_message.expiry_empty_error_message)
                    return {
                        type: 'error',
                        errorMessage: settings.card_validate_message.expiry_empty_error_message,
                    }
                }

                if (!antom_is_valid_date(expire_date)) {
                    show_error('antom-expire-date-error', settings.card_validate_message.expire_invalid_error_message)
                    return {
                        type: 'error',
                        errorMessage: settings.card_validate_message.expire_invalid_error_message,
                    }
                }

                hide_error('antom-expire-date-error')

                const CVC_input = document.querySelector('input.antom-cvc')
                let cvc = ''
                if (CVC_input) {
                    cvc = CVC_input.value
                }
                if (!cvc) {
                    show_error('antom-cvc-error', settings.card_validate_message.cvv_empty_error_message)
                    return {
                        type: 'error',
                        errorMessage: settings.card_validate_message.cvv_empty_error_message,
                    }
                }

                if (!antom_is_valid_CVV(cvc)) {
                    show_error('antom-cvc-error', settings.card_validate_message.cvv_invalid_error_message)
                    return {
                        type: 'error',
                        errorMessage: settings.card_validate_message.cvv_invalid_error_message,
                    }
                }

                hide_error('antom-cvc-error')

                const holder_name_input = document.querySelector('input.antom-holder-name')
                let holder_name = ''
                if (holder_name_input) {
                    holder_name = holder_name_input.value
                }

                if (!holder_name) {
                    show_error('antom-holder-name-error', settings.card_validate_message.holder_name_required)
                    return {
                        type: 'error',
                        errorMessage: settings.card_validate_message.holder_name_required,
                    }
                } else {
                    hide_error('antom-holder-name-error')
                }



                // request to get card token
                let expiry_parts = expire_date.split('/')
                let month = antom_trim(expiry_parts[0])
                let year = antom_trim(expiry_parts[1])

                const aesKey = generateAESKey();
                const key = CryptoJS.enc.Utf8.parse(aesKey);
                const ciphertext = getCipherText(key, card_number, month, year, holder_name, cvc)


                const encrypt = new JSEncrypt();
                const public_key = formatPublicKey(settings.card_public_key)


                encrypt.setPublicKey(public_key);
                let encrypted = encrypt.encrypt(aesKey);


                const url = settings.card_token_url
                const method = 'POST'
                const headers = {
                    'client-id': settings.client_id,
                    'request-time': antom_get_current_date_time(),
                    'signature': 'algorithm=RSA256, keyVersion=2, signature=testing_signature',
                    'encrypt': 'algorithm=AES256, keyVersion=0, symmetricKey=' + encodeURIComponent(encrypted)
                }

                var cardTokenGetSuccess = false
                var keyNotFound = false;

                //show_antom_loading_mask(settings.animate_setting.request_to_antom_payments_gateway)

                // 发起请求
                $.ajax({
                    type: 'POST',
                    url: settings.card_token_url,
                    data: ciphertext,
                    contentType: 'text/plain',
                    headers: headers,
                    async: false,
                    success: function (antom_response) {
                        if (antom_response && antom_response.paymentMethodDetail && antom_response.paymentMethodDetail.card && antom_response.paymentMethodDetail.card.cardToken) {
                            cardTokenGetSuccess = true
                            request_card_token = antom_response.paymentMethodDetail.card.cardToken
                            return {
                                type: 'success',
                            }
                        } else {
                            if(antom_response && antom_response.result && antom_response.result.resultCode == 'KEY_NOT_FOUND'){
                                keyNotFound = true;
                            }

                            return {
                                type: 'error',
                                errorMessage: settings.antom_card_token_fetch_error,
                            };
                        }
                    },
                    error: function (xhr, status, error) {

                        return {
                            type: 'error',
                            errorMessage: settings.antom_card_token_fetch_error,
                        };
                    }
                })

                if (cardTokenGetSuccess === false) {
                    
                    if( keyNotFound === true){
                        return {
                            type: 'error',
                            errorMessage: 'clientId error, Please use an account register from antom plugin to process card payment',
                        };
                    }

                    return {
                        type: 'error',
                        errorMessage: settings.antom_card_token_fetch_error,
                    };
                }
            }

        });
        return undefined
    }, [
        emitResponse.responseTypes.ERROR,
        emitResponse.responseTypes.SUCCESS,
        onCheckoutValidationBeforeProcessing
    ]);

    useEffect(() => {
        const checkoutProcess = onPaymentSetup(() => {
            const is_antom_card_valid = !!request_card_token.length;

            if (is_antom_card_valid) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            antom_card_token: request_card_token,
                        },
                    },
                };
            }

            return {
                type: emitResponse.responseTypes.ERROR,
                message: 'There was an error',
            };
        })

        return () => {
            checkoutProcess();
        };
    }, [
        onPaymentSetup
    ])

    useEffect(() => {
        const afterProcessSuccess = onCheckoutAfterProcessingWithSuccess(() => {
            //show request to antom payments gateway message.
            //show_antom_loading_mask(settings.animate_setting.redirect_to_antom_loading_description)
        })

        return () => {
            afterProcessSuccess();
        };
    }, [onCheckoutAfterProcessingWithSuccess])

    useEffect(() => {
        const afterProcessError = onCheckoutAfterProcessingWithError(() => {
            if (isAntomCardChecked()) {
                //hide_antom_loading_mask()
            }
        })

        return () => {
            afterProcessError();
        };
    }, [onCheckoutAfterProcessingWithError])



    // get image path by card type
    const cardIcon = cardType ? settings.card_icon_lists[cardType] : settings.card_icon_lists.card;
    const isTestMode = settings.is_test_mode
    const isLoggedIn = settings.is_logged_in
    const loginWarningMessage = settings.login_warning_message

    const testWarningComponent = () => {
        return (
            <div>
                <p className="antom-test-mode-warning">run in antom test mode</p>
                <p className="antom-test-mode-info">you can test with this card number : <span className="strong">4054695723100768</span> . expire date with this format : <span className="strong"> MM / YY</span> , such as <span className="strong">02 / 29</span>, CVC with any Three digits, such as <span className="strong">123</span></p>
            </div>
        )
    }

    const FormFields = () => {
        return (
            <div className="antom-cards">
                <div className="antom-cards-container">
                    <p>Card Information</p>
                </div>
                <div className="antom-cards-container">
                    <div className="antom-cards-container-item">
                        <div className="antom-cards-container-item-line">
                            <div className="card-cover">
                                {cardIcon && <img src={cardIcon} alt={`${cardType} icon`} />}
                            </div>
                            <input placeholder="Card number" id="antom-card-number" className="antom-card-number" value={cardNumber} onChange={handleCardNumberChange} ref={cardNumberInputRef} />
                        </div>
                        <div className="antom-cards-container-item-line antom-cards-container-item-error antom-card-number-error">

                        </div>
                    </div>
                </div>
                <div className="antom-cards-container">
                    <div className="antom-cards-container-item expire-date">
                        <div className="antom-cards-container-item-line">
                            <input placeholder="Exipre date" id="antom-expire-date" className="antom-expire-date" value={expireDate} onChange={handleExpireDateChange} ref={expireDateInputRef} />
                        </div>
                        <div className="antom-cards-container-item-line antom-cards-container-item-error antom-expire-date-error"></div>
                    </div>
                    <div className="antom-cards-container-item">
                        <div className="antom-cards-container-item-line">
                            <input placeholder="CVC" id="antom-cvc" className="antom-cvc" value={cardCvc} onChange={handleCardCvcChange} ref={cvcInputRef} />
                        </div>
                        <div className="antom-cards-container-item-line antom-cards-container-item-error antom-cvc-error"></div>
                    </div>
                </div>
                <div className="antom-cards-container">
                    <div className="antom-cards-container-item">
                        <div className="antom-cards-container-item-line">
                            <input placeholder="Holder name" id="antom-holder-name" className="antom-holder-name" value={cardHolderName} onChange={handleCardHolderName} ref={holderNameInputRef} />
                        </div>
                        <div className="antom-cards-container-item-line antom-cards-container-item-error antom-holder-name-error"></div>
                    </div>
                </div>

                {isTestMode && testWarningComponent()}
            </div>
        )

    }

    const WarningUserNotLogin = (props) => {
        const { login_in_message } = props.settings
        return (
            <p>{login_in_message}</p>
        )
    }

    return (
        <div>
            <FormFields />
        </div>

    );
};

export default CardPayFields;