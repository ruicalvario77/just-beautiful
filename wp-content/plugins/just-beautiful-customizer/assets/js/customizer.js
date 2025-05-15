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

    // Image upload functionality
    var mediaUploader;
    $('#jbc-upload-image').on('click', function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media({
            title: 'Upload Image',
            button: {
                text: 'Use this image'
            },
            multiple: false // Single image upload only
        }).on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            // Validate file type (PNG or JPG only)
            if (attachment.mime !== 'image/png' && attachment.mime !== 'image/jpeg') {
                alert('Please upload a PNG or JPG image.');
                return;
            }
            
            // Validate file size (<5MB, i.e., 5 * 1024 * 1024 bytes)
            if (attachment.size > 5 * 1024 * 1024) {
                alert('Image must be less than 5MB.');
                return;
            }
            
            // Display the image preview
            $('#jbc-image-preview').html('<img src="' + attachment.url + '" style="max-width: 100%;">');
            
            // Optionally store the image URL or ID for later use (e.g., in live preview)
            console.log('Uploaded Image URL:', attachment.url);
            console.log('Uploaded Image ID:', attachment.id);
        }).open();
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

    // Load Google Fonts
    if (jbcSettings.fonts && jbcSettings.fonts.length > 0) {
        WebFont.load({
            google: {
                families: jbcSettings.fonts
            }
        });
    }

    // Populate font dropdown with styled options
    jbcSettings.fonts.forEach(function(font) {
        var option = $('<option>').val(font).text(font).css('font-family', font);
        $('#jbc-font-select').append(option);
    });

    // Set initial font and color
    $('#jbc-font-select').val(jbcSettings.fonts[0]);
    $('#jbc-text-input').css('font-family', jbcSettings.fonts[0]);
    var initialColor = $('#jbc-color-picker').val();
    $('#jbc-text-input').css('color', initialColor);

    // Character counter
    var maxLength = 50;
    $('#jbc-text-input').on('input', function() {
        var remaining = maxLength - $(this).val().length;
        $('#jbc-char-count').text(remaining + ' characters left');
    });
    $('#jbc-char-count').text(maxLength + ' characters left');

    // Font and color change handlers
    $('#jbc-font-select').on('change', function() {
        var selectedFont = $(this).val();
        $('#jbc-text-input').css('font-family', selectedFont);
    });
    $('#jbc-color-picker').on('input', function() {
        var selectedColor = $(this).val();
        $('#jbc-text-input').css('color', selectedColor);
    });
});