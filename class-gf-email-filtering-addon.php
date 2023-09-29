<?php

use Gravity_Forms\Gravity_Forms\Settings\Fields\Textarea;

GFForms::include_addon_framework();

class GFEmailFilteringAddOn extends GFAddOn
{
    protected $_version = GF_EMAIL_FILTERING_ADDON_VERSION;
    protected $_min_gravityforms_version = '1.9.16';
    protected $_slug = 'gravityforms-addon-email-filtering';
    protected $_path = 'gravityforms-addon-email-filtering/gravityforms-addon-email-filtering.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Email Filtering';
    protected $_short_title = 'Email Filtering Add-On';

    /**
     * Defines the capability needed to access the Add-On settings page.
     *
     * @since  2.5.4
     * @access protected
     * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
     */
    protected $_capabilities_settings_page = 'gravityforms_email_filtering';

    /**
     * Defines the capability needed to access the Add-On form settings page.
     *
     * @since  2.5.4
     * @access protected
     * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
     */
    protected $_capabilities_form_settings = 'gravityforms_email_filtering';

    /**
     * Defines the capability needed to uninstall the Add-On.
     *
     * @since  2.5.4
     * @access protected
     * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
     */
    protected $_capabilities_uninstall = 'gravityforms_email_filtering_uninstall';

    /**
     * Defines the capabilities needed for the Post Creation Add-On
     *
     * @since  2.5.4
     * @access protected
     * @var    array $_capabilities The capabilities needed for the Add-On
     */
    protected $_capabilities = ['gravityforms_email_filtering', 'gravityforms_email_filtering_uninstall'];

    private static $_instance = null;

	public $denylist = [];
	public $settings = [];
	public $denylist_tooltip = 'Using one entry per line, enter a list of domains or email addresses to filter. You may also include wildcard notations to block top-level domains (e.g., *.ru).';
	public $validation_tooltip = 'Please enter a default error message if a denied email is submitted. ';

    /**
     * Get an instance of this class.
     */
    public static function get_instance(): GFEmailFilteringAddOn
    {
        if (self::$_instance == null) {
            self::$_instance = new GFEmailFilteringAddOn();
        }

        return self::$_instance;
    }

	/**
	 * Add tasks or filters here that you want to perform both in the backend and frontend and for ajax requests
	 */
	public function init(): void {
		parent::init();

		$this->settings = get_option('gravityformsaddon_' . $this->_slug . '_settings');
		$this->denylist = $this->getDenylist();
	}

    /**
     * Add tasks or filters here that you want to perform only in admin.
     */
    public function init_admin(): void
    {
        parent::init_admin();

        add_action('gform_editor_js', [$this, 'gform_editor_js']);
        add_action('gform_field_advanced_settings', [$this, 'gform_field_advanced_settings'], 10, 2);
        add_filter('gform_tooltips', [$this, 'gform_tooltips']);
    }

    /**
     * Add tasks or filters here that you want to perform only in the front end.
     */
    public function init_frontend(): void
    {
        parent::init_frontend();

        add_filter('gform_validation', [$this, 'gform_validation']);
    }

    /**
     * Add the additional settings to the plugin settings page.
     */
    public function plugin_settings_fields(): array
    {
        return [
            [
                'title' => __('Email Filtering Global Settings', 'gf-email-filtering-addon'),
                'description' => __('If a filtered email is used in any email field, the form will error on submission. You can also globally define a list of filtered emails and/or domains and a custom validation message if a filtered email is submitted. These settings can be overridden on individual email fields in the advanced settings.', 'gf-email-filtering-addon'),
                'fields' => [
                    [
                        'label' => __('Global Email Filters', 'gf-email-filtering-addon'),
                        'type' => 'textarea',
                        'name' => 'default_email_denylist',
                        'tooltip' => __("{$this->denylist_tooltip} This setting can be overridden on individual email fields in the advanced settings.", 'gf-email-filtering-addon'),
                        'class' => 'medium',
						'save_callback' => [$this, 'saveDenylist'],
                    ],
                    [
                        'label' => __('Global Validation Message', 'gf-email-filtering-addon'),
                        'type' => 'text',
                        'name' => 'default_email_denylist_error_msg',
                        'tooltip' => __("{$this->validation_tooltip} This setting can be overridden on individual email fields in the advanced settings.", 'gf-email-filtering-addon'),
                        'class' => 'medium',
                    ],
                ],
            ],
        ];
    }

