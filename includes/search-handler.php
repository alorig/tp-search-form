<?php
/**
 * Vehicle Search Handler for TirePoint Search Form Plugin
 * 
 * This file contains the vehicle search logic and AJAX handlers
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class TPSF_SearchHandler {
    
    /**
     * Get makes that have vehicles with tire products
     */
    public static function get_makes() {
        $makes = array();
        
        // Get all makes
        $make_terms = get_terms(array(
            'taxonomy' => 'vehicle-make',
            'hide_empty' => false,
            'parent' => 0,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        if (!empty($make_terms) && !is_wp_error($make_terms)) {
            foreach ($make_terms as $make) {
                // Check if this make is actually used in any published vehicle-model posts
                $related_posts = get_posts(array(
                    'post_type' => 'vehicle-model',
                    'post_status' => 'publish',
                    'numberposts' => 1,
                    'tax_query' => array(array(
                        'taxonomy' => 'vehicle-make',
                        'field' => 'slug',
                        'terms' => $make->slug,
                    )),
                ));
                
                if (!empty($related_posts)) {
                    $makes[] = array(
                        'value' => $make->slug,
                        'label' => $make->name
                    );
                }
            }
        }
        
        return $makes;
    }
    
    /**
     * Get models for selected make
     */
    public static function get_models($make) {
        $models = array();
        $model_terms = array();
        
        // Get all published vehicle-model posts for this make
        $posts = get_posts(array(
            'post_type' => 'vehicle-model',
            'post_status' => 'publish',
            'numberposts' => -1,
            'tax_query' => array(array(
                'taxonomy' => 'vehicle-make',
                'field' => 'slug',
                'terms' => $make,
            )),
        ));
        
        // Extract unique models from these posts
        foreach ($posts as $post) {
            $post_models = wp_get_post_terms($post->ID, 'vehicles-model');
            foreach ($post_models as $model) {
                $model_terms[$model->slug] = $model->name;
            }
        }
        
        // Convert to array format
        foreach ($model_terms as $slug => $name) {
            $models[] = array(
                'value' => $slug,
                'label' => $name
            );
        }
        
        return $models;
    }
    
    /**
     * Get years for selected model
     */
    public static function get_years($make, $model) {
        $years = array();
        $year_terms = array();
        
        // Get all published vehicle-model posts for this make and model
        $posts = get_posts(array(
            'post_type' => 'vehicle-model',
            'post_status' => 'publish',
            'numberposts' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'vehicle-make',
                    'field' => 'slug',
                    'terms' => $make,
                ),
                array(
                    'taxonomy' => 'vehicles-model',
                    'field' => 'slug',
                    'terms' => $model,
                )
            )
        ));
        
        // Extract unique years from these posts
        foreach ($posts as $post) {
            $post_years = wp_get_post_terms($post->ID, 'vehicle-model-year');
            foreach ($post_years as $year) {
                $year_terms[$year->slug] = $year->name;
            }
        }
        
        // Convert to array format
        foreach ($year_terms as $slug => $name) {
            $years[] = array(
                'value' => $slug,
                'label' => $name
            );
        }
        
        return $years;
    }
    
    /**
     * Get tire results for make and model
     */
    public static function get_tire_results($make, $model) {
        $tires = array();
        
        // Get vehicles for this make and model
        $vehicles = get_posts(array(
            'post_type' => 'vehicle-model',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'make',
                    'value' => $make,
                    'compare' => '='
                ),
                array(
                    'key' => 'model',
                    'value' => $model,
                    'compare' => '='
                )
            )
        ));
        
        if (!empty($vehicles)) {
            // Get tire products related to these vehicles
            $tire_products = self::get_tires_for_vehicles($vehicles);
            
            foreach ($tire_products as $tire) {
                $tires[] = array(
                    'id' => $tire->ID,
                    'title' => $tire->post_title,
                    'size' => get_post_meta($tire->ID, '_tire_size', true),
                    'type' => get_post_meta($tire->ID, '_tire_type', true),
                    'price' => self::get_tire_price($tire->ID),
                    'image' => get_the_post_thumbnail_url($tire->ID, 'medium'),
                    'availability' => self::get_tire_availability($tire->ID),
                    'url' => get_permalink($tire->ID)
                );
            }
        }
        
        return $tires;
    }
    

    
    /**
     * Get tires for vehicles
     */
    private static function get_tires_for_vehicles($vehicles) {
        $vehicle_ids = wp_list_pluck($vehicles, 'ID');
        
        return get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => 12,
            'meta_query' => array(
                array(
                    'key' => '_vehicle_id',
                    'value' => $vehicle_ids,
                    'compare' => 'IN'
                )
            ),
            'orderby' => 'title',
            'order' => 'ASC'
        ));
    }
    
    /**
     * Get tire price
     */
    private static function get_tire_price($tire_id) {
        if (class_exists('WooCommerce')) {
            $product = wc_get_product($tire_id);
            if ($product) {
                return $product->get_price_html();
            }
        }
        
        $price = get_post_meta($tire_id, '_price', true);
        return $price ? '$' . number_format($price, 2) : 'Price on request';
    }
    
    /**
     * Get tire availability
     */
    private static function get_tire_availability($tire_id) {
        $stock_status = get_post_meta($tire_id, '_stock_status', true);
        
        switch ($stock_status) {
            case 'instock':
                return 'In Stock';
            case 'outofstock':
                return 'Out of Stock';
            case 'onbackorder':
                return 'On Backorder';
            default:
                return 'Check Availability';
        }
    }
    
    /**
     * Log search for analytics
     */
    public static function log_search($search_data) {
        $searches = get_option('tpsf_search_log', array());
        $searches[] = array(
            'make' => $search_data['make'] ?? '',
            'model' => $search_data['model'] ?? '',
            'year' => $search_data['year'] ?? '',
            'timestamp' => current_time('timestamp'),
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        );
        
        // Keep only last 100 searches
        if (count($searches) > 100) {
            $searches = array_slice($searches, -100);
        }
        
        update_option('tpsf_search_log', $searches);
    }
} 