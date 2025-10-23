
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';
import { __ } from '@wordpress/i18n';
import CustomLabel from './components/custom-label';
import DefaultContent from './components/default-content'

const settings = getSetting( 'antom_dana_data', {} );

const defaultLabel = __(
	'Dana',
	'antom-payments'
);

let label_text = '<span class="wc-block-components-payment-method-label" style="display:flex;flex-direction:row;align-items:center">';
label_text += decodeEntities(settings.title) || defaultLabel;

if (typeof settings.icon != 'undefined' && settings.icon.length > 0) {
	label_text += '<span class="antom-payment-block-icons antom-payment-icons">'
	settings.icon.forEach(item => {
		label_text += '<img class="antom-payment-icons-item" src="' + item + '"/>'
	})
	label_text += '</span>'
}

label_text += '</span>'


/**
 * antom_dana payment method config object.
 */
const antom_dana = {
	name: "antom_dana",
	label: <CustomLabel label={label_text} />,
	content: <DefaultContent settings={settings} />,
	edit: <DefaultContent settings={settings} />,
	canMakePayment: () => true,
	ariaLabel: label_text,
	paymentMethodId:'antom_dana',
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( antom_dana );
