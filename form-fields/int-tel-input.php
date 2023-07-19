<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once(__DIR__ . '/../vendor/autoload.php');

use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;

/**
 * Elementor Form Field - Credit Card Number
 *
 * Add a new "Credit Card Number" field to Elementor form widget.
 *
 * @since 1.0.0
 */
class Elementor_Int_Tel_Input_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base
{

    public $plugin;

    public $depended_scripts = ['int-tel-input-script-handle'];

    public $depended_styles = [
        'int-tel-input-style-handle',
        'bmg-int-tel-input-style-handle',
    ];

    /**
     * Field constructor.
     *
     * Used to add a script to the Elementor editor preview.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function __construct($plugin)
    {
        parent::__construct();
        $this->plugin = $plugin;
        add_action('elementor/preview/init', [$this, 'editor_preview_footer']);
    }

    /**
     * Get field type.
     *
     * Retrieve credit card number field unique ID.
     *
     * @since 1.0.0
     * @access public
     * @return string Field type.
     */
    public function get_type()
    {
        return 'int-tel-input';
    }

    /**
     * Get field name.
     *
     * Retrieve credit card number field label.
     *
     * @since 1.0.0
     * @access public
     * @return string Field name.
     */
    public function get_name()
    {
        return esc_html__('Int Tel Input', 'elementor-form-int-tel-input-field');
    }

    /**
     * Render field output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access public
     * @param mixed $item
     * @param mixed $item_index
     * @param ElementorPro\Modules\Forms\Widgets\Form $form
     * @return void
     */
    public function render($item, $item_index, $form)
    {
        $form_id = $form->get_id();

        $form->add_render_attribute(
            'input' . $item_index,
            [
                'class' => 'elementor-field-textual',
                'for' => $form_id . $item_index,
                'type' => 'tel',
                'inputmode' => 'numeric',
                'maxlength' => '19',
            ]
        );

        $utils = plugins_url('assets/js/utils.js', $this->plugin);
        echo '<input ' . $form->get_render_attribute_string('input' . $item_index) . '>';

        $options = [
            "utilsScript" => $utils,
            "allowDropdown" => $item['allow-dropdown'],
            "autoInsertDialCode" => $item['auto-insert-dial-code'],
            "autoPlaceholder" => $item['auto-placeholder'],
            "formatOnDisplay" => $item['format-on-display'],
            "separateDialCode" => $item['separate-dial-code'],
            "showFlags" => $item['show-flags'],
        ];

        empty($item['initial-country']) ?: $options['initialCountry'] = $item['initial-country'];
        empty($item['exclude-countries']) ?: $options['excludeCountries'] = explode(',', $item['exclude-countries']);
        empty($item['preferred-countries']) ?: $options['preferredCountries'] = explode(',', $item['preferred-countries']);
        empty($item['only-countries']) ?: $options['onlyCountries'] = explode(',', $item['only-countries']);

        $json = json_encode($options);
        $idAttr = $form->get_render_attributes('form', 'id');

        $js = <<<JS
    let input_{$form_id}_{$item_index} = jQuery("#form-field-{$item['custom_id']}");
    if (input_{$form_id}_{$item_index}.parents('div[data-elementor-type=popup]').length > 0) {
        jQuery(document).on('elementor/popup/show', function () {
            let input_{$form_id}_{$item_index} = jQuery("#form-field-{$item['custom_id']}");
            iti{$form_id}_{$item_index} = window.intlTelInput(input_{$form_id}_{$item_index}[0], {$json});
        })
    } else {
        iti{$form_id}_{$item_index} = window.intlTelInput(input_{$form_id}_{$item_index}[0], {$json});
    }

    jQuery(document).on('change', '#form-field-{$item['custom_id']}', function () {
        jQuery("#form-field-{$item['custom_id']}").val(iti{$form_id}_{$item_index}.getNumber(0));
    })

JS;
        wp_add_inline_script('int-tel-input-script-handle', $js);
    }

