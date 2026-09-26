<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Gutenberg block: Twenty20 Before-After.
 *
 * Rendering is delegated to twenty20_shortcode_init() so the block inherits
 * the same input validation / output escaping as the shortcode (2.0.6 XSS fix).
 * Attributes are pre-sanitized here as defense in depth.
 */
function twenty20_block_render( $attributes ) {
  if ( ! function_exists( 'twenty20_shortcode_init' ) ) {
    return '';
  }

  $attributes = is_array( $attributes ) ? $attributes : array();

  $offset = 0.5;
  if ( isset( $attributes['offset'] ) && is_numeric( $attributes['offset'] ) ) {
    $offset = (float) $attributes['offset'];
  }

  $direction = ( isset( $attributes['direction'] ) && 'vertical' === $attributes['direction'] ) ? 'vertical' : 'horizontal';

  $align = isset( $attributes['align'] ) ? strtolower( trim( (string) $attributes['align'] ) ) : '';
  if ( ! in_array( $align, array( '', 'left', 'right' ), true ) ) {
    $align = '';
  }

  $hover = ( isset( $attributes['hover'] ) && 'true' === strtolower( trim( (string) $attributes['hover'] ) ) ) ? 'true' : 'false';

  return twenty20_shortcode_init(
    array(
      'img1'      => isset( $attributes['img1'] ) ? absint( $attributes['img1'] ) : 0,
      'img2'      => isset( $attributes['img2'] ) ? absint( $attributes['img2'] ) : 0,
      'offset'    => $offset,
      'direction' => $direction,
      'width'     => isset( $attributes['width'] ) ? sanitize_text_field( $attributes['width'] ) : '',
      'align'     => $align,
      'before'    => isset( $attributes['before'] ) ? sanitize_text_field( $attributes['before'] ) : '',
      'after'     => isset( $attributes['after'] ) ? sanitize_text_field( $attributes['after'] ) : '',
      'hover'     => $hover,
    )
  );
}

function twenty20_register_block() {
  if ( ! function_exists( 'register_block_type' ) ) {
    return;
  }

  wp_register_script(
    'twenty20-block-editor',
    ZB_T20_URL . '/blocks/twenty20/editor.js',
    array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-i18n', 'jquery' ),
    ZB_T20_VER,
    true
  );

  wp_register_style(
    'twenty20-block-editor-style',
    ZB_T20_URL . '/blocks/twenty20/editor.css',
    array( 'wp-edit-blocks' ),
    ZB_T20_VER
  );

  register_block_type(
    ZB_T20_PATH . 'blocks/twenty20/block.json',
    array(
      'render_callback' => 'twenty20_block_render',
    )
  );
}
add_action( 'init', 'twenty20_register_block' );

/**
 * The live preview inside the editor needs the frontend slider assets.
 * (The shortcode's wp_footer initializer never runs in the editor —
 * editor.js initializes the preview itself.)
 * Hooked to both editor hooks so the scripts also reach the iframed
 * editor canvas; guarded so the frontend keeps its existing loader.
 */
function twenty20_block_editor_assets() {
  if ( ! is_admin() ) {
    return;
  }
  wp_enqueue_style( 'twenty20', ZB_T20_URL . '/assets/css/twenty20.css', array(), ZB_T20_VER );
  wp_enqueue_script( 'twenty20-eventmove', ZB_T20_URL . '/assets/js/jquery.event.move.js', array( 'jquery' ), ZB_T20_VER, true );
  wp_enqueue_script( 'twenty20', ZB_T20_URL . '/assets/js/jquery.twenty20.js', array( 'jquery', 'twenty20-eventmove' ), ZB_T20_VER, true );
}
add_action( 'enqueue_block_editor_assets', 'twenty20_block_editor_assets' );
add_action( 'enqueue_block_assets', 'twenty20_block_editor_assets' );
