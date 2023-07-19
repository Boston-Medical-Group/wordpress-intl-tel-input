<?php

/**
 * Plugin Name: Elementor Forms Int Tel Input Field
 * Description: Custom addon that adds a fancy "intl-tel-input" field to Elementor Forms Widget.
 * Plugin URI:  https://www.bostonmedicalgroup.es/
 * Version:     1.0.1
 * Author:      Jean Rumeau
 * Text Domain: elementor-form-int-tel-input-field
 *
 * Elementor tested up to: 3.13.3
 * Elementor Pro tested up to: 3.13.2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register `int-tel-input` field-type to Elementor form widget.
 *
 * @since 1.0.0
 * @param \ElementorPro\Modules\Forms\Registrars\Form_Fields_Registrar $form_fields_registrar
 * @return void
 */
function add_new_int_tel_input_field($form_fields_registrar)
{

    require_once(__DIR__ . '/vendor/autoload.php');
    require_once(__DIR__ . '/form-fields/int-tel-input.php');

    $form_fields_registrar->register(new \Elementor_Int_Tel_Input_Field(__FILE__));
}

add_action('elementor_pro/forms/fields/register', 'add_new_int_tel_input_field');


/**
 * Register scripts and styles for Elementor form fields.
 */
function elementor_int_tel_input_field_dependencies()
{

    /* Scripts */
    wp_register_script('int-tel-input-script-handle', plugins_url('assets/js/intlTelInput.min.js', __FILE__));
    //wp_register_script('int-tel-input-util-script-handle', plugins_url('assets/js/utils.js', __FILE__));

    /* Styles */
    wp_register_style('int-tel-input-style-handle', plugins_url('assets/css/intlTelInput.min.css', __FILE__));
    wp_register_style('bmg-int-tel-input-style-handle', plugins_url('assets/css/bmg-efit.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'elementor_int_tel_input_field_dependencies');
