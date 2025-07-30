/**
 * TirePoint Vehicle Search Form JavaScript
 */

jQuery(document).ready(function($) {
    
    // Initialize vehicle search form functionality
    function initVehicleSearchForm() {
        const $makeSelect = $('#tpsf-make');
        const $modelSelect = $('#tpsf-model');
        const $yearSelect = $('#tpsf-year');
        const $resetButton = $('#tpsf-reset-form');
        const $resultsContainer = $('#tpsf-results');
        const $loadingSpinner = $('#tpsf-loading');
        
        // Load saved selections from cookies
        loadSavedSelections();
        
        // Handle Make selection
        $makeSelect.on('change', function() {
            const selectedMake = $(this).val();
            
            if (selectedMake) {
                // Reset dependent dropdowns
                resetDropdown($modelSelect);
                resetDropdown($yearSelect);
                
                // Load models for selected make
                loadModels(selectedMake);
                
                // Hide results
                $resultsContainer.hide();
            }
        });
        
        // Handle Model selection
        $modelSelect.on('change', function() {
            const selectedModel = $(this).val();
            const selectedMake = $makeSelect.val();
            
            if (selectedModel && selectedMake) {
                // Reset year dropdown
                resetDropdown($yearSelect);
                
                // Load years for selected model
                loadYears(selectedMake, selectedModel);
                
                // Load tire results for Make + Model
                loadTireResults(selectedMake, selectedModel);
            }
        });
        
        // Handle Year selection
        $yearSelect.on('change', function() {
            const selectedYear = $(this).val();
            const selectedMake = $makeSelect.val();
            const selectedModel = $modelSelect.val();
            
            if (selectedYear && selectedMake && selectedModel) {
                // Redirect to tire archive page
                redirectToTireArchive(selectedMake, selectedModel, selectedYear);
            }
        });
        
        // Handle Reset button
        $resetButton.on('click', function() {
            resetForm();
        });
    }
    
    /**
     * Load models for selected make
     */
    function loadModels(make) {
        const $modelSelect = $('#tpsf-model');
        
        showLoading($modelSelect);
        
        $.ajax({
            url: tpsf_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tpsf_get_models',
                make: make,
                nonce: tpsf_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateDropdown($modelSelect, response.data);
                    $modelSelect.prop('disabled', false);
                } else {
                    showError('Failed to load models.');
                }
            },
            error: function() {
                showError('Error loading models. Please try again.');
            }
        });
    }
    
    /**
     * Load years for selected model
     */
    function loadYears(make, model) {
        const $yearSelect = $('#tpsf-year');
        
        showLoading($yearSelect);
        
        $.ajax({
            url: tpsf_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tpsf_get_years',
                make: make,
                model: model,
                nonce: tpsf_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateDropdown($yearSelect, response.data);
                    $yearSelect.prop('disabled', false);
                } else {
                    showError('Failed to load years.');
                }
            },
            error: function() {
                showError('Error loading years. Please try again.');
            }
        });
    }
    
    /**
     * Load tire results for Make + Model
     */
    function loadTireResults(make, model) {
        const $resultsContainer = $('#tpsf-results');
        const $resultsGrid = $('#tpsf-results-grid');
        const $resultsCount = $('.tpsf-results-count');
        
        $resultsContainer.show();
        showLoading($resultsGrid);
        
        $.ajax({
            url: tpsf_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'tpsf_get_tire_results',
                make: make,
                model: model,
                nonce: tpsf_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    displayTireResults(response.data, $resultsGrid, $resultsCount);
                } else {
                    showError('No tires found for this vehicle.');
                }
            },
            error: function() {
                showError('Error loading tire results. Please try again.');
            }
        });
    }
    
    /**
     * Redirect to tire archive page
     */
    function redirectToTireArchive(make, model, year) {
        const archiveUrl = `/tires-for/${make}/${model}/${year}/`;
        
        // Save selections to cookies before redirect
        saveSelectionsToCookies(make, model, year);
        
        // Redirect to archive page
        window.location.href = archiveUrl;
    }
    
    /**
     * Populate dropdown with options
     */
    function populateDropdown($select, options) {
        $select.empty();
        $select.append('<option value="">Select ' + $select.attr('id').replace('tpsf-', '').charAt(0).toUpperCase() + $select.attr('id').replace('tpsf-', '').slice(1) + '</option>');
        
        if (options && options.length > 0) {
            options.forEach(function(option) {
                $select.append('<option value="' + option.value + '">' + option.label + '</option>');
            });
        }
    }
    
    /**
     * Reset dropdown to initial state
     */
    function resetDropdown($select) {
        $select.empty();
        $select.append('<option value="">Select ' + $select.attr('id').replace('tpsf-', '').charAt(0).toUpperCase() + $select.attr('id').replace('tpsf-', '').slice(1) + '</option>');
        $select.prop('disabled', true);
    }
    
    /**
     * Show loading state for dropdown
     */
    function showLoading($element) {
        $element.html('<option value="">Loading...</option>');
    }
    
    /**
     * Display tire results
     */
    function displayTireResults(tires, $container, $count) {
        if (!tires || tires.length === 0) {
            $container.html('<div class="tpsf-no-results">No tires found for this vehicle.</div>');
            $count.text('0 results');
            return;
        }
        
        let html = '';
        tires.forEach(function(tire) {
            html += `
                <div class="tpsf-tire-card tpsf-fade-in">
                    <img src="${tire.image}" alt="${tire.title}" class="tpsf-tire-image">
                    <h4 class="tpsf-tire-title">${tire.title}</h4>
                    <div class="tpsf-tire-size">${tire.size}</div>
                    <div class="tpsf-tire-type">${tire.type}</div>
                    <div class="tpsf-tire-price">${tire.price}</div>
                    <div class="tpsf-tire-availability">${tire.availability}</div>
                </div>
            `;
        });
        
        $container.html(html);
        $count.text(tires.length + ' results');
    }
    
    /**
     * Reset form to initial state
     */
    function resetForm() {
        $('#tpsf-make').val('');
        resetDropdown($('#tpsf-model'));
        resetDropdown($('#tpsf-year'));
        $('#tpsf-results').hide();
        
        // Clear cookies
        clearSavedSelections();
    }
    
    /**
     * Save selections to cookies
     */
    function saveSelectionsToCookies(make, model, year) {
        const expires = new Date();
        expires.setDate(expires.getDate() + 30); // 30 days
        
        document.cookie = `tpsf_make=${make}; expires=${expires.toUTCString()}; path=/`;
        document.cookie = `tpsf_model=${model}; expires=${expires.toUTCString()}; path=/`;
        document.cookie = `tpsf_year=${year}; expires=${expires.toUTCString()}; path=/`;
    }
    
    /**
     * Load saved selections from cookies
     */
    function loadSavedSelections() {
        const make = getCookie('tpsf_make');
        const model = getCookie('tpsf_model');
        const year = getCookie('tpsf_year');
        
        if (make) {
            $('#tpsf-make').val(make);
            // Trigger model loading
            $('#tpsf-make').trigger('change');
            
            // If we have model and year, load them after a delay
            if (model && year) {
                setTimeout(function() {
                    $('#tpsf-model').val(model).trigger('change');
                    setTimeout(function() {
                        $('#tpsf-year').val(year);
                    }, 500);
                }, 500);
            }
        }
    }
    
    /**
     * Clear saved selections from cookies
     */
    function clearSavedSelections() {
        document.cookie = 'tpsf_make=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
        document.cookie = 'tpsf_model=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
        document.cookie = 'tpsf_year=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
    }
    
    /**
     * Get cookie value by name
     */
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }
    
    /**
     * Show error message
     */
    function showError(message) {
        const $error = $('<div class="tpsf-error">' + message + '</div>');
        $('.tpsf-search-container').append($error);
        
        // Remove error after 5 seconds
        setTimeout(function() {
            $error.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Initialize the vehicle search form
    initVehicleSearchForm();
    
    // Expose functions globally for potential use
    window.TPSF = {
        loadModels: loadModels,
        loadYears: loadYears,
        loadTireResults: loadTireResults,
        resetForm: resetForm,
        saveSelectionsToCookies: saveSelectionsToCookies,
        loadSavedSelections: loadSavedSelections
    };
}); 