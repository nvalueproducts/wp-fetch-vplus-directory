<?php
/*
Plugin Name: vplusDirectory
Description: A WordPress plugin to fetch and display data from a given website URL using cURL.
Version: 1.0
Author: vip-system.ir - ChatGPT
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('VPLUS_DIRECTORY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VPLUS_DIRECTORY_PLUGIN_URL', plugin_dir_url(__FILE__));

// Enqueue necessary scripts and styles
function vplus_directory_enqueue_scripts() {
    wp_enqueue_style('vplus-directory-style', VPLUS_DIRECTORY_PLUGIN_URL . 'css/style.css');
    wp_enqueue_script('vplus-directory-script', VPLUS_DIRECTORY_PLUGIN_URL . 'js/script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'vplus_directory_enqueue_scripts');

// Add settings page
function vplus_directory_settings_menu() {
    add_options_page('vplusDirectory Settings', 'vplusDirectory Settings', 'manage_options', 'vplus-directory-settings', 'vplus_directory_settings_page');
}
add_action('admin_menu', 'vplus_directory_settings_menu');

// Settings page callback function
function vplus_directory_settings_page() {
    ?>
    <div class="wrap">
        <h2>vplusDirectory Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('vplus_directory_settings');
            do_settings_sections('vplus-directory-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register and define the settings
function vplus_directory_register_settings() {
    register_setting('vplus_directory_settings', 'vplus_directory_enabled');
    add_settings_section('vplus_directory_main_section', 'Main Settings', 'vplus_directory_main_section_cb', 'vplus-directory-settings');
    add_settings_field('vplus_directory_enable_field', 'Enable Plugin', 'vplus_directory_enable_field_cb', 'vplus-directory-settings', 'vplus_directory_main_section');
}
add_action('admin_init', 'vplus_directory_register_settings');

// Section callback function
function vplus_directory_main_section_cb() {
    echo '<p>Main settings for vplusDirectory plugin.</p>';
}

// Enable field callback function
function vplus_directory_enable_field_cb() {
    $enabled = get_option('vplus_directory_enabled');
    echo '<input type="checkbox" name="vplus_directory_enabled" value="1" ' . checked(1, $enabled, false) . '/>';
}

// Register shortcode
function vplus_directory_shortcode($atts) {
    // Extract attributes
    $atts = shortcode_atts(
        array(
            'url' => '', // Default value
        ),
        $atts
    );

    // Check if plugin is enabled
    if (get_option('vplus_directory_enabled') != 1) {
        return 'Error: vplusDirectory plugin is not enabled.';
    }

    // Check if URL is provided
    if (empty($atts['url'])) {
        return 'Error: Please provide a valid URL.';
    }

    // Make cURL request
    $response = wp_remote_get($atts['url']);

    // Check for errors
    if (is_wp_error($response)) {
        return 'Error: Failed to fetch data from the provided URL.';
    }

    // Get the body of the response
    $body = wp_remote_retrieve_body($response);

    // Display the fetched data
    return $body;
}
add_shortcode('vplus_directory', 'vplus_directory_shortcode');
