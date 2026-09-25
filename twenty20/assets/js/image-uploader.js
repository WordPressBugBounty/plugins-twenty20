jQuery(function($) {
    var frame;

    // Resolve the preview placeholder without fataling when localization is missing.
    function t20Placeholder($parent) {
        if (typeof twenty20_widget !== 'undefined' && twenty20_widget && twenty20_widget.placeholder_url) {
            return twenty20_widget.placeholder_url;
        }
        var fromForm = $parent && $parent.data ? $parent.data('placeholder') : '';
        return fromForm ? fromForm : '';
    }
    
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
            var url = attachment && attachment.url ? attachment.url : '';
            if (!url) {
                return;
            }
            
            // Update the field and preview
            if (isBeforeImage) {
                $parent.find('.mac-img-before').val(url);
                $parent.find('.mac-img-before').closest('p').find('img').attr('src', url);
            } else {
                $parent.find('.mac-img-after').val(url);
                $parent.find('.mac-img-after').closest('p').find('img').attr('src', url);
            }
        });
        
        frame.open();
    });
    
    // Handle removing images
    jQuery('body').on('click', '.mac-remove-image-before', function(e) {
        e.preventDefault();
        var $parent = $(this).closest('.mac_options_form');
        $parent.find('.mac-img-before').val('');
        var placeholder = t20Placeholder($parent);
        if (placeholder) {
            $parent.find('.mac-img-before').closest('p').find('img').attr('src', placeholder);
        }
    });
    
    jQuery('body').on('click', '.mac-remove-image-after', function(e) {
        e.preventDefault();
        var $parent = $(this).closest('.mac_options_form');
        $parent.find('.mac-img-after').val('');
        var placeholder = t20Placeholder($parent);
        if (placeholder) {
            $parent.find('.mac-img-after').closest('p').find('img').attr('src', placeholder);
        }
    });
});
