# TirePoint Vehicle Search Form Plugin

A custom WordPress plugin for TirePoint.ca that provides a modern, responsive vehicle search form with Make → Model → Year dropdown flow and AJAX functionality.

## Features

- ✅ Vehicle-specific search with Make → Model → Year flow
- ✅ Dynamic dropdown filtering via AJAX
- ✅ Live tire results display (12 cards per query)
- ✅ Automatic redirect to `/tires-for/{make}/{model}/{year}/` on year selection
- ✅ Cookie memory for 30 days (pre-fills form on return)
- ✅ Responsive design (desktop: 3-column, mobile: stacked)
- ✅ WooCommerce integration ready
- ✅ Search logging and analytics
- ✅ Mobile-friendly interface with touch targets
- ✅ Error handling and loading states

## Installation

1. **Upload the plugin files** to your WordPress site:
   - Upload the entire `tirepoint-search-form` folder to `/wp-content/plugins/`
   - Or zip the folder and upload via WordPress admin

2. **Activate the plugin**:
   - Go to WordPress Admin → Plugins
   - Find "TirePoint Search Form" and click "Activate"

3. **Usage**:
   - Use the shortcode `[tirepoint_search_form]` in any post or page
   - Place directly below the hero section for optimal visibility
   - Form is always visible and not collapsible

## File Structure

```
tirepoint-search-form/
├── tirepoint-search-form.php          # Main plugin file
├── assets/
│   ├── css/
│   │   └── tirepoint-search-form.css  # Styles
│   └── js/
│       └── tirepoint-search-form.js   # JavaScript functionality
├── templates/
│   └── search-form.php                # HTML template
├── includes/
│   └── search-handler.php             # Search logic
└── README.md                          # This file
```

## Customization

### CSS Styling
Edit `assets/css/tirepoint-search-form.css` to match your website's design.

### Search Logic
Modify `includes/search-handler.php` to implement your specific search requirements:
- Custom post types
- WooCommerce products
- External APIs
- Database queries

### Template
Customize `templates/search-form.php` to change the HTML structure.

## Search Flow

### Dropdown Flow & Logic
| Step | Field | Type | Filter Dependency |
|------|-------|------|-------------------|
| 1 | Make | Taxonomy (vehicle-make) | Only show Makes that have Vehicles connected to published Tire Products |
| 2 | Model | Taxonomy (vehicles-model) | Filtered by selected Make (parent term); must also have tires available |
| 3 | Year | Taxonomy (vehicle-model-year) | Filtered by selected Model; based on Vehicles that also have tire products |

### Behavior
- **Auto Trigger**: Tire results load via AJAX after Make and Model selected
- **Year Selection**: Redirects to `/tires-for/{make}/{model}/{year}/`
- **Submit Button**: ❌ Not used
- **Reset Option**: ✅ Yes, resets form and hides results
- **Page Reload**: ❌ Never reload page (except on Year-based redirect)

### Live Results Display
- **Number of Cards**: 12 tire cards shown per query
- **Card Layout**: Desktop: 4 cards × 3 rows, Mobile: 2 cards per row
- **Card Content**: Image, Size, Type, Vehicle Name, Price, Availability, CTA

## TODO Items

### Search Functionality
- [ ] Implement tire-specific search logic
- [ ] Add filter options (size, brand, price, season)
- [ ] Integrate with tire database/API
- [ ] Add search suggestions
- [ ] Implement search analytics

### UI/UX Enhancements
- [ ] Add search filters dropdown
- [ ] Implement autocomplete
- [ ] Add search history
- [ ] Create mobile-optimized interface
- [ ] Add loading animations

### Advanced Features
- [ ] Search result pagination
- [ ] Advanced filtering
- [ ] Search result caching
- [ ] Integration with tire inventory system
- [ ] Price comparison features

## Development

### Adding New Features

1. **Search Logic**: Edit `includes/search-handler.php`
2. **Styling**: Modify `assets/css/tirepoint-search-form.css`
3. **JavaScript**: Update `assets/js/tirepoint-search-form.js`
4. **Template**: Customize `templates/search-form.php`

### AJAX Endpoints

The plugin uses WordPress AJAX with the action `tpsf_search`. Add your search logic in the `handle_search()` method in the main plugin file.

### Hooks and Filters

```php
// Customize search results
add_filter('tpsf_search_results', 'my_custom_results', 10, 2);

// Modify search query
add_filter('tpsf_search_query_args', 'my_custom_query', 10, 2);
```

## Support

For support and customization requests, contact the development team.

## License

GPL v2 or later

## Version History

- **1.0.0** - Initial release with basic search functionality
