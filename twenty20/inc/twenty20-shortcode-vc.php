<?php
  /*
   * Visual Composer Support
   */
add_action( 'vc_before_init', 'twenty20_shortcode_integrateWithVC' );
function twenty20_shortcode_integrateWithVC() {
  vc_map( array(
    "name"      =>  __( "Twenty20", 'zb_twenty20' ),
    "base"      =>  "twenty20",
    "icon"      =>  "icon-twenty20",
    "category"  =>  __( "Content", 'zb_twenty20'),
    "params"    =>  array(
      array(
        'type'        =>  'attach_image',
        'heading'     =>  __( 'Before Image', 'zb_twenty20' ),
        'param_name'  =>  'img1',
        "description" =>  __("Select Before Image.", 'zb_twenty20'),
      ),
      array(
        'type'        =>  'attach_image',
        'heading'     =>  __( 'After Image', 'zb_twenty20' ),
        'param_name'  =>  'img2',
        "description" =>  __("Select After Image.", 'zb_twenty20'),
      ),
      array(
        'type'        =>  'textfield',
        'heading'     =>  __( 'Before Text', 'zb_twenty20' ),
        'param_name'  =>  'before',
        "description" =>  __("Twenty20 before text.", 'zb_twenty20'),
      ),
      array(
        'type'        =>  'textfield',
        'heading'     =>  __( 'After Text', 'zb_twenty20' ),
        'param_name'  =>  'after',
        "description" =>  __("Twenty20 after text.", 'zb_twenty20'),
      ),
      array(
        'type'        =>  'textfield',
        'heading'     =>  __( 'Width', 'zb_twenty20' ),
        'param_name'  =>  'width',
        "description" =>  __("Twenty20 container width.", 'zb_twenty20'),
      ),

      array(
        'type'        =>  'dropdown',
        'heading'     =>  __( 'Direction', 'zb_twenty20' ),
        'param_name'  =>  'direction',
        "description" =>  __("Select twenty20 slider direction", 'zb_twenty20'),
        "value"       =>  array(
          'Horizontal'  =>  'default',
          'Vertical'    =>  'vertical'
        )
      ),
      array(
        'type'          =>  'dropdown',
        'heading'       =>  __( 'Offset', 'zb_twenty20' ),
        'param_name'    =>  'offset',
        "description"   =>  __("Slider offset", 'zb_twenty20'),
        "value"         =>  array(
          '0.5' =>  '0.5',
          '0.1' =>  '0.1',
          '0.2' =>  '0.2',
          '0.3' =>  '0.3',
          '0.4' =>  '0.4',
          '0.5' =>  '0.5',
          '0.6' =>  '0.6',
          '0.7' =>  '0.7',
          '0.8' =>  '0.8',
          '0.9' =>  '0.9',
          '1'   =>  '1.0',
        )
      ),
      array(
        'type'        =>  'dropdown',
        'heading'     =>  __( 'Mouseover', 'zb_twenty20' ),
        'param_name'  =>  'hover',
        "description" =>  __("Move slider on mouse hover?", 'zb_twenty20'),
        "value"       =>  array(
          'No'  =>  'false',
          'Yes'    =>  'true'
        )
      ),
      array(
        'type'          =>  'dropdown',
        'heading'       =>  __( 'Alignment', 'zb_twenty20' ),
        'param_name'    =>  'align',
        "description"   =>  __("Set alignment", 'zb_twenty20'),
        "value"         =>  array(
          'None'  =>  'none',
          'Right' =>  'right',
          'Left'  =>  'left'
        )
      ),
    ),
  ));
}
if ( class_exists( 'WPBakeryShortCode' ) ) {
  class WPBakeryShortCode_Twenty20_Shortcode extends WPBakeryShortCode { }
}
