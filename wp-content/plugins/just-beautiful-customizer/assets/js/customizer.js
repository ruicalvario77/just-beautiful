jQuery(document).ready(function($) {
    // Show popup when Customize button is clicked
    $('#jbc-customize-button').on('click', function() {
        $('#jbc-customization-popup').show();
    });

    // Hide popup when Close button is clicked
    $('#jbc-close-popup').on('click', function() {
        $('#jbc-customization-popup').hide();
    });
});