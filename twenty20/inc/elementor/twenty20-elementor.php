<?php
class Twenty20_Image_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return "twenty20";
	}

	public function get_title() {
		return __( 'Twenty20 image<br/>Before-After', 'zb_twenty20' );
	}

	public function get_icon() {
		return 'eicon-image-before-after';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Twenty20 Content', 'zb_twenty20' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'img1',
			[
				'label' => __( 'Before Image', 'zb_twenty20' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				]
			]
		);

		$this->add_control(
			'img2',
			[
				'label' => __( 'After Image', 'zb_twenty20' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				]
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
			'direction',
			[
				'label' => __( 'Direction', 'zb_twenty20' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'default' => __( 'Horizontal', 'zb_twenty20' ),
					'vertical' => __( 'Vertical', 'zb_twenty20' ),
				],
				'default' => 'default',
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

		$this->add_control(
			'offset',
			[
				'label' => __( 'Offset', 'zb_twenty20' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'0.1' => __( '0.1', 'zb_twenty20' ),
					'0.2' => __( '0.2', 'zb_twenty20' ),
					'0.3' => __( '0.3', 'zb_twenty20' ),
					'0.4' => __( '0.4', 'zb_twenty20' ),
					'0.5' => __( '0.5', 'zb_twenty20' ),
					'0.6' => __( '0.6', 'zb_twenty20' ),
					'0.7' => __( '0.7', 'zb_twenty20' ),
					'0.8' => __( '0.8', 'zb_twenty20' ),
					'0.9' => __( '0.9', 'zb_twenty20' ),
					'1' => __( '1.0', 'zb_twenty20' ),
				],
				'default' => '0.5',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$img1_id = esc_attr($settings['img1']['id']);
		$img2_id = esc_attr($settings['img2']['id']);
		$offset = esc_attr($settings['offset']);
		$before = isset($settings['before']) ? ' before="' . esc_attr($settings['before']) . '"' : '';
		$after = isset($settings['after']) ? ' after="' . esc_attr($settings['after']) . '"' : '';
		$direction = $settings['direction'] === 'default' ? '' : ' direction="' . esc_attr($settings['direction']) . '"';
		$hover = $settings['hover'] === 'false' ? '' : ' hover="' . esc_attr($settings['hover']) . '"';

		echo do_shortcode('[twenty20 img1="' . $img1_id . '" img2="' . $img2_id . '" offset="' . $offset . '"' . $direction . $before . $after . $hover . ']');
	}

	protected function _content_template() {}

}