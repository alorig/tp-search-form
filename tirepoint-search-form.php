<?php
/**
 * Plugin Name: TirePoint Search Form
 * Plugin URI: https://tirepoint.ca
 * Description: Custom search form plugin for TirePoint.ca website
 * Version: 1.0.0
 * Author: TirePoint
 * License: GPL v2 or later
 * Text Domain: tirepoint-search-form
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TPSF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TPSF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TPSF_VERSION', '1.0.0');

// Include required files
require_once TPSF_PLUGIN_PATH . 'includes/search-handler.php';
require_once TPSF_PLUGIN_PATH . 'includes/class-activator.php';

// Register activation hook
register_activation_hook(__FILE__, array('TPSF_Activator', 'activate'));

/**
 * Main TirePoint Search Form Plugin Class
 */
class TirePointSearchForm {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX handlers for vehicle search
        add_action('wp_ajax_tpsf_get_makes', array($this, 'handle_get_makes'));
        add_action('wp_ajax_nopriv_tpsf_get_makes', array($this, 'handle_get_makes'));
        add_action('wp_ajax_tpsf_get_models', array($this, 'handle_get_models'));
        add_action('wp_ajax_nopriv_tpsf_get_models', array($this, 'handle_get_models'));
        add_action('wp_ajax_tpsf_get_years', array($this, 'handle_get_years'));
        add_action('wp_ajax_nopriv_tpsf_get_years', array($this, 'handle_get_years'));
        add_action('wp_ajax_tpsf_get_tire_results', array($this, 'handle_get_tire_results'));
        add_action('wp_ajax_nopriv_tpsf_get_tire_results', array($this, 'handle_get_tire_results'));
        
        add_shortcode('tirepoint_search_form', array($this, 'render_search_form'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // TODO: Add initialization code here
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'tpsf-styles',
            TPSF_PLUGIN_URL . 'assets/css/tirepoint-search-form.css',
            array(),
            TPSF_VERSION
        );
        
        wp_enqueue_script(
            'tpsf-script',
            TPSF_PLUGIN_URL . 'assets/js/tirepoint-search-form.js',
            array('jquery'),
            TPSF_VERSION,
            true
        );
        
        wp_localize_script('tpsf-script', 'tpsf_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tpsf_nonce')
        ));
    }
    
    /**
     * Handle AJAX request to get makes with available tires
     */
    public function handle_get_makes() {
        check_ajax_referer('tpsf_nonce', 'nonce');
        
        $makes = TPSF_SearchHandler::get_makes();
        wp_send_json_success($makes);
    }
    
    /**
     * Handle AJAX request to get models for selected make
     */
    public function handle_get_models() {
        check_ajax_referer('tpsf_nonce', 'nonce');
        
        $make = sanitize_text_field($_POST['make']);
        
        if (empty($make)) {
            wp_send_json_error('Make is required.');
        }
        
        $models = TPSF_SearchHandler::get_models($make);
        wp_send_json_success($models);
    }
    
    /**
     * Handle AJAX request to get years for selected model
     */
    public function handle_get_years() {
        check_ajax_referer('tpsf_nonce', 'nonce');
        
        $make = sanitize_text_field($_POST['make']);
        $model = sanitize_text_field($_POST['model']);
        
        if (empty($make) || empty($model)) {
            wp_send_json_error('Make and Model are required.');
        }
        
        $years = TPSF_SearchHandler::get_years($make, $model);
        wp_send_json_success($years);
    }
    
    /**
     * Handle AJAX request to get tire results for make and model
     */
    public function handle_get_tire_results() {
        try {
            check_ajax_referer('tpsf_nonce', 'nonce');
            
            $make = sanitize_text_field($_POST['make']);
            $model = sanitize_text_field($_POST['model']);
            
            // Allow make-only searches
            if (empty($make)) {
                wp_send_json_error('Make is required.');
            }
            
            // Log the search
            TPSF_SearchHandler::log_search(array(
                'make' => $make,
                'model' => $model
            ));
            
            $tires = TPSF_SearchHandler::get_tire_results($make, $model);
            wp_send_json_success($tires);
            
        } catch (Exception $e) {
            error_log("TPSF Error: " . $e->getMessage());
            wp_send_json_error('Error processing request: ' . $e->getMessage());
        }
    }
    
    /**
     * Render search form shortcode
     */
    public function render_search_form($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => 'Search tires...',
            'button_text' => 'Search'
        ), $atts);
        
        ob_start();
        include TPSF_PLUGIN_PATH . 'templates/search-form.php';
        return ob_get_clean();
    }
}

// Initialize plugin
new TirePointSearchForm(); 