<?php
/**
 * Plugin Activation Handler
 * 
 * Handles tasks that need to be performed when the plugin is activated
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class TPSF_Activator {
    
    /**
     * Run activation tasks
     */
    public static function activate() {
        // Create necessary database tables (if needed)
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log activation
        self::log_activation();
    }
    
    /**
     * Create database tables if needed
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Example table for search logs (optional)
        $table_name = $wpdb->prefix . 'tpsf_search_logs';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            search_term varchar(255) NOT NULL,
            user_ip varchar(45) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $default_options = array(
            'tpsf_enable_analytics' => true,
            'tpsf_max_results' => 10,
            'tpsf_enable_suggestions' => false,
            'tpsf_search_timeout' => 30,
            'tpsf_enable_filters' => false
        );
        
        foreach ($default_options as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Log plugin activation
     */
    private static function log_activation() {
        $activation_log = get_option('tpsf_activation_log', array());
        $activation_log[] = array(
            'version' => TPSF_VERSION,
            'timestamp' => current_time('timestamp'),
            'site_url' => get_site_url()
        );
        
        update_option('tpsf_activation_log', $activation_log);
    }
} 