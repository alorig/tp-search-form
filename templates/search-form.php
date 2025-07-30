<?php
/**
 * Vehicle Search Form Template for TirePoint Search Form Plugin
 * 
 * This template implements the Make → Model → Year dropdown flow
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get attributes from shortcode
$placeholder = isset($atts['placeholder']) ? esc_attr($atts['placeholder']) : 'Select your vehicle...';
$button_text = isset($atts['button_text']) ? esc_attr($atts['button_text']) : 'Find Tires';
?>

<div class="tpsf-search-container">
    <form class="tpsf-vehicle-search-form" method="post" id="tpsf-vehicle-form">
        <div class="tpsf-form-row">
            <!-- Make Dropdown -->
            <div class="tpsf-form-group">
                <label for="tpsf-make" class="tpsf-label">Make</label>
                <select id="tpsf-make" name="make" class="tpsf-select" required>
                    <option value="">Select Make</option>
                    <?php
                    // Get makes that have vehicles with tire products
                    $makes = get_terms(array(
                        'taxonomy' => 'vehicle-make',
                        'hide_empty' => true,
                        'orderby' => 'name',
                        'order' => 'ASC'
                    ));
                    
                    if (!empty($makes) && !is_wp_error($makes)) {
                        foreach ($makes as $make) {
                            echo '<option value="' . esc_attr($make->slug) . '">' . esc_html($make->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Model Dropdown -->
            <div class="tpsf-form-group">
                <label for="tpsf-model" class="tpsf-label">Model</label>
                <select id="tpsf-model" name="model" class="tpsf-select" disabled>
                    <option value="">Select Model</option>
                </select>
            </div>

            <!-- Year Dropdown -->
            <div class="tpsf-form-group">
                <label for="tpsf-year" class="tpsf-label">Year</label>
                <select id="tpsf-year" name="year" class="tpsf-select" disabled>
                    <option value="">Select Year</option>
                </select>
            </div>
        </div>

        <!-- Reset Button -->
        <div class="tpsf-form-actions">
            <button type="button" class="tpsf-reset-button" id="tpsf-reset-form">
                Reset Form
            </button>
        </div>
    </form>
    
    <!-- Live Results Container -->
    <div class="tpsf-results-container" id="tpsf-results" style="display: none;">
        <div class="tpsf-results-header">
            <h3>Tire Results</h3>
            <span class="tpsf-results-count"></span>
        </div>
        <div class="tpsf-results-grid" id="tpsf-results-grid">
            <!-- Tire cards will be loaded here via AJAX -->
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div class="tpsf-loading-spinner" id="tpsf-loading" style="display: none;">
    <div class="tpsf-spinner"></div>
    <p>Loading tires...</p>
</div> 