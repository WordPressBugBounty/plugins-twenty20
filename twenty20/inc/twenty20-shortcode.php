<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

function twenty20_shortcode_init( $atts ) {
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

  $t20ID = 'twenty20-' . $i;

  // --- Sanitize: images must be attachment IDs (prevents arbitrary URL / JS schemes). ---
  $img1_id = absint( $atts['img1'] );
  $img2_id = absint( $atts['img2'] );

  // --- Sanitize: offset must be a float clamped to 0.1 - 1.0. ---
  $offset = is_numeric( $atts['offset'] ) ? (float) $atts['offset'] : 0.5;
  $offset = max( 0.1, min( 1.0, $offset ) );

  // --- Whitelist: direction / align / hover. ---
  $direction = ( isset( $atts['direction'] ) && 'vertical' === strtolower( trim( $atts['direction'] ) ) ) ? 'vertical' : 'horizontal';
  $is_vertical = ( 'vertical' === $direction );

  $align_raw = isset( $atts['align'] ) ? strtolower( trim( $atts['align'] ) ) : '';
  $align = in_array( $align_raw, array( '', 'none', 'left', 'right' ), true ) ? $align_raw : '';
  if ( 'none' === $align ) {
    $align = '';
  }

  $hover = ( isset( $atts['hover'] ) && 'true' === strtolower( trim( $atts['hover'] ) ) ) ? 'true' : 'false';
  $is_hover = ( 'true' === $hover );

  // --- Sanitize: width. Accepts plain number (= %), or number + % / px. Anything else falls back to default. ---
  $width_style = 'width: 100% !important; clear: both;';
  $width_raw = isset( $atts['width'] ) ? trim( (string) $atts['width'] ) : '';
  if ( '' !== $width_raw ) {
    if ( is_numeric( $width_raw ) ) {
      $w = (float) $width_raw;
      if ( $w > 0 && $w <= 100 ) {
        // Plain number historically meant percent.
        $width_style = 'width: ' . $w . '%;';
      }
    } elseif ( preg_match( '/^(\d{1,4}(?:\.\d+)?)(%|px)$/i', $width_raw, $m ) ) {
      $num = (float) $m[1];
      $unit = strtolower( $m[2] );
      if ( '%' === $unit && $num > 0 && $num <= 100 ) {
        $width_style = 'width: ' . $num . '%;';
      } elseif ( 'px' === $unit && $num > 0 && $num <= 3000 ) {
        $width_style = 'width: ' . $num . 'px;';
      }
    }
  }

  // Alignment helpers (fixed CSS fragments only — no user input inside).
  $isLeft = '';
  $isRight = '';
  if ( 'right' === $align ) {
    $isRight = ' float: right; margin-left: 20px;';
    if ( '' === $width_raw ) {
      $width_style = 'width: 50%;';
    }
  }
  if ( 'left' === $align ) {
    $isLeft = ' float: left; margin-right: 20px;';
    if ( '' === $width_raw ) {
      $width_style = 'width: 50%;';
    }
  }

  // --- Sanitize: before / after labels as plain text (no HTML — prevents Stored XSS). ---
  $before = isset( $atts['before'] ) ? sanitize_text_field( $atts['before'] ) : '';
  $after  = isset( $atts['after'] ) ? sanitize_text_field( $atts['after'] ) : '';

  $script = '';
  $output = '';

  if ( ! empty( $img1_id ) && ! empty( $img2_id ) ) {
    $img1_url = wp_get_attachment_url( $img1_id );
    $img2_url = wp_get_attachment_url( $img2_id );

    if ( empty( $img1_url ) || empty( $img2_url ) ) {
      $output = '<div class="twenty20" style="color: red;">Twenty20 need two images.</div>';
    } else {
      // Get alt text from media library.
      $img1_alt = get_post_meta( $img1_id, '_wp_attachment_image_alt', true );
      $img2_alt = get_post_meta( $img2_id, '_wp_attachment_image_alt', true );

      // If no alt text is set, use default values.
      $img1_alt = ! empty( $img1_alt ) ? sanitize_text_field( $img1_alt ) : 'Before image';
      $img2_alt = ! empty( $img2_alt ) ? sanitize_text_field( $img2_alt ) : 'After image';

      $container_class = 'twentytwenty-container ' . $t20ID;
      if ( $is_hover ) {
        $container_class .= ' t20-hover';
      }

      $output = '<div id="' . esc_attr( $t20ID ) . '" class="twenty20" style="' . esc_attr( $width_style . $isLeft . $isRight ) . '">';
      $output .= '<div class="' . esc_attr( $container_class ) . '"';
      if ( $is_vertical ) {
        $output .= ' data-orientation="vertical"';
      }
      // Pass dynamic values via data attributes + JSON (never inline user input in JS strings).
      $output .= ' data-offset="' . esc_attr( (string) $offset ) . '"';
      $output .= ' data-before="' . esc_attr( $before ) . '"';
      $output .= ' data-after="' . esc_attr( $after ) . '"';
      $output .= ' data-hover="' . esc_attr( $hover ) . '">';
      $output .= '<img src="' . esc_url( $img1_url ) . '" alt="' . esc_attr( $img1_alt ) . '" loading="lazy" />';
      $output .= '<img src="' . esc_url( $img2_url ) . '" alt="' . esc_attr( $img2_alt ) . '" loading="lazy" />';
      $output .= '</div></div>';

      // Static initializer: reads data-* attributes, uses .text() (never .html()),
      // numeric offset via parseFloat with fallback. JSON-encoded ID prevents breakout.
      $script .= '<script>jQuery(function($) {'
        . 'var id = ' . wp_json_encode( $t20ID ) . ';'
        . 'var $container = $(".twentytwenty-container." + id);'
        . 'if (!$container.length || $container.data("twenty20-init")) return;'
        . 'var offset = parseFloat($container.attr("data-offset"));'
        . 'if (isNaN(offset) || offset < 0.1 || offset > 1) { offset = 0.5; }'
        . 'var isVertical = $container.attr("data-orientation") === "vertical";'
        . 'var isHover = $container.attr("data-hover") === "true";'
        . 'var cfg = { default_offset_pct: offset };'
        . 'if (isVertical) { cfg.orientation = "vertical"; }'
        . 'if (isHover) { cfg.move_slider_on_hover = true; }'
        . 'function initTwenty20() {'
        . 'if ($container.data("twenty20-init")) return;'
        . 'try { $container.twentytwenty(cfg); } catch(e) {}'
        . '$container.data("twenty20-init", true);'
        . 'var before = $container.attr("data-before") || "";'
        . 'var after = $container.attr("data-after") || "";'
        . 'var $overlay = $container.find(".twentytwenty-overlay");'
        . 'if (before) { $container.find(".twentytwenty-before-label").text(before); }'
        . 'else { $container.find(".twentytwenty-before-label").hide(); }'
        . 'if (after) { $container.find(".twentytwenty-after-label").text(after); }'
        . 'else { $container.find(".twentytwenty-after-label").hide(); }'
        . 'if (!before && !after) { $overlay.hide(); }'
        . '}'
        . 'var $images = $container.find("img");'
        . 'var loaded = 0;'
        . 'function maybeInit() { loaded++; if (loaded >= $images.length) { initTwenty20(); } }'
        . 'if ($images.length !== 2) { return; }'
        . 'var allComplete = true;'
        . '$images.each(function(){ if (!this.complete || typeof this.naturalWidth !== "undefined" && this.naturalWidth === 0) { allComplete = false; } });'
        . 'if (allComplete) { initTwenty20(); }'
        . 'else { $images.on("load", maybeInit);'
        . 'setTimeout(function(){ if (!$container.data("twenty20-init")) { initTwenty20(); } }, 2000); }'
        . '});</script>';
    }
  } else {
    $output = '<div class="twenty20" style="color: red;">Twenty20 need two images.</div>';
  }

  $i++;
  // Add the JavaScript initialization to the footer.
  if ( '' !== $script ) {
    add_action( 'wp_footer', function() use ( $script ) {
      // $script is built from static JS + wp_json_encode only — no raw user input.
      echo $script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }, 20 );
  }
  return $output;
}
add_shortcode( 'twenty20', 'twenty20_shortcode_init' );
