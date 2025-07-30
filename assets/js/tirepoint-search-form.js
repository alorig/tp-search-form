/**
 * TirePoint Vehicle Search Form - Premium UI JavaScript
 * Professional, modular implementation with sliding dropdown behavior
 */

(function($) {
    'use strict';

    // Configuration
    const CONFIG = {
        selectors: {
            form: '#tpsf-vehicle-form',
            makeSelect: '#tpsf-make',
            modelSelect: '#tpsf-model',
            yearSelect: '#tpsf-year',
            resetButton: '#tpsf-reset-form',
            resultsContainer: '#tpsf-results',
            resultsGrid: '#tpsf-results-grid',
            resultsCount: '.tpsf-results-count',
            loadingSpinner: '#tpsf-loading',
            formRow: '.tpsf-form-row'
        },
        classes: {
            singleDropdown: 'single-dropdown',
            twoDropdowns: 'two-dropdowns',
            threeDropdowns: 'three-dropdowns',
            hidden: 'hidden',
            fadeIn: 'tpsf-fade-in'
        },
        animations: {
            duration: 500,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
        }
    };

    // Main Controller Class
    class TirePointSearchController {
        constructor() {
            this.elements = {};
            this.state = {
                make: null,
                model: null,
                year: null
            };
            this.init();
        }

        /**
         * Initialize the controller
         */
        init() {
            this.cacheElements();
            this.bindEvents();
            this.loadMakes();
            this.loadSavedSelections();
            this.updateFormState();
        }

        /**
         * Cache DOM elements for performance
         */
        cacheElements() {
            this.elements = {
                form: $(CONFIG.selectors.form),
                makeSelect: $(CONFIG.selectors.makeSelect),
                modelSelect: $(CONFIG.selectors.modelSelect),
                yearSelect: $(CONFIG.selectors.yearSelect),
                resetButton: $(CONFIG.selectors.resetButton),
                resultsContainer: $(CONFIG.selectors.resultsContainer),
                resultsGrid: $(CONFIG.selectors.resultsGrid),
                resultsCount: $(CONFIG.selectors.resultsCount),
                loadingSpinner: $(CONFIG.selectors.loadingSpinner),
                formRow: $(CONFIG.selectors.formRow)
            };
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            this.elements.makeSelect.on('change', this.handleMakeChange.bind(this));
            this.elements.modelSelect.on('change', this.handleModelChange.bind(this));
            this.elements.yearSelect.on('change', this.handleYearChange.bind(this));
            this.elements.resetButton.on('click', this.handleReset.bind(this));
        }

        /**
         * Handle make selection change
         */
        handleMakeChange(event) {
            const selectedMake = $(event.target).val();
            
            if (selectedMake) {
                this.state.make = selectedMake;
                this.state.model = null;
                this.state.year = null;
                
                console.log('Make selected:', selectedMake);
                this.resetDependentDropdowns(['model', 'year']);
                this.loadModels(selectedMake);
                this.loadTireResults(selectedMake, ''); // Load products immediately
                this.updateFormState();
            }
        }

        /**
         * Handle model selection change
         */
        handleModelChange(event) {
            const selectedModel = $(event.target).val();
            
            if (selectedModel && this.state.make) {
                this.state.model = selectedModel;
                this.state.year = null;
                
                this.resetDependentDropdowns(['year']);
                this.loadYears(this.state.make, selectedModel);
                this.loadTireResults(this.state.make, selectedModel);
                this.updateFormState();
            }
        }

        /**
         * Handle year selection change
         */
        handleYearChange(event) {
            const selectedYear = $(event.target).val();
            
            if (selectedYear && this.state.make && this.state.model) {
                this.state.year = selectedYear;
                this.redirectToTireArchive(this.state.make, this.state.model, selectedYear);
            }
        }

        /**
         * Handle reset button click
         */
        handleReset() {
            this.resetForm();
        }

        /**
         * Load makes with available tires
         */
        loadMakes() {
            this.showLoading(this.elements.makeSelect);
            
            $.ajax({
                url: tpsf_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tpsf_get_makes',
                    nonce: tpsf_ajax.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.populateDropdown(this.elements.makeSelect, response.data);
                    } else {
                        this.showError('Failed to load makes.');
                    }
                },
                error: () => {
                    this.showError('Error loading makes. Please try again.');
                }
            });
        }

        /**
         * Load models for selected make
         */
        loadModels(make) {
            this.showLoading(this.elements.modelSelect);
            
            $.ajax({
                url: tpsf_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tpsf_get_models',
                    make: make,
                    nonce: tpsf_ajax.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.populateDropdown(this.elements.modelSelect, response.data);
                        this.elements.modelSelect.prop('disabled', false);
                    } else {
                        this.showError('Failed to load models.');
                    }
                },
                error: () => {
                    this.showError('Error loading models. Please try again.');
                }
            });
        }

        /**
         * Load years for selected model
         */
        loadYears(make, model) {
            this.showLoading(this.elements.yearSelect);
            
            $.ajax({
                url: tpsf_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tpsf_get_years',
                    make: make,
                    model: model,
                    nonce: tpsf_ajax.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.populateDropdown(this.elements.yearSelect, response.data);
                        this.elements.yearSelect.prop('disabled', false);
                    } else {
                        this.showError('Failed to load years.');
                    }
                },
                error: () => {
                    this.showError('Error loading years. Please try again.');
                }
            });
        }

        /**
         * Load tire results for make and model
         */
        loadTireResults(make, model) {
            this.elements.resultsContainer.show();
            this.showLoading(this.elements.resultsGrid);
            
            console.log('Loading tire results for:', make, model);
            
            $.ajax({
                url: tpsf_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tpsf_get_tire_results',
                    make: make,
                    model: model,
                    nonce: tpsf_ajax.nonce
                },
                success: (response) => {
                    console.log('Tire results response:', response);
                    if (response.success) {
                        this.displayTireResults(response.data);
                    } else {
                        this.showError('No tires found for this vehicle.');
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Tire results error:', error);
                    this.showError('Error loading tire results. Please try again.');
                }
            });
        }

        /**
         * Display tire results
         */
        displayTireResults(tires) {
            if (!tires || tires.length === 0) {
                this.elements.resultsGrid.html('<div class="tpsf-no-results">No tires found for this vehicle.</div>');
                this.elements.resultsCount.text('0 results');
                return;
            }
            
            let html = '';
            tires.forEach((tire, index) => {
                html += this.createTireCard(tire, index);
            });
            
            this.elements.resultsGrid.html(html);
            this.elements.resultsCount.text(tires.length + ' results');
        }

        /**
         * Create tire card HTML
         */
        createTireCard(tire, index) {
            return `
                <div class="tpsf-tire-card ${CONFIG.classes.fadeIn}" style="animation-delay: ${index * 0.1}s">
                    <img src="${tire.image || '/wp-content/plugins/tirepoint-search-form/assets/images/placeholder-tire.jpg'}" 
                         alt="${tire.title}" 
                         class="tpsf-tire-image"
                         onerror="this.src='/wp-content/plugins/tirepoint-search-form/assets/images/placeholder-tire.jpg'">
                    <h4 class="tpsf-tire-title">${tire.title}</h4>
                    <div class="tpsf-tire-size">${tire.size || 'Size not specified'}</div>
                    <div class="tpsf-tire-type">${tire.type || 'All Season'}</div>
                    <div class="tpsf-tire-price">${tire.price || 'Price on request'}</div>
                    <div class="tpsf-tire-availability">${tire.availability || 'Check availability'}</div>
                </div>
            `;
        }

        /**
         * Update form state and visual appearance
         */
        updateFormState() {
            const { make, model, year } = this.state;
            
            // Remove all state classes
            this.elements.formRow.removeClass([
                CONFIG.classes.singleDropdown,
                CONFIG.classes.twoDropdowns,
                CONFIG.classes.threeDropdowns
            ]);
            
            // Add appropriate state class
            if (make && !model && !year) {
                this.elements.formRow.addClass(CONFIG.classes.twoDropdowns);
            } else if (make && model && !year) {
                this.elements.formRow.addClass(CONFIG.classes.threeDropdowns);
            } else if (make && model && year) {
                this.elements.formRow.addClass(CONFIG.classes.threeDropdowns);
            } else {
                this.elements.formRow.addClass(CONFIG.classes.singleDropdown);
            }
        }

        /**
         * Reset dependent dropdowns
         */
        resetDependentDropdowns(dropdowns) {
            dropdowns.forEach(dropdown => {
                const element = this.elements[dropdown + 'Select'];
                this.resetDropdown(element);
            });
        }

        /**
         * Reset dropdown to initial state
         */
        resetDropdown($select) {
            $select.empty();
            const label = $select.attr('id').replace('tpsf-', '').charAt(0).toUpperCase() + 
                         $select.attr('id').replace('tpsf-', '').slice(1);
            $select.append(`<option value="">Select ${label}</option>`);
            $select.prop('disabled', true);
        }

        /**
         * Populate dropdown with options
         */
        populateDropdown($select, options) {
            $select.empty();
            const label = $select.attr('id').replace('tpsf-', '').charAt(0).toUpperCase() + 
                         $select.attr('id').replace('tpsf-', '').slice(1);
            $select.append(`<option value="">Select ${label}</option>`);
            
            if (options && options.length > 0) {
                options.forEach(option => {
                    $select.append(`<option value="${option.value}">${option.label}</option>`);
                });
            }
        }

        /**
         * Show loading state
         */
        showLoading($element) {
            $element.html('<option value="">Loading...</option>');
        }

        /**
         * Hide results container
         */
        hideResults() {
            this.elements.resultsContainer.hide();
        }

        /**
         * Reset form to initial state
         */
        resetForm() {
            this.state = { make: null, model: null, year: null };
            this.elements.makeSelect.val('');
            this.resetDependentDropdowns(['model', 'year']);
            this.hideResults();
            this.updateFormState();
            this.clearSavedSelections();
        }

        /**
         * Redirect to tire archive page
         */
        redirectToTireArchive(make, model, year) {
            const archiveUrl = `/tires-for/${make}/${model}/${year}/`;
            this.saveSelectionsToCookies(make, model, year);
            window.location.href = archiveUrl;
        }

        /**
         * Save selections to cookies
         */
        saveSelectionsToCookies(make, model, year) {
            const expires = new Date();
            expires.setDate(expires.getDate() + 30);
            
            document.cookie = `tpsf_make=${make}; expires=${expires.toUTCString()}; path=/`;
            document.cookie = `tpsf_model=${model}; expires=${expires.toUTCString()}; path=/`;
            document.cookie = `tpsf_year=${year}; expires=${expires.toUTCString()}; path=/`;
        }

        /**
         * Load saved selections from cookies
         */
        loadSavedSelections() {
            const make = this.getCookie('tpsf_make');
            const model = this.getCookie('tpsf_model');
            const year = this.getCookie('tpsf_year');
            
            if (make) {
                this.elements.makeSelect.val(make);
                this.state.make = make;
                
                if (model) {
                    setTimeout(() => {
                        this.elements.modelSelect.val(model);
                        this.state.model = model;
                        
                        if (year) {
                            setTimeout(() => {
                                this.elements.yearSelect.val(year);
                                this.state.year = year;
                                this.updateFormState();
                            }, 500);
                        }
                    }, 500);
                }
            }
        }

        /**
         * Clear saved selections from cookies
         */
        clearSavedSelections() {
            const cookies = ['tpsf_make', 'tpsf_model', 'tpsf_year'];
            cookies.forEach(cookie => {
                document.cookie = `${cookie}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
            });
        }

        /**
         * Get cookie value by name
         */
        getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        /**
         * Show error message
         */
        showError(message) {
            const $error = $(`<div class="tpsf-error">${message}</div>`);
            $('.tpsf-search-container').append($error);
            
            setTimeout(() => {
                $error.fadeOut(() => {
                    $error.remove();
                });
            }, 5000);
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        window.TPSF = new TirePointSearchController();
    });

})(jQuery); 