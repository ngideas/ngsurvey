<?php
/**
 * The file defines the API class to handle settings management in NgSurvey
 *
 * Based on the WooCommerce settings API.
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes/abstracts
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The survey question base class.
 *
 * This is used to define base question class.
 *
 * @package    NgSurvey
 * @author     NgIdeas <support@ngideas.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link       https://ngideas.com
 * @since      1.0.0
 */
abstract class NgSurvey_Settings_API {
    
    /**
     * The unique identifier of this plugin. Must on lowercase alphanumeric [a-z0-9] and no special characters.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $type    The string used to uniquely identify this plugin.
     */
    public $name = 'ngsurvey_';
    
    /**
     * The unique ID of the settings page/section/plugin being displayed
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $type    The string used to uniquely identify this plugin.
     */
    public $id = '';
    
    /**
     * The title of this extension.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $type    The title of the plugin
     */
    public $title = '';
    
    /**
     * The list of settings fields defined by the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $options    The options data array.
     */
    protected $form_fields = array();
    
    
    /**
     * The options array encapsulates options that are applied to the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $options    The options data array.
     */
    protected $settings = null;
    
    /**
     * The posted settings data. When empty, $_POST data will be used.
     *
     * @var array
     */
    protected $data = array();
    
    /**
     * Define the base question type functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct ( $config = array() ) {
        
        if( !empty($config[ 'id' ]) ) {
            
            $this->id = $config[ 'id' ];
        }
        
        if( !empty($config[ 'title' ]) ) {
            
            $this->title = $config[ 'title' ];
        }
        
        if( !empty( $config[ 'form_fields' ] ) ) {
            $this->form_fields = array_merge( $this->form_fields, $config[ 'form_fields' ] );
        }
    }
    
    /**
     * Generate Settings HTML.
     *
     * Generate the HTML for the fields on the "settings" screen.
     *
     * @param array $form_fields (default: array()) Array of form fields.
     * @param bool  $echo Echo or return.
     * 
     * @since  1.0.0
     * @uses   method_exists()
     */
    public function generate_settings_html( $echo = true ) {
        if ( empty( $this->form_fields ) ) {
            $this->form_fields = $this->get_form_fields();
        }
        
        foreach ( $this->form_fields as $k => $v ) {
            $type = sanitize_key( $this->get_field_type( $v ) );
            
            if ( method_exists( $this, 'generate_' . $type . '_html' ) ) {
                $this->{'generate_' . $type . '_html'}( $k, $v );
            } else {
                $this->generate_text_html( $k, $v );
            }
        }
    }
    
    /**
     * Saves the given settings to the plugin settings
     *
     * @param array $data the form fields values that needs to be saved. If none send, POST values will be used.
     */
    public function save_settings( $form_fields = null ) {
        $data = $this->get_post_data();
        if ( empty( $data ) ) {
            return false;
        }
        
        if ( empty( $form_fields ) ) {
            $form_fields = $this->get_form_fields();
        }
        
        if( empty( $form_fields ) ) {
            return false;
        }
        
        if( empty( $this->settings ) ) {
            $this->init_settings();
        }
        
        $values = array();
        foreach ( $form_fields as $k => $form_field ) {
            $values[ $k ] = $this->get_field_value( $k, $form_field, $data );
        }
        
        update_option( $this->get_option_key(), $values );
        $this->settings = $values;
        
        do_action( 'ngsurvey_settings_saved' );
        
        return true;
    }
    
    
    /**
     * Gets the value of the given option of the plugin.
     *
     * Gets an option from the settings API, using defaults if necessary to prevent undefined notices.
     *
     * @param  string $key Option key.
     * @param  mixed  $empty_value Value when empty.
     * @return string The value specified for the option or a default value for the option.
     */
    public function get_option( $key, $empty_value = null ) {
        if ( empty( $this->settings ) ) {
            $this->init_settings();
        }
        
        // Get option default if unset.
        if ( ! isset( $this->settings[ $key ] ) ) {
            $form_fields            = $this->get_form_fields();
            $this->settings[ $key ] = isset( $form_fields[ $key ] ) ? $this->get_field_default( $form_fields[ $key ] ) : '';
        }
        
        if ( ! is_null( $empty_value ) && '' === $this->settings[ $key ] ) {
            $this->settings[ $key ] = $empty_value;
        }
        
        return $this->settings[ $key ];
    }
    
