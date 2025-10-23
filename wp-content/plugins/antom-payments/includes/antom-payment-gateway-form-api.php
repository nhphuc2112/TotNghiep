<?php

/**
 * Antom_Payment_Gateways_Form_Api
 *
 * @user Antom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Antom_Payment_Gateways_Form_Api extends WC_Settings_API {
	/**
	 * Overwrite get_option_key to load core settings field from antom payment gateways
	 *
	 * @return string
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function get_option_key() {
		return Antom_Payment_Gateways_State::ANTOM_SETTING_PREFIX
				. Antom_Payment_Gateways_State::ANTOM_CORE_SETTING_FIELD;
	}

	/**
	 * Overwrite init settings to load core settings from options
	 *
	 * @since: 1.0.0
	 * @author:Antom
	 */
	public function init_settings() {
		$settings = antom_load_core_setting();

		if ( 1 === intval( $settings['test_mode'] ) ) {
			$settings['test_mode']           = 'yes';
			$settings['settlement_currency'] = $settings['test_currency'];
		} else {
			$settings['settlement_currency'] = $settings['prod_currency'];
		}

		$this->settings = $settings;
	}

	public function generate_text_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'tr_class'          => '',
			'warning'           => '',
			'warning_class'     => '',
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top" class="<?php echo esc_attr( $data['tr_class'] ); ?>">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo esc_html( $this->get_tooltip_html( $data ) ); // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp">
				<fieldset
				<?php
				if ( $data['warning'] ) {
					echo esc_attr( 'class=antom-warning-input' );}
				?>
				>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" value="<?php echo esc_attr( $this->get_option( $key ) ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo esc_html( $this->get_custom_attribute_html( $data ) ); // WPCS: XSS ok. ?> />
					<?php
					if ( $data['warning'] ) {
						?>
						<div class="<?php echo esc_attr( $data['warning_class'] ); ?>"><?php echo esc_html( $data['warning'] ); ?></div>
						<?php
					}
					?>
				</fieldset>
				<?php echo wp_kses_post( $this->get_description_html( $data ) ); // WPCS: XSS ok. ?>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * Generate Select HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	public function generate_select_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'options'           => array(),
			'warning'           => '',
			'warning_class'     => '',
			'tr_class'          => '',
		);

		$data  = wp_parse_args( $data, $defaults );
		$value = $this->get_option( $key );

		ob_start();
		?>
		<tr valign="top" class="<?php echo esc_attr( $data['tr_class'] ); ?>">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo esc_html( $this->get_tooltip_html( $data ) ); // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp">
				<fieldset 
				<?php
				if ( $data['warning'] ) {
					echo esc_attr( 'class=antom-warning-select' );}
				?>
				>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<select class="select <?php echo esc_attr( $data['class'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo esc_html( $this->get_custom_attribute_html( $data ) ); // WPCS: XSS ok. ?>>
						<?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
							<?php if ( is_array( $option_value ) ) : ?>
								<optgroup label="<?php echo esc_attr( $option_key ); ?>">
									<?php foreach ( $option_value as $option_key_inner => $option_value_inner ) : ?>
										<option value="<?php echo esc_attr( $option_key_inner ); ?>" <?php selected( (string) $option_key_inner, esc_attr( $value ) ); ?>><?php echo esc_html( $option_value_inner ); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php else : ?>
								<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( (string) $option_key, esc_attr( $value ) ); ?>><?php echo esc_html( $option_value ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
					<?php
					if ( $data['warning'] ) {
						?>
						<div class="<?php echo esc_attr( $data['warning_class'] ); ?>"><?php echo esc_html( $data['warning'] ); ?></div>
						<?php
					}
					?>
					<?php echo esc_html( $this->get_description_html( $data ) ); // WPCS: XSS ok. ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * Generate Textarea HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	public function generate_textarea_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'tr_class'          => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top" class="<?php echo esc_attr( $data['tr_class'] ); ?>">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo esc_html( $this->get_tooltip_html( $data ) ); // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<textarea rows="3" cols="20" class="input-text wide-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo esc_html( $this->get_custom_attribute_html( $data ) ); // WPCS: XSS ok. ?>><?php echo esc_textarea( $this->get_option( $key ) ); ?></textarea>
					<?php echo esc_html( $this->get_description_html( $data ) ); // WPCS: XSS ok. ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}


	public function generate_paragraph_copy_html( $key, $data ) {

		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'tr_class'          => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );
		ob_start();
		?>
		<tr valign="top" class="<?php echo esc_attr( $data['tr_class'] ); ?>">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?><?php echo wp_kses_post( $this->get_tooltip_html( $data ) ); // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<p class="antom-paragraph"><?php echo esc_html( $data['paragraph_text'] ); ?>
						<a class="copy-to-clipboard"
							data-clipboard-text="<?php echo esc_html( $data['paragraph_text'] ); ?>">
							<img class="copy-icon"
								src="<?php echo esc_html( ANTOM_PAYMENT_GATEWAYS_URL ) . 'assets/images/copy.svg'; ?>"/>
						</a>
						<span class="copy-result" style="margin-left:10px;color:#ccc"></span>
					</p>
				</fieldset>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}
}