    /**
     * Field validation.
     *
     * Validate credit card number field value to ensure it complies to certain rules.
     *
     * @since 1.0.0
     * @access public
     * @param \ElementorPro\Modules\Forms\Classes\Field_Base   $field
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
     * @return void
     */
    public function validation($field, $record, $ajax_handler)
    {

        if (empty($field['value'])) {
            return;
        }

        $phoneNumber = $field['value'];
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $phoneNumberProto = $phoneUtil->parse($phoneNumber, 'EC');
        } catch (NumberParseException $e) {
            $ajax_handler->add_error_message(
                esc_html__('Phone number invalid.', 'elementor-form-int-tel-input-field')
            );
            $ajax_handler->add_error(
                $field['id'],
                esc_html__('Phone number invalid.', 'elementor-form-int-tel-input-field')
            );

            return;
        }

        if (!$phoneUtil->isValidNumber($phoneNumberProto)) {
            $ajax_handler->add_error_message(
                esc_html__('Phone number invalid.', 'elementor-form-int-tel-input-field')
            );
            $ajax_handler->add_error(
                $field['id'],
                esc_html__('Phone number invalid.', 'elementor-form-int-tel-input-field')
            );
        }
    }

    /**
     * Update form widget controls.
     *
     * Add input fields to allow the user to customize the credit card number field.
     *
     * @since 1.0.0
     * @access public
     * @param \Elementor\Widget_Base $widget The form widget instance.
     * @return void
     */
    public function update_controls($widget)
    {
        $elementor = \ElementorPro\Plugin::elementor();

        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if (is_wp_error($control_data)) {
            return;
        }

        $field_controls = [
            'allow-dropdown' => [
                'name' => 'allow-dropdown',
                'label' => esc_html__('Allow Dropdown', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => true,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'advanced',
                'inner_tab'    => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'auto-insert-dial-code' => [
                'name' => 'auto-insert-dial-code',
                'label' => esc_html__('Allow Insert Dial Code', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => false,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'advanced',
                'inner_tab'    => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'auto-placeholder' => [
                'name' => 'auto-placeholder',
                'label' => esc_html__('Auto Placeholder', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'polite' => 'polite',
                    'aggresive' => 'aggresive',
                    'off' => 'off',
                ],
                'default' => 'polite',
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'advanced',
                'inner_tab'    => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'exclude-countries' => [
                'name' => 'exclude-countries',
                'label' => esc_html__('Exclude countries', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'content',
                'inner_tab'    => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'format-on-display' => [
                'name' => 'format-on-display',
                'label' => esc_html__('Format on display', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => true,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'advanced',
                'inner_tab'    => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'initial-country' => [
                'name' => 'initial-country',
                'label' => esc_html__('Default Country Code', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'content',
                'inner_tab'    => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'only-countries' => [
                'name' => 'only-countries',
                'label' => esc_html__('Only countries', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'content',
                'inner_tab'    => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'preferred-countries' => [
                'name' => 'preferred-countries',
                'label' => esc_html__('Preferred countries', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'content',
                'inner_tab'    => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'separate-dial-code' =>  [
                'name' => 'separate-dial-code',
                'label' => esc_html__('Separate Dial Code', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => false,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'advanced',
                'inner_tab'    => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'show-flags' =>  [
                'name' => 'show-flags',
                'label' => esc_html__('Show Flags', 'elementor-form-int-tel-input-field'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => true,
                'dynamic' => [
                    'active' => true,
                ],
                'condition' => [
                    'field_type' => $this->get_type(),
                ],
                'tab'          => 'content',
                'inner_tab'    => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ]
        ];

        $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);

        $widget->update_control('form_fields', $control_data);
    }

    /**
     * Elementor editor preview.
     *
     * Add a script to the footer of the editor preview screen.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function editor_preview_footer()
    {
        add_action('wp_footer', [$this, 'content_template_script']);
    }

    /**
     * Content template script.
     *
     * Add content template alternative, to display the field in Elemntor editor.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function content_template_script()
    {
?>
        <script>
            jQuery(document).ready(() => {

                elementor.hooks.addFilter(
                    'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
                    function(inputField, item, i) {
                        const fieldType = 'tel';
                        const fieldId = `form_field_${i}`;
                        const fieldClass = `elementor-field-textual elementor-field ${item.css_classes}`;

                        return `<input type="${fieldType}" id="${fieldId}" class="${fieldClass}">`;
                    }, 10, 3
                );


            });
        </script>
<?php
    }
}
