import React, { useEffect } from 'react';
import { decodeEntities } from '@wordpress/html-entities';
import TestModeWarning from './test-mode-warning';
import $ from 'jquery';

const DefaultContent = (props) => {
    const { eventRegistration, settings } = props;
    let isTestMode = settings.is_test_mode
    const { onPaymentProcessing, onCheckoutAfterProcessingWithSuccess, onCheckoutAfterProcessingWithError
    } = eventRegistration;

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

    const isAntomWalletChecked = () => {
        let id = "radio-control-wc-payment-method-options-" + settings.payment_method
        return $('#' + id).is(':checked')
    }


    useEffect(() => {
        const showAntomRequestAnimate = onPaymentProcessing(() => {
            if (isAntomWalletChecked()) {
                show_antom_loading_mask(settings.animate_setting.request_to_antom_payments_gateway)
            }
        })

        return () => {
            showAntomRequestAnimate()
        }
    }, [onPaymentProcessing])

    useEffect(() => {
        const afterProcessSuccess = onCheckoutAfterProcessingWithSuccess(() => {
            if (isAntomWalletChecked()) {
                //show request to antom payments gateway message.
                show_antom_loading_mask(settings.animate_setting.redirect_to_antom_loading_description)
            }

        })

        return () => {
            afterProcessSuccess();
        };
    }, [onCheckoutAfterProcessingWithSuccess])

    useEffect(() => {
        const afterProcessError = onCheckoutAfterProcessingWithError(() => {
            if (isAntomWalletChecked()) {
                hide_antom_loading_mask()
            }
        })

        return () => {
            afterProcessError();
        };
    }, [onCheckoutAfterProcessingWithError])


    const Content = () => {
        return decodeEntities(settings.description || '')
    }

    return (
        <div>
            <Content />
            {isTestMode && <TestModeWarning is_test_mode={isTestMode} />}
        </div>
    )
}

export default DefaultContent;