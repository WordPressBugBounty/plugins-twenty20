<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Elementor_Twenty20_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'twenty20_widget';
    }

    public function get_title() {
        return __( 'Twenty20 Before-After', 'plugin-name' );
    }

    public function get_icon() {
        return 'eicon-image-before-after';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    // Method to enqueue scripts and styles
    public function get_style_depends() {
        return [ 'twenty20-elementor-style' ]; // Handle of the CSS file
    }

    public function get_script_depends() {
        return [ 'twenty20-elementor-script' ]; // Handle of the JS file
    }

    protected function register_controls() {

    	 wp_enqueue_style( 'twenty20-elementor-style', ZB_T20_URL . '/assets/css/twenty20.css', array(), ZB_T20_VER );
        wp_enqueue_script( 'twenty20-elementor-script', ZB_T20_URL .'/assets/js/jquery.twenty20.js', [ 'jquery' ], ZB_T20_VER, true );
        

        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'plugin-name' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'img1',
            [
                'label' => __( 'Image 1', 'plugin-name' ),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
            'img2',
            [
                'label' => __( 'Image 2', 'plugin-name' ),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $this->add_control(
			'before',
			[
				'label' => __( 'Before Text', 'zb_twenty20' ),
				'type' => \Elementor\Controls_Manager::TEXT
			]
		);

		$this->add_control(
			'after',
			[
				'label' => __( 'After Text', 'zb_twenty20' ),
				'type' => \Elementor\Controls_Manager::TEXT
			]
		);

        $this->add_control(
            'offset',
            [
                'label' => __( 'Offset', 'plugin-name' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.5,
                    'unit' => '',
                ],
                'range' => [
                    '' => [
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.01,
                    ],
                ],
            ]
        );

        $this->add_control(
            'direction',
            [
                'label' => __( 'Direction', 'plugin-name' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'horizontal',
                'options' => [
                    'horizontal' => __( 'Horizontal', 'plugin-name' ),
                    'vertical' => __( 'Vertical', 'plugin-name' ),
                ],
            ]
        );

        $this->add_control(
			'hover',
			[
				'label' => __( 'Mouse over', 'zb_twenty20' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'true' => __( 'Yes', 'zb_twenty20' ),
					'false' => __( 'No', 'zb_twenty20' ),
				],
				'default' => 'false',
			]
		);

        $this->end_controls_section();
    }

    protected function _register_controls() {
        // Backward compatibility for old Elementor versions.
        if ( method_exists( $this, 'register_controls' ) ) {
            $this->register_controls();
        }
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Sanitize everything before passing to the shortcode (shortcode re-validates as well).
        $img1_id = isset( $settings['img1']['id'] ) ? absint( $settings['img1']['id'] ) : 0;
        $img2_id = isset( $settings['img2']['id'] ) ? absint( $settings['img2']['id'] ) : 0;

        $offset_raw = isset( $settings['offset']['size'] ) ? $settings['offset']['size'] : ( isset( $settings['offset'] ) && is_numeric( $settings['offset'] ) ? $settings['offset'] : 0.5 );
        $offset = is_numeric( $offset_raw ) ? (float) $offset_raw : 0.5;
        $offset = max( 0.1, min( 1.0, $offset ) );

        $direction = ( isset( $settings['direction'] ) && 'vertical' === $settings['direction'] ) ? 'vertical' : 'horizontal';
        $hover = ( isset( $settings['hover'] ) && 'true' === $settings['hover'] ) ? 'true' : 'false';
        $before = isset( $settings['before'] ) ? sanitize_text_field( $settings['before'] ) : '';
        $after = isset( $settings['after'] ) ? sanitize_text_field( $settings['after'] ) : '';

        echo do_shortcode( sprintf(
            '[twenty20 img1="%d" img2="%d" direction="%s" offset="%s" before="%s" after="%s" hover="%s"]',
            $img1_id,
            $img2_id,
            esc_attr( $direction ),
            esc_attr( (string) $offset ),
            esc_attr( $before ),
            esc_attr( $after ),
            esc_attr( $hover )
        ) );
    }

    protected function _content_template() {
    ?>
    <#
    // Ensure the necessary controls are set or provide default values
    var img1Url = settings.img1.url ? settings.img1.url : '<?php echo \Elementor\Utils::get_placeholder_image_src(); ?>';
    var img2Url = settings.img2.url ? settings.img2.url : '<?php echo \Elementor\Utils::get_placeholder_image_src(); ?>';
    var offset = settings.offset && settings.offset.size !== '' ? settings.offset.size : 0.5;
    var direction = settings.direction ? settings.direction : 'horizontal';
    var beforeText = settings.before ? settings.before : '';
    var afterText = settings.after ? settings.after : '';
    var hover = settings.hover === 'true' ? 'hover' : '';
    var t20ID = 'twenty20-' + Math.floor(Math.random() * 10000);

    var containerClass = 'twentytwenty-container ' + t20ID + ' ' + hover;
    var orientationAttr = direction === 'vertical' ? 'data-orientation="vertical"' : '';
    
    // Ensure default styles are applied even when certain controls are not set
    var containerStyles = offset ? 'width: ' + (offset * 100) + '%;' : '';
    #>

    <div id="{{ t20ID }}" class="twenty20">
        <div class="{{ containerClass }}" {{ orientationAttr }} data-offset="{{ offset }}" data-hover="{{ hover }}" data-before="{{ beforeText }}" data-after="{{ afterText }}">
            <img src="{{ img1Url }}" alt="Before Image" />
            <img src="{{ img2Url }}" alt="After Image" />
        </div>
        <# if (beforeText) { #>
            <span class="twentytwenty-before-label">{{ beforeText }}</span>
        <# } #>
        <# if (afterText) { #>
            <span class="twentytwenty-after-label">{{ afterText }}</span>
        <# } #>
    </div>

    <style>
        #{{ t20ID }} .twentytwenty-container {
            position: relative;
            overflow: hidden;
        }

        #{{ t20ID }} .twentytwenty-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        #{{ t20ID }} .twentytwenty-before-label,
        #{{ t20ID }} .twentytwenty-after-label {
            position: absolute;
            top: 10px;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            padding: 5px;
        }

        #{{ t20ID }} .twentytwenty-before-label {
            left: 10px;
        }

        #{{ t20ID }} .twentytwenty-after-label {
            right: 10px;
        }

        <# if( hover ) { #>
            #{{ t20ID }} .twentytwenty-container:hover .twentytwenty-overlay {
                width: 100%;
            }
        <# } #>

        <# if( direction === 'vertical' ) { #>
            #{{ t20ID }} .twentytwenty-container {
                flex-direction: column;
            }
        <# } #>
    </style>

    <script>
        jQuery(document).ready(function($) {
            // Editor preview only: read sanitized values from data attributes, never interpolate raw text into JS.
            var $container = $('#{{ t20ID }} .twentytwenty-container');
            if (!$container.length) return;
            var offset = parseFloat($container.attr('data-offset'));
            if (isNaN(offset) || offset < 0.1 || offset > 1) { offset = 0.5; }
            var cfg = { default_offset_pct: offset };
            if ($container.attr('data-orientation') === 'vertical') { cfg.orientation = 'vertical'; }
            if ($container.attr('data-hover') === 'hover') { cfg.move_slider_on_hover = true; }
            try { $container.twentytwenty(cfg); } catch(e) {}
            // Labels are already rendered escaped in HTML above; just toggle visibility.
            var before = $container.attr('data-before') || '';
            var after = $container.attr('data-after') || '';
            if (!before) { $container.find('.twentytwenty-before-label').hide(); }
            if (!after) { $container.find('.twentytwenty-after-label').hide(); }
        });
    </script>
    <?php
}



}
