import { __ } from '@wordpress/i18n';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';
import CustomLabel from './components/custom-label';
import DefaultContent from './components/default-content'

const settings = getSetting('antom_card_data', {});

const defaultLabel = __(
    'V/M Card',
    'antom-payments'
);


let label_text = '<span class="wc-block-components-payment-method-label" style="display:flex;flex-direction:row;align-items:center">';
label_text += decodeEntities(settings.title) || defaultLabel;

if (Array.isArray(settings.icon) && settings.icon.length > 0) {
    label_text += '<span class="antom-payment-block-icons antom-payment-icons">'
    settings.icon.forEach(item => {
        label_text += '<img class="antom-payment-icons-item" src="' + item + '"/>'
    })
    label_text += '</span>'
}

label_text += '</span>'



/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = (props) => {
    const { PaymentMethodLabel } = props.components;
    return <PaymentMethodLabel text={label} />;
};

/**
 * antom_card payment method config object.
 */
const antom_card = {
    name: "antom_card",
    label: <CustomLabel label={label_text} />,
    content: <DefaultContent settings={settings} />,
    edit: <DefaultContent settings={settings} />,
    canMakePayment: () => true,
    ariaLabel: label_text,
    paymentMethodId: 'antom_card',
    supports: {
        features: settings.supports,
    },
};

registerPaymentMethod(antom_card);