    /**
     * Initialise Settings.
     *
     * Store all settings in a single database entry
     * and make sure the $settings array is either the default
     * or the settings stored in the database.
     *
     * @since 1.0.0
     * @uses get_option(), add_option()
     */
    protected function init_settings() {
        $this->settings = get_option( $this->get_option_key(), null );
        
        // If there are no settings defined, use defaults.
        if ( ! is_array( $this->settings ) ) {
            $form_fields    = $this->get_form_fields();
            $this->settings = array_merge( array_fill_keys( array_keys( $form_fields ), '' ), wp_list_pluck( $form_fields, 'default' ) );
        }
    }
    
    /**
     * Return the name of the option in the WP DB.
     *
     * @since 2.6.0
     * @return string
     */
    protected function get_option_key() {
        return $this->name . $this->id . '_settings';
    }
    
    /**
     * Get the form fields after they are initialized.
     *
     * @return array of options
     */
    protected function get_form_fields() {
        return apply_filters( 'ngsurvey_settings_form_fields_' . $this->id, array_map( array( $this, 'set_defaults' ), $this->form_fields ) );
    }
    
    /**
     * Get a field's posted and validated value.
     *
     * @param string $key Field key.
     * @param array  $field Field array.
     * @param array  $post_data Posted data.
     * @return string
     */
    protected function get_field_value( $key, $field, $post_data = array() ) {
        $type      = $this->get_field_type( $field );
        $field_key = $this->get_field_key( $key );
        $post_data = empty( $post_data ) ? $_POST : $post_data;
        $value     = isset( $post_data[ $field_key ] ) ? $post_data[ $field_key ] : null;
        
        if ( isset( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ) {
            return call_user_func( $field['sanitize_callback'], $value );
        }
        
        // Look for a validate_FIELDID_field method for special handling.
        if ( is_callable( array( $this, 'validate_' . $key . '_field' ) ) ) {
            return $this->{'validate_' . $key . '_field'}( $key, $value );
        }
        
        // Look for a validate_FIELDTYPE_field method.
        if ( is_callable( array( $this, 'validate_' . $type . '_field' ) ) ) {
            return $this->{'validate_' . $type . '_field'}( $key, $value );
        }
        
        // Fallback to text.
        return $this->validate_text_field( $key, $value );
    }
    
    /**
     * Prefix key for settings.
     *
     * @param  string $key Field key.
     * @return string
     */
    protected function get_field_key( $key ) {
        return $this->name . $this->id . '_' . $key;
    }
    
    /**
     * Get a fields type. Defaults to "text" if not set.
     *
     * @param  array $field Field key.
     * @return string
     */
    protected function get_field_type( $field ) {
        return empty( $field['type'] ) ? 'text' : $field['type'];
    }
    
    /**
     * Get a fields default value. Defaults to "" if not set.
     *
     * @param  array $field Field key.
     * @return string
     */
    protected function get_field_default( $field ) {
        return empty( $field['default'] ) ? '' : $field['default'];
    }
    
    /**
     * Set default required properties for each field.
     *
     * @param array $field Setting field array.
     * @return array
     */
    protected function set_defaults( $field ) {
        if ( ! isset( $field['default'] ) ) {
            $field['default'] = '';
        }
        return $field;
    }
    
    /**
     * Validate Text Field.
     *
     * Make sure the data is escaped correctly, etc.
     *
     * @param  string $key Field key.
     * @param  string $value Posted Value.
     * @return string
     */
    protected function validate_text_field( $key, $value ) {
        $value = is_null( $value ) ? '' : $value;
        return wp_kses_post( trim( stripslashes( $value ) ) );
    }
    
    /**
     * Validate Password Field. No input sanitization is used to avoid corrupting passwords.
     *
     * @param  string $key Field key.
     * @param  string $value Posted Value.
     * @return string
     */
    protected function validate_password_field( $key, $value ) {
        $value = is_null( $value ) ? '' : $value;
        return trim( stripslashes( $value ) );
    }
    
    /**
     * Validate Textarea Field.
     *
     * @param  string $key Field key.
     * @param  string $value Posted Value.
     * @return string
     */
    protected function validate_textarea_field( $key, $value ) {
        $value = is_null( $value ) ? '' : $value;
        return wp_kses(
            trim( stripslashes( $value ) ),
            array_merge(
                array(
                    'iframe' => array(
                        'src'   => true,
                        'style' => true,
                        'id'    => true,
                        'class' => true,
                    ),
                ),
                wp_kses_allowed_html( 'post' )
                )
            );
    }
    
    /**
     * Validate Checkbox Field.
     *
     * If not set, return "no", otherwise return "yes".
     *
     * @param  string $key Field key.
     * @param  string $value Posted Value.
     * @return string
     */
    protected function validate_checkbox_field( $key, $value ) {
        return ! is_null( $value ) ? 'yes' : 'no';
    }
    
    /**
     * Validate Select Field.
     *
     * @param  string $key Field key.
     * @param  string $value Posted Value.
     * @return string
     */
    protected function validate_select_field( $key, $value ) {
        $value = is_null( $value ) ? '' : $value;
        return $this->clean( stripslashes( $value ) );
    }
    
    /**
     * Validate Multiselect Field.
     *
     * @param  string $key Field key.
     * @param  string $value Posted Value.
     * @return string|array
     */
    protected function validate_multiselect_field( $key, $value ) {
        return is_array( $value ) ? array_map( array( $this, 'clean' ), array_map( 'stripslashes', $value ) ) : '';
    }
    
    /**
     * Cleans the variable value
     *
     * @param array|string $var value to clean
     *
     * @return array|string return cleaned value
     */
    protected function clean( $var ) {
        if ( is_array( $var ) ) {
            return array_map( array( $this, 'clean' ), $var );
        } else {
            return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
        }
    }
    
    /**
     * Returns the POSTed data, to be used to save the settings.
     *
     * @return array
     */
    protected function get_post_data() {
        if ( ! empty( $this->data ) && is_array( $this->data ) ) {
            return $this->data;
        }
        return $_POST; // WPCS: CSRF ok, input var ok.
    }
    
    /**
     * Generate Text Input HTML.
     *
     * @param string $key Field key.
     * @param array  $data Field data.
     * @since  1.0.0
     * @return string
     */
    protected function generate_title_html( $key, $data ) {
        $defaults  = array(
            'title'             => '',
            'class'             => '',
            'css'               => '',
            'type'              => 'title',
            'desc_tip'          => false,
            'description'       => '',
            'custom_attributes' => array(),
        );
        
        $data = wp_parse_args( $data, $defaults );
        ?>
		<div class="mb-3 mt-3">
			<h4 class="<?php echo esc_attr( $data['class'] ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>"><?php echo wp_kses_post( $data['title'] );?></h4>
			<div><?php echo wp_kses_post( $data['description'] );?></div>
		</div>
		<?php
	}
	
	/**
	 * Generate Text Input HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	protected function generate_text_html( $key, $data ) {
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
	    );
	    
	    $data = wp_parse_args( $data, $defaults );
	    ?>
		<div class="row mb-3">
			<label for="<?php echo esc_attr( $field_key ); ?>" class="col-sm-2 col-form-label">
				<?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // Data sanitized inside the function ?>
				<legend class="screen-reader-text visually-hidden"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			</label>
			<div class="col-sm-10">
				<input 
					type="<?php echo esc_attr( $data['type'] ); ?>" 
					class="form-control <?php echo esc_attr( $data['class'] ); ?>" 
					name="<?php echo esc_attr( $field_key ); ?>" 
					id="<?php echo esc_attr( $field_key ); ?>" 
					style="<?php echo esc_attr( $data['css'] ); ?>" 
					value="<?php echo esc_attr( $this->get_option( $key ) ); ?>" 
					placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" 
					<?php disabled( $data['disabled'], true ); ?> 
					<?php echo $this->get_custom_attribute_html( $data ); // attribute data escaped inside the funtion?>/>
				<?php echo $this->get_description_html( $data ); // html escaped inside the function with wp_kses_post?>
			</div>
		</div>
		<?php
	}

	/**
	 * Generate Password Input HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	protected function generate_password_html( $key, $data ) {
		$data['type'] = 'password';
		return $this->generate_text_html( $key, $data );
	}

	/**
	 * Generate Color Picker Input HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	protected function generate_color_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );
		?>
		<div class="row mb-3">
			<label for="<?php echo esc_attr( $field_key ); ?>" class="col-sm-2 col-form-label">
				<?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // Data sanitized inside the function ?>
				<legend class="screen-reader-text visually-hidden"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			</label>
			<div class="col-sm-10">
				<input 
					type="<?php echo esc_attr( $data['type'] ); ?>" 
					class="form-control form-control-color <?php echo esc_attr( $data['class'] ); ?>" 
					name="<?php echo esc_attr( $field_key ); ?>" 
					id="<?php echo esc_attr( $field_key ); ?>" 
					style="<?php echo esc_attr( $data['css'] ); ?>" 
					value="<?php echo esc_attr( $this->get_option( $key ) ); ?>" 
					<?php disabled( $data['disabled'], true ); ?> 
					<?php echo $this->get_custom_attribute_html( $data ); // attribute data escaped inside the funtion?>/>
				<?php echo $this->get_description_html( $data ); // html escaped inside the function with wp_kses_post?>
			</div>
		</div>
		<?php
	}

	/**
	 * Generate Textarea HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	protected function generate_textarea_html( $key, $data ) {
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
		);

		$data = wp_parse_args( $data, $defaults );
		?>
		<div class="row mb-3">
			<label for="<?php echo esc_attr( $field_key ); ?>" class="col-sm-2 col-form-label">
				<?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // Data sanitized inside the function ?>
				<legend class="screen-reader-text visually-hidden"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			</label>
			<div class="col-sm-10">
				<textarea 
					rows="3" 
					cols="20" 
					class="form-control <?php echo esc_attr( $data['class'] ); ?>" 
					type="<?php echo esc_attr( $data['type'] ); ?>" 
					name="<?php echo esc_attr( $field_key ); ?>" 
					id="<?php echo esc_attr( $field_key ); ?>" 
					style="<?php echo esc_attr( $data['css'] ); ?>" 
					placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" 
					<?php disabled( $data['disabled'], true ); ?> 
					<?php echo $this->get_custom_attribute_html( $data ); // attributes html escaped inside the function?>><?php echo esc_textarea( $this->get_option( $key ) ); ?></textarea>
				<?php echo $this->get_description_html( $data ); // html escaped inside the function with wp_kses_post?>
			</div>
		</div>
		<?php
	}

	/**
	 * Generate Checkbox HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	protected function generate_checkbox_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'label'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		if ( ! $data['label'] ) {
			$data['label'] = $data['title'];
		}
		?>
		<div class="row mb-3">
			<label for="<?php echo esc_attr( $field_key ); ?>" class="col-sm-2 col-form-label">
				<?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // Data sanitized inside the function ?>
				<legend class="screen-reader-text visually-hidden"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			</label>
			<div class="col-sm-10">
				<div class="form-check">
    				<input
    					type="checkbox" 
    				    <?php disabled( $data['disabled'], true ); ?> 
    				    class="form-check-input <?php echo esc_attr( $data['class'] ); ?>" 
    				    name="<?php echo esc_attr( $field_key ); ?>" 
    				    id="<?php echo esc_attr( $field_key ); ?>" 
    				    style="<?php echo esc_attr( $data['css'] ); ?>" 
    				    value="1" 
    				    <?php checked( $this->get_option( $key ), 'yes' ); ?> 
    				    <?php echo $this->get_custom_attribute_html( $data ); //attribute html escaped inside the function?>/> 
					<label class="form-check-label" for="<?php echo esc_attr( $field_key ); ?>">
						<?php echo wp_kses_post( $data['label'] ); ?>
					</label>
				</div>
				<?php echo $this->get_description_html( $data ); // html is escaped inside the function with wp_kses_post?>
			</div>
		</div>
		<?php
	}

	/**
	 * Generate Select HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	protected function generate_select_html( $key, $data ) {
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
		);

		$data = wp_parse_args( $data, $defaults );
		?>
		<div class="row mb-3">
			<label for="<?php echo esc_attr( $field_key ); ?>" class="col-sm-2 col-form-label">
				<?php echo esc_html( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // Data sanitized inside the function ?>
				<legend class="screen-reader-text visually-hidden"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			</label>
			<div class="col-sm-10">
				<select 
					class="form-select <?php echo esc_attr( $data['class'] ); ?>" 
					name="<?php echo esc_attr( $field_key ); ?>" 
					id="<?php echo esc_attr( $field_key ); ?>" 
					style="<?php echo esc_attr( $data['css'] ); ?>" 
					<?php disabled( $data['disabled'], true ); ?> 
					<?php echo $this->get_custom_attribute_html( $data );?>>
					<?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
						<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( (string) $option_key, esc_attr( $this->get_option( $key ) ) ); ?>><?php echo esc_html( $option_value ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php echo $this->get_description_html( $data ); //html is escaped inside the function with wp_kses_post?>
			</div>
		</div>
		<?php
	}

	/**
	 * Generate Multiselect HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	protected function generate_multiselect_html( $key, $data ) {
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
			'select_buttons'    => false,
			'options'           => array(),
		);

		$data  = wp_parse_args( $data, $defaults );
		$value = (array) $this->get_option( $key, array() );
		?>
		<div class="row mb-3">
			<label for="<?php echo esc_attr( $field_key ); ?>" class="col-sm-2 col-form-label">
				<?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // Data sanitized inside the function ?>
				<legend class="screen-reader-text visually-hidden"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
			</label>
			<div class="col-sm-10">
				<select 
					multiple="multiple" 
					class="form-select multiselect <?php echo esc_attr( $data['class'] ); ?>" 
					name="<?php echo esc_attr( $field_key ); ?>[]" 
					id="<?php echo esc_attr( $field_key ); ?>" 
					style="<?php echo esc_attr( $data['css'] ); ?>" 
					<?php disabled( $data['disabled'], true ); ?> 
					<?php echo $this->get_custom_attribute_html( $data );?>>
					<?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
						<option value="<?php echo esc_attr( $option_key ); ?>" <?php selected( in_array( (string) $option_key, $value, true ), true ); ?>><?php echo esc_html( $option_value ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php echo $this->get_description_html( $data ); //html is escaped inside the function with wp_kses_post?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Get HTML for descriptions.
	 *
	 * @param  array $data Data for the description.
	 * @return string
	 */
	protected function get_description_html( $data ) {
	    return !empty($data['description']) ? '<div class="form-text">' . wp_kses_post( $data['description'] ) .'</div>' : '';
	}
	
	/**
	 * Get custom attributes.
	 *
	 * @param  array $data Field data.
	 * @return string
	 */
	protected function get_custom_attribute_html( $data ) {
	    $custom_attributes = array();
	    
	    if ( ! empty( $data['custom_attributes'] ) && is_array( $data['custom_attributes'] ) ) {
	        foreach ( $data['custom_attributes'] as $attribute => $attribute_value ) {
	            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
	        }
	    }
	    
	    return implode( ' ', $custom_attributes );
	}
	
	/**
	 * Get HTML for tooltips.
	 *
	 * @param  array $data Data for the tooltip.
	 * @return string
	 */
	protected function get_tooltip_html( $data ) {
	    if ( true === $data['desc_tip'] ) {
	        $tip = $data['description'];
	    } elseif ( ! empty( $data['desc_tip'] ) ) {
	        $tip = $data['desc_tip'];
	    } else {
	        $tip = '';
	    }
	    
	    return $tip ? $this->help_tip( $tip, true ) : '';
	}
	
	/**
	 * Gets the formatted tooltip
	 *
	 * @param string $tip tooltip value
	 * @param boolean $allow_html
	 *
	 * @return string formatted tooltip
	 */
	protected function help_tip( $tip, $allow_html = false ) {
	    if ( $allow_html ) {
	        $tip = $this->sanitize_tooltip( $tip );
	    } else {
	        $tip = esc_attr( $tip );
	    }
	    
	    return '<span class="ngsurvey-help-tip" data-tip="' . $tip . '"></span>';
	}
	
	/**
	 * Cleans all unneaded tags
	 *
	 * @param string $var value to sanitize
	 *
	 * @return string sanitised value
	 */
	protected function sanitize_tooltip( $var ) {
	    return htmlspecialchars(
	        wp_kses(
	            html_entity_decode( $var ),
	            array(
	                'br'     => array(),
	                'em'     => array(),
	                'strong' => array(),
	                'small'  => array(),
	                'span'   => array(),
	                'ul'     => array(),
	                'li'     => array(),
	                'ol'     => array(),
	                'p'      => array(),
	            )
	            )
	        );
	}
	
	/**
	 * Throws error and dies. Used in ajax response.
	 *
	 * If no error code or message is provided, default unauthorised error will be thrown
	 */
	protected function raise_error( $code = '001', $message = 'Unauthorised access' ) {
	    $error = new WP_Error( $code, __( $message, 'ngsurvey' ) );
	    wp_send_json_error( $error );
	}
}
