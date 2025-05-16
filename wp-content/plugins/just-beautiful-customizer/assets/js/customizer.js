jQuery(document).ready(function($) {
    var currentPlacement = null;
    var customizations = {};

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
            multiple: false
        }).on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();

            // Validate file type (PNG or JPG only)
            if (attachment.mime !== 'image/png' && attachment.mime !== 'image/jpeg') {
                alert('Please upload a PNG or JPG image.');
                return;
            }

            // Validate file size (<5MB)
            if (attachment.size > 5 * 1024 * 1024) {
                alert('Image must be less than 5MB.');
                return;
            }

            // Display the image preview
            $('#jbc-image-preview').html('<img src="' + attachment.url + '" style="max-width: 100%;">');

            // Store image for current placement
            if (currentPlacement) {
                customizations[currentPlacement] = customizations[currentPlacement] || {};
                customizations[currentPlacement].image = { url: attachment.url, id: attachment.id };
                console.log('After image upload:', customizations[currentPlacement]);
                updatePreview();
            }
        }).open();
    });

    // Hide popup when clicking outside
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
        // Set initial placement
        currentPlacement = jbcSettings.allowed_zones[0];
        select.val(currentPlacement);
        loadCustomization(currentPlacement);
        updatePreview();
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

    // Populate font dropdown
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
        if (currentPlacement) {
            customizations[currentPlacement] = customizations[currentPlacement] || {};
            customizations[currentPlacement].text = $(this).val();
            console.log('After text input:', customizations[currentPlacement]);
            updatePreview();
        }
    });
    $('#jbc-char-count').text(maxLength + ' characters left');

    // Font and color change handlers
    $('#jbc-font-select').on('change', function() {
        var selectedFont = $(this).val();
        $('#jbc-text-input').css('font-family', selectedFont);
        if (currentPlacement) {
            customizations[currentPlacement] = customizations[currentPlacement] || {};
            customizations[currentPlacement].font = selectedFont;
            updatePreview();
        }
    });
    $('#jbc-color-picker').on('input', function() {
        var selectedColor = $(this).val();
        $('#jbc-text-input').css('color', selectedColor);
        if (currentPlacement) {
            customizations[currentPlacement] = customizations[currentPlacement] || {};
            customizations[currentPlacement].color = selectedColor;
            updatePreview();
        }
    });

    // Placement change handler
    $('#jbc-placement-select').on('change', function() {
        if (currentPlacement) {
            saveCustomization(currentPlacement);
        }
        currentPlacement = $(this).val();
        loadCustomization(currentPlacement);
        updatePreview();
    });

    // Save current customization
    function saveCustomization(placement) {
        customizations[placement] = {
            image: customizations[placement] ? customizations[placement].image : null,
            text: $('#jbc-text-input').val(),
            font: $('#jbc-font-select').val(),
            color: $('#jbc-color-picker').val()
        };
    }

    // Load customization for a placement
    function loadCustomization(placement) {
        var cust = customizations[placement] || {};
        $('#jbc-text-input').val(cust.text || '');
        $('#jbc-font-select').val(cust.font || jbcSettings.fonts[0]);
        $('#jbc-color-picker').val(cust.color || '#000000');
        $('#jbc-image-preview').html(cust.image ? '<img src="' + cust.image.url + '" style="max-width: 100%;">' : '');
    }

    // Update preview function
    function updatePreview() {
        console.log('Customizations:', customizations);
        var container = $('#jbc-overlay-container');
        container.empty(); // Clear existing overlays

        var image = $('#jbc-base-image')[0];
        var nw = image.naturalWidth;
        var nh = image.naturalHeight;

        if (nw && nh) {
            for (var placement in customizations) {
                if (customizations.hasOwnProperty(placement)) {
                    var zone = jbcSettings.zones[placement];
                    if (zone) {
                        var leftPc = (zone.x / nw) * 100;
                        var topPc = (zone.y / nh) * 100;
                        var widthPc = (zone.width / nw) * 100;
                        var heightPc = (zone.height / nh) * 100;

                        var overlayDiv = $('<div>').css({
                            position: 'absolute',
                            left: leftPc + '%',
                            top: topPc + '%',
                            width: widthPc + '%',
                            height: heightPc + '%',
                            overflow: 'hidden',
                            display: 'flex',              // Center contents
                            flexDirection: 'column',      // Stack image and text vertically
                            justifyContent: 'center',     // Center vertically
                            alignItems: 'center'          // Center horizontally
                        });

                        var cust = customizations[placement];
                        if (cust.image) {
                            console.log('Adding image for', placement);
                            var img = $('<img>').attr('src', cust.image.url).css({
                                width: '100%',
                                height: '100%',
                                objectFit: 'contain'
                            });
                            overlayDiv.append(img);
                        }
                        if (cust.text) {
                            console.log('Adding text for', placement, ':', cust.text);
                            var textDiv = $('<div>').text(cust.text).css({
                                fontFamily: cust.font || 'Arial',
                                color: cust.color || '#000000',
                                fontSize: '20px',
                                whiteSpace: 'nowrap',
                                background: 'rgba(255, 255, 255, 0.5)', // Semi-transparent background
                                padding: '5px',
                                border: '1px solid #000',                // Border for visibility
                                position: 'relative',
                                zIndex: 10                               // Ensure text is above image
                            });
                            overlayDiv.append(textDiv);
                        }

                        container.append(overlayDiv);
                    }
                }
            }
        }
    }

    // Update preview when base image loads
    $('#jbc-base-image').on('load', function() {
        updatePreview();
    });
});