    /**
     * Add setting to the email field's advanced settings.
     *
     * @param integer $position Specifies the position that the settings will be displayed.
     * @param integer $form_id  The ID of the form from which the entry value was submitted.
     */
    public function gform_field_advanced_settings(int $position, ?int $form_id = null): void
    {
        // Create settings on position 50 (right after Field Label).
        if ($position !== 50) {
            return;
        }

        // Get settings for placeholder text.
        if ($this->settings) {
            $denylist_placeholder = __('Global Email Filters: ', 'gf-email-filtering-addon') . "\r\n" . implode("\r\n", $this->denylist);
            $denylist_msg = __('Global Error Message: ', 'gf-email-filtering-addon') . $this->settings['default_email_denylist_error_msg'];
        } else {
            $denylist_placeholder = __('Set Email Filters', 'gf-email-filtering-addon');
            $denylist_msg = __('Set Error Message', 'gf-email-filtering-addon');
        }

        $denylist_placeholder = esc_attr($denylist_placeholder);
        $denylist_msg = esc_attr($denylist_msg);
        $filter_label = esc_html('Filtered Emails', 'gf-email-filtering-addon');
        $filter_label .= gform_tooltip('form_field_email_filtering', '', true);
        $message_label = esc_html('Filtered Emails Validation Message', 'gf-email-filtering-addon');
        $message_label .= gform_tooltip('form_field_email_filtering_validation', '', true);

        echo <<<HTML
			<li class="email_filtering_setting field_setting">
				<label for="field_email_filtering">
					$filter_label
				</label>
				<textarea id="field_email_filtering" class="fieldwidth-3" size="35" onkeyup="SetFieldProperty('email_filtering', this.value);" placeholder="$denylist_placeholder"></textarea>
			</li>

			<li class="email_filtering_validation field_setting">
				<label for="field_email_filtering_validation">
					$message_label
				</label>
				<input type="text" id="field_email_filtering_validation" class="fieldwidth-3" size="35" onkeyup="SetFieldProperty('email_filtering_validation', this.value);" placeholder="$denylist_msg">
			</li>
		HTML;
    }

    /**
     * Add the additional tooltips to the new fields.
     */
    public function gform_tooltips(array $tooltips): array
    {
        $tooltips['form_field_email_filtering'] = __("{$this->denylist_tooltip} This will override the globally-defined email filters. Enter 'none' to bypass the global setting and allow all email addresses.", 'gf-email-filtering-addon');
        $tooltips['form_field_email_filtering_validation'] = __("{$this->validation_tooltip} This will override the globally-defined error message.", 'gf-email-filtering-addon');
        return $tooltips;
    }

    /**
     * Inject JavaScript into the form editor page.
     */
    public function gform_editor_js(): void
    {
        echo <<<HTML
			<script>
				jQuery(document).ready(function($) {
					// Alter the setting offered for the email input type.
					// This will show all fields that the email field shows plus the custom settings
					fieldSettings["email"] += ", .email_filtering_setting, .email_filtering_validation";

					// Binding to the load field settings event to initialize custom settings.
					$(document).bind("gform_load_field_settings", function(event, field, form){
						$("#field_email_filtering").val(field["email_filtering"]);
						$("#field_email_filtering_validation").val(field["email_filtering_validation"]);
					});
				});
			</script>
		HTML;
    }

