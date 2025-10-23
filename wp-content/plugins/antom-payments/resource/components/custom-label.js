import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';

const CustomLabel = (props) => {
    const { label, onChange } = props;

    return (
        <RichText
            tagName="label"
            value={label}
            onChange={onChange}
            {...useBlockProps()}
        />
    );
};

export default CustomLabel;