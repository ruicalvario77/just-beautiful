jQuery(document).ready(function($) {
    // Show popup when Customize button is clicked
    $('#jbc-customize-button').on('click', function(event) {
        event.preventDefault();
        $('#jbc-customization-popup').show();
    });

    // Hide popup when Close button is clicked
    $('.jbc-close-button').on('click', function() {
        $('#jbc-customization-popup').hide();
    });

    // Hide popup when clicking outside the content
    $('#jbc-customization-popup').on('click', function(event) {
        if (event.target === this) {
            $(this).hide();
        }
    });

    // Set visibility based on settings
    if (jbcSettings.allow_image) {
        $('.jbc-image-upload').show();
    }
    if (jbcSettings.allow_text) {
        $('.jbc-text-input').show();
    }

    // Populate placement select
    if (jbcSettings.allowed_zones && jbcSettings.allowed_zones.length > 0) {
        var select = $('#jbc-placement-select');
        select.empty();
        jbcSettings.allowed_zones.forEach(function(zone) {
            select.append('<option value="' + zone + '">' + zone + '</option>');
        });
    } else {
        $('.jbc-placement-selection').hide();
    }
});