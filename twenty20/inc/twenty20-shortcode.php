<?php

function twenty20_zb_sanitize_xss_offset( $input ) {
  $output = str_replace( '})});alert(/XSS-offset/)//', '', $input );
  return $output;
}

function twenty20_shortcode_init( $atts) {
  $atts = shortcode_atts(
    array(
      'img1' => '',
      'img2' => '',
      'offset' => '0.5',
      'direction' => 'horizontal',
      'width' => '',
      'align' => '',
      'before' => '',
      'after' => '',
      'hover' => 'false',
    ), $atts, 'twenty20'
  );

  static $i = 1;

  $t20ID = "twenty20-" . $i;

  $isVertical = "";
  $data_vertical = "";
  $isLeft = "";
  $isRight = "";

  if (esc_attr( $atts['align'] ) == "right"){
    $isRight = " float: right; margin-left: 20px;";
    if (empty($atts['width'])){ $atts['width'] = "width: 50%;"; }
  }

  if (esc_attr( $atts['align'] ) == "left"){
    $isLeft = " float: left; margin-right: 20px;";
    if (empty($atts['width'])){ $atts['width'] = "width: 50%;"; }
  }

  if( is_numeric( $atts['width'] ) ){
    if (empty($atts['width'])){
      $atts['width'] = "width: 100% !important; clear: both;";
    }else{
      $atts['width'] = "width: " . $atts['width'] . '%;';
    }
  }else{
    $atts['width'] = "width: 100% !important; clear: both;";
  }

  if($atts['direction'] == "vertical"){
    $isVertical = ' data-orientation=vertical';
    $data_vertical = ", orientation: 'vertical'";
  }
  if( $atts['hover'] === "true"){
    $isHover = ',move_slider_on_hover: true';
    $yesHover = "t20-hover";
  }else{
    $isHover = '';
    $yesHover = '';
  }

  $script = "";
  if(!empty($atts['img1']) && !empty($atts['img2'])){
    // Get alt text from media library
    $img1_alt = get_post_meta($atts['img1'], '_wp_attachment_image_alt', true);
    $img2_alt = get_post_meta($atts['img2'], '_wp_attachment_image_alt', true);

    // If no alt text is set, use default values
    $img1_alt = !empty($img1_alt) ? $img1_alt : 'Before image';
    $img2_alt = !empty($img2_alt) ? $img2_alt : 'After image';

    $output = '<div id="'.esc_attr($t20ID).'" class="twenty20" style="'. esc_attr($atts['width'] . $isLeft . $isRight) . '">';
    $output .= '<div class="twentytwenty-container '. esc_attr( $t20ID . ' ' . $yesHover ) .'"' . esc_attr( $isVertical ) . '>';
    $output .= '<img src="'. esc_url( wp_get_attachment_url( $atts['img1'] ) ) .'" alt="'. esc_attr($img1_alt) .'" />';
    $output .= '<img src="'. esc_url( wp_get_attachment_url( $atts['img2'] ) ) .'" alt="'. esc_attr($img2_alt) .'" />';
    $output .= '</div></div>';
    $script .= '<script>jQuery(function($) {
        // Wait for images to load before initializing
        var $container = $(".twentytwenty-container.' . esc_js($t20ID) . '");
        var $images = $container.find("img");
        var loaded = 0;
        
        function initTwenty20() {
            if($container.data("twenty20-init")) return;
            
            if($container.attr("data-orientation") === "vertical") {
                $container.twentytwenty({
                    default_offset_pct: ' . esc_js($atts['offset']) . $isHover . ',
                    orientation: "vertical"
                });
            } else {
                $container.twentytwenty({
                    default_offset_pct: ' . esc_js($atts['offset']) . $isHover . '
                });
            }
            $container.data("twenty20-init", true);
        }

        // Check if images are already cached
        if($images.get(0).complete && $images.get(1).complete) {
            initTwenty20();
        } else {
            // Wait for images to load
            $images.on("load", function() {
                loaded++;
                if(loaded === $images.length) {
                    initTwenty20();
                }
            });
            
            // Fallback if images fail to load
            setTimeout(function() {
                if(!$container.data("twenty20-init")) {
                    initTwenty20();
                }
            }, 2000);
        }
        
        // Handle before/after labels
        ';
        
        if($atts['before']){
            $script .= '$(".' . twenty20_zb_sanitize_xss_offset(esc_js($t20ID)) . ' .twentytwenty-before-label").html("' . twenty20_zb_sanitize_xss_offset(esc_js($atts['before'])) . '");';
        } else {
            $script .= '$(".' . twenty20_zb_sanitize_xss_offset(esc_js($t20ID)) . ' .twentytwenty-overlay").hide();';
        }
        
        if($atts['after']){
            $script .= '$(".' . twenty20_zb_sanitize_xss_offset(esc_js($t20ID)) . ' .twentytwenty-after-label").html("' . twenty20_zb_sanitize_xss_offset(esc_js($atts['after'])) . '");';
        } else {
            $script .= '$(".' . twenty20_zb_sanitize_xss_offset(esc_js($t20ID)) . ' .twentytwenty-overlay").hide();';
        }
        
        $script .= '});</script>';
    
  }else{
    $output = '<div class="twenty20" style="color: red;">Twenty20 need two images.</div>';
  }

    $i++;
    // Add the JavaScript initialization to the footer
    add_action('wp_footer', function() use ($script) { echo $script; }, 20);
    return $output;
}
add_shortcode( 'twenty20', 'twenty20_shortcode_init' );