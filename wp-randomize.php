<?php
/**
 * Plugin Name:       WP Randomize
 * Plugin URI:        https://wpvar.com/wp-randomize
 * Description:       Adds <strong>advanced random categories & posts Widget</strong> which you can fully customize.
 * Version:           1.0.0
 * Requires at least: 4.4
 * Requires PHP:      5.3
 * Author:            wpvar.com
 * Author URI:        https://wpvar.com/
 * License:           GNU Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wp-randomize
 * @package WPRAND
 */

defined('ABSPATH') or die();

if (! class_exists('WPRAND_Widget')) {
    include plugin_dir_path(__FILE__) . 'inc/WPRAND_Widget.class.php';
}

add_action('admin_enqueue_scripts', 'wprand_load_colorpicker', 10);

add_action('init', 'wprand_load_text_domain', 10);


new WPRAND_Widget();


function wprand_load_colorpicker($hook)
{
    if ('widgets.php' != $hook) {
        return;
    }
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('wprand_colorpicker', plugin_dir_url(__FILE__) . 'assets/js/wprand_colorpicker.js', array(), '1.0');
}
function wprand_load_text_domain()
{
    load_plugin_textdomain('wp-randomize');
}