    /**
     * Add email filtering to gforms validation function.
     * @see https://docs.gravityforms.com/using-gform-validation-hook/
     */
    public function gform_validation(array $validation_result): array
    {
        // Collect form results.
        $form = $validation_result['form'];

        // Loop through results.
        foreach ($form['fields'] as &$field) {
            // If this is not an email field, skip.
            if (RGFormsModel::get_input_type($field) !== 'email') {
                continue;
            }

            // If the field is hidden by GF conditional logic, skip.
            if (RGFormsModel::is_field_hidden($form, $field, [])) {
                continue;
            }

            // Collect banned domains from the form and clean up.
            if (! empty($field['email_filtering'])) {
				$denylist = $this->getDenylist($field['email_filtering']);
            } else {
				$denylist = $this->denylist;
			}

            // Get the domain from user entered email.
            $email = $this->clean_string(rgpost("input_{$field['id']}"));
            $domain = $this->clean_string(rgar(explode('@', $email), 1));
            $tld = strrchr($domain, '.');

            /**
             * Filter to allow third-party plugins short circuit filtering validation.
             *
             * @since 2.5.1
             * @param bool   false      Default value.
             * @param array  $field     The Field Object.
             * @param string $email     The email entered in the input.
             * @param string $domain    The full domain entered in the input.
             * @param string $tld       The top level domain entered in the input.
             * @param array  $denylist  List of the blocked emailed/domains.
             */
            if (apply_filters('gf_email_filtering_validation_short_circuit', false, $field, $email, $domain, $tld, $denylist)) {
                continue;
            }

            // Create array of banned domains.
            $denylist = array_map(function ($item) {
				return str_replace('*', '', $item);
			}, $denylist);

            $denylist = array_filter($denylist);

            // No filtered emails, skip.
            if (empty($denylist)) {
                continue;
            }

            // If the email, domain or tld isn't denied, skip.
            if (! in_array($email, $denylist, true) && ! in_array($domain, $denylist, true) && ! in_array($tld, $denylist, true)) {
                continue;
            }

            /**
             * Filter to allow third party plugins to set the email filtering validation.
             *
             * @since 2.5.1
             * @param bool   false      Default value.
             * @param array  $field     The Field Object.
             * @param string $email     The email entered in the input.
             * @param string $domain    The full domain entered in the input.
             * @param string $tld       The top level domain entered in the input.
             * @param array  $denylist  List of the blocked emailed/domains.
             */
            $validation_result['is_valid'] = apply_filters('gf_email_filtering_is_valid', false, $field, $email, $domain, $tld, $denylist);
            $field['failed_validation'] = true;

            // Set the validation message or use the default.
            if (! empty($field['email_filtering_validation'])) {
                $validation_message = $field['email_filtering_validation'];
            } elseif ($this->settings) {
                $validation_message = $this->settings['default_email_denylist_error_msg'];
            } else {
                $validation_message = __('Sorry, the email address entered is not eligible for this form.', 'gf-email-filtering-addon');
            }

            /**
             * Filter to allow third party plugins to set the email filtering validation.
             *
             * @since 2.5.1
             * @param bool   $validation_message The custom validation method.
             * @param array  $field              The Field Object.
             * @param string $email              The email entered in the input.
             * @param string $domain             The full domain entered in the input.
             * @param string $tld                The top level domain entered in the input.
             * @param array  $denylist           List of the blocked emailed/domains.
             */
            $field['validation_message'] = apply_filters('gf_email_filtering_validation_message', $validation_message, $field, $email, $domain, $tld, $denylist);
        }

        $validation_result['form'] = $form;

        return $validation_result;
    }

	/**
	 * Normalize the denylist setting upon save
	 */
	public function saveDenylist(Textarea $field, string $setting): string {
		$denylist = str_replace(',', "\r\n", $setting);
		$denylist = explode("\r\n", $denylist);
		$denylist = array_map([$this, 'clean_string'], $denylist);

		return implode("\r\n", $denylist);
	}

	/**
	 * Get the denylist in an array.
	 */
	public function getDenylist(string $denylist = ''): array {
		$denylist = $denylist ?: $this->settings['default_email_denylist'];
		$denylist = str_replace(',', "\r\n", $denylist);
		$denylist = explode("\r\n", $denylist);

		return array_map([$this, 'clean_string'], $denylist);
	}

    /**
     * Convert a string to lowercase and remove extra whitespace.
     */
    protected function clean_string(string $string): string
    {
        return strtolower(trim($string));
    }
}
