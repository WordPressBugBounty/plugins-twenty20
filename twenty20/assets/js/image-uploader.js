jQuery(function($) {
    var frame;
    
    // Handle click on "Select image" button
    jQuery('body').on('click', '.mac-upload_image_button', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $parent = $button.closest('.mac_options_form');
        var isBeforeImage = $button.data('t20') === 'img-t20-before';
        
        // Create a new media frame
        frame = wp.media({
            title: isBeforeImage ? 'Select Before Image' : 'Select After Image',
            library: {
                type: 'image'
            },
            button: {
                text: 'Use this image'
            },
            multiple: false
        });

        // When an image is selected in the media frame...
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            
            // Update the field and preview
            if (isBeforeImage) {
                $parent.find('.mac-img-before').val(attachment.url);
                $parent.find('.mac-img-before').closest('p').find('img').attr('src', attachment.url);
            } else {
                $parent.find('.mac-img-after').val(attachment.url);
                $parent.find('.mac-img-after').closest('p').find('img').attr('src', attachment.url);
            }
        });
        
        frame.open();
    });
    
    // Handle removing images
    jQuery('body').on('click', '.mac-remove-image-before', function(e) {
        e.preventDefault();
        var $parent = $(this).closest('.mac_options_form');
        $parent.find('.mac-img-before').val('');
        $parent.find('.mac-img-before').closest('p').find('img').attr('src', twenty20_widget.placeholder_url);
    });
    
    jQuery('body').on('click', '.mac-remove-image-after', function(e) {
        e.preventDefault();
        var $parent = $(this).closest('.mac_options_form');
        $parent.find('.mac-img-after').val('');
        $parent.find('.mac-img-after').closest('p').find('img').attr('src', twenty20_widget.placeholder_url);
    });
});