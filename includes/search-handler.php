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
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        if (!empty($make_terms) && !is_wp_error($make_terms)) {
            foreach ($make_terms as $make) {
                // Check if this make has vehicles with tire products
                if (self::make_has_tires($make->slug)) {
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
        
        // Get models that belong to the selected make and have vehicles with tire products
        $model_terms = get_terms(array(
            'taxonomy' => 'vehicles-model',
            'hide_empty' => true,
            'meta_query' => array(
                array(
                    'key' => 'parent_make',
                    'value' => $make,
                    'compare' => '='
                )
            )
        ));
        
        if (!empty($model_terms) && !is_wp_error($model_terms)) {
            foreach ($model_terms as $model) {
                // Check if this model has vehicles with tire products
                if (self::model_has_tires($model->slug)) {
                    $models[] = array(
                        'value' => $model->slug,
                        'label' => $model->name
                    );
                }
            }
        }
        
        return $models;
    }
    
    /**
     * Get years for selected model
     */
    public static function get_years($make, $model) {
        $years = array();
        
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
        
        // Get years from vehicle-model-year taxonomy for these vehicles
        $year_terms = get_terms(array(
            'taxonomy' => 'vehicle-model-year',
            'hide_empty' => true,
            'object_ids' => wp_list_pluck($vehicles, 'ID')
        ));
        
        if (!empty($year_terms) && !is_wp_error($year_terms)) {
            foreach ($year_terms as $year) {
                // Check if this year has tire products
                if (self::year_has_tires($make, $model, $year->slug)) {
                    $years[] = array(
                        'value' => $year->slug,
                        'label' => $year->name
                    );
                }
            }
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
     * Check if make has vehicles with tire products
     */
    private static function make_has_tires($make_slug) {
        $vehicles = get_posts(array(
            'post_type' => 'vehicle-model',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'make',
                    'value' => $make_slug,
                    'compare' => '='
                )
            )
        ));
        
        if (!empty($vehicles)) {
            return self::vehicle_has_tires($vehicles[0]->ID);
        }
        
        return false;
    }
    
    /**
     * Check if model has vehicles with tire products
     */
    private static function model_has_tires($model_slug) {
        $vehicles = get_posts(array(
            'post_type' => 'vehicle-model',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'model',
                    'value' => $model_slug,
                    'compare' => '='
                )
            )
        ));
        
        if (!empty($vehicles)) {
            return self::vehicle_has_tires($vehicles[0]->ID);
        }
        
        return false;
    }
    
    /**
     * Check if year has tire products
     */
    private static function year_has_tires($make, $model, $year) {
        $vehicles = get_posts(array(
            'post_type' => 'vehicle-model',
            'posts_per_page' => 1,
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
                ),
                array(
                    'key' => 'year',
                    'value' => $year,
                    'compare' => '='
                )
            )
        ));
        
        if (!empty($vehicles)) {
            return self::vehicle_has_tires($vehicles[0]->ID);
        }
        
        return false;
    }
    
    /**
     * Check if vehicle has tire products
     */
    private static function vehicle_has_tires($vehicle_id) {
        // This would depend on your specific relationship setup
        // Example: Check if vehicle has related tire products
        $related_tires = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_vehicle_id',
                    'value' => $vehicle_id,
                    'compare' => '='
                )
            )
        ));
        
        return !empty($related_tires);
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