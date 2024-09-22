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

    protected function _register_controls() {

    	 wp_enqueue_style( 'twenty20-elementor-style', ZB_T20_URL . '/assets/css/twenty20.css' );
        wp_enqueue_script( 'twenty20-elementor-script', ZB_T20_URL .'/assets/js/jquery.twenty20.js', [ 'jquery' ], false, true );
        

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

    protected function render() {
        $settings = $this->get_settings_for_display();

        echo do_shortcode('[twenty20 img1="' . esc_attr( $settings['img1']['id'] ) . '" img2="' . esc_attr( $settings['img2']['id']) . '" offset="' . esc_attr( $settings['offset']['size'] ) . '" direction="' . esc_attr( $settings['direction'] ) . '" before="' . esc_attr( $settings['before'] ) . '" after="' . esc_attr( $settings['after'] ) . '" hover="' . esc_attr( $settings['hover'] ) . '"]');
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
        <div class="{{ containerClass }}" {{ orientationAttr }}>
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
            var $container = $('#{{ t20ID }} .twentytwenty-container');

            $container.twentytwenty({
                default_offset_pct: {{ offset }},
                orientation: '{{ direction }}'
            });

            <# if(beforeText) { #>
                $container.find('.twentytwenty-before-label').text('{{ beforeText }}');
            <# } else { #>
                $container.find('.twentytwenty-before-label').hide();
            <# } #>

            <# if(afterText) { #>
                $container.find('.twentytwenty-after-label').text('{{ afterText }}');
            <# } else { #>
                $container.find('.twentytwenty-after-label').hide();
            <# } #>
        });
    </script>
    <?php
}



}
