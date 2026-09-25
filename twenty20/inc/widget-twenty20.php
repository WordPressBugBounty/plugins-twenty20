<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// The bundled preview placeholder must never be treated as a real selection.
function twenty20_widget_is_placeholder_url( $url ) {
  $url = trim( (string) $url );
  if ( '' === $url ) {
    return true;
  }
  return ( false !== strpos( $url, 'assets/images/placeholder.png' ) );
}

// Widget Registration.
function twenty20_slider_widget_register() {
  register_widget( 'twenty20_slider_widget' );
}

class twenty20_slider_widget extends WP_Widget {
  // Widget Class Constructor
  function __construct() {
    parent::__construct(
      't20_slider_widget',
      __( 'Twenty20 Slider', 'zb_twenty20' ),
      array( 'description' => __( 'Highlight the differences between two images.', 'zb_twenty20' ), )
    );
    add_action( 'admin_enqueue_scripts', array( &$this, 'mac_admin_scripts' ) );
    // Block-based widgets/site editor screens do not always fire admin_enqueue_scripts for legacy forms.
    add_action( 'enqueue_block_editor_assets', array( &$this, 'twenty20_enqueue_widget_admin_assets' ) );
  }

  function mac_admin_scripts( $hook ) {
    if ( 'widgets.php' !== $hook && 'customize.php' !== $hook && 'site-editor.php' !== $hook ) {
        return;
    }
    $this->twenty20_enqueue_widget_admin_assets();
  }

  // Shared loader so classic widgets, customizer AND the block widget editor all get the uploader.
  public function twenty20_enqueue_widget_admin_assets() {
    if ( ! function_exists( 'wp_enqueue_media' ) ) {
        return;
    }
    wp_enqueue_media();
    wp_register_style( 'mac_style', ZB_T20_URL . '/assets/css/admin.css', array(), ZB_T20_VER );
    wp_enqueue_style( 'mac_style' );
    // NOTE: assets/js/admin.js does not exist in the plugin — only register the uploader that ships.
    wp_register_script( 'mac_widget_img', ZB_T20_URL . '/assets/js/image-uploader.js', array( 'jquery', 'media-upload', 'media-views' ), ZB_T20_VER, true );
    wp_localize_script(
      'mac_widget_img',
      'twenty20_widget',
      array(
        'placeholder_url' => ZB_T20_URL . '/assets/images/placeholder.png',
      )
    );
    wp_enqueue_script( 'mac_widget_img' );
  }

  // Front-end View
  public function widget( $args, $instance ) {
    echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-provided markup.
    if ( ! empty( $instance['title'] ) ) {
        echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $instance['title'] ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- before/after title are theme markup.
    }
    ?>
    <div class="mac-wrap">

      <?php
        $t20ID = isset( $args['widget_id'] ) ? sanitize_html_class( $args['widget_id'] ) : 'twenty20-widget-' . uniqid();
        $is_vertical = ! empty( $instance['is_vertical'] );

        $offset_raw = isset( $instance['t20_widget_offset'] ) ? $instance['t20_widget_offset'] : '0.5';
        $offset = is_numeric( $offset_raw ) ? (float) $offset_raw : 0.5;
        $offset = max( 0.1, min( 1.0, $offset ) );

        $hover_raw = isset( $instance['t20_widget_hover'] ) ? strtolower( trim( (string) $instance['t20_widget_hover'] ) ) : 'false';
        $is_hover = ( 'true' === $hover_raw );

        $before = isset( $instance['t20_widget_before'] ) ? sanitize_text_field( $instance['t20_widget_before'] ) : '';
        $after  = isset( $instance['t20_widget_after'] ) ? sanitize_text_field( $instance['t20_widget_after'] ) : '';

        $img_before_raw = isset( $instance['t20_img_before'] ) ? trim( (string) $instance['t20_img_before'] ) : '';
        $img_after_raw  = isset( $instance['t20_img_after'] ) ? trim( (string) $instance['t20_img_after'] ) : '';
        // Images may be attachment URLs saved by the media uploader — allow only http(s) URLs.
        // Legacy instances may have the preview placeholder saved as data — never render that.
        if ( twenty20_widget_is_placeholder_url( $img_before_raw ) ) {
          $img_before_raw = '';
        }
        if ( twenty20_widget_is_placeholder_url( $img_after_raw ) ) {
          $img_after_raw = '';
        }
        $img_before = esc_url_raw( $img_before_raw, array( 'http', 'https' ) );
        $img_after  = esc_url_raw( $img_after_raw, array( 'http', 'https' ) );
      ?>
      <?php if ( ! empty( $img_before ) && ! empty( $img_after ) ) : ?>
      <div class="twenty20">
        <div class="twentytwenty-container <?php echo esc_attr( $t20ID ); ?>"<?php echo $is_vertical ? ' data-orientation="vertical"' : ''; ?>
          data-offset="<?php echo esc_attr( (string) $offset ); ?>"
          data-before="<?php echo esc_attr( $before ); ?>"
          data-after="<?php echo esc_attr( $after ); ?>"
          data-hover="<?php echo $is_hover ? 'true' : 'false'; ?>">
          <img src="<?php echo esc_url( $img_before ); ?>" alt="<?php echo esc_attr( $before ? $before : 'Before image' ); ?>" loading="lazy">
          <img src="<?php echo esc_url( $img_after ); ?>" alt="<?php echo esc_attr( $after ? $after : 'After image' ); ?>" loading="lazy">
        </div>
        <script>
          jQuery(window).on("load", function(){
            (function($){
              var id = <?php echo wp_json_encode( $t20ID ); ?>;
              var $container = $(".twentytwenty-container." + id);
              if (!$container.length || $container.data("twenty20-init")) return;
              var offset = parseFloat($container.attr("data-offset"));
              if (isNaN(offset) || offset < 0.1 || offset > 1) { offset = 0.5; }
              var cfg = { default_offset_pct: offset };
              if ($container.attr("data-orientation") === "vertical") { cfg.orientation = "vertical"; }
              if ($container.attr("data-hover") === "true") { cfg.move_slider_on_hover = true; }
              try { $container.twentytwenty(cfg); } catch(e) {}
              $container.data("twenty20-init", true);
              var before = $container.attr("data-before") || "";
              var after = $container.attr("data-after") || "";
              if (before) { $container.find(".twentytwenty-before-label").text(before); }
              else { $container.find(".twentytwenty-before-label").hide(); }
              if (after) { $container.find(".twentytwenty-after-label").text(after); }
              else { $container.find(".twentytwenty-after-label").hide(); }
            })(jQuery);
          });
        </script>
      </div>
      <?php endif ?>
    </div>
  <?php echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme-provided markup.
  }

  // Widget Layout
  public function form( $instance ) {

    $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
    $t20_widget_before = ! empty( $instance['t20_widget_before'] ) ? $instance['t20_widget_before'] : '';
    $t20_widget_after = ! empty( $instance['t20_widget_after'] ) ? $instance['t20_widget_after'] : '';
    $t20_img_before = ( isset( $instance['t20_img_before'] ) ? $instance['t20_img_before'] : '' );
    $t20_img_after = isset( $instance['t20_img_after'] ) ? $instance['t20_img_after'] : '';
    $is_vertical = ! empty( $instance['is_vertical'] ) ? 1 : 0;
    $t20_widget_offset = ( isset( $instance['t20_widget_offset'] ) && is_numeric( $instance['t20_widget_offset'] ) ) ? (string) $instance['t20_widget_offset'] : '0.5';
    $t20_widget_hover = ( isset( $instance['t20_widget_hover'] ) && 'true' === strtolower( trim( (string) $instance['t20_widget_hover'] ) ) ) ? 'true' : 'false';

  ?>

  <div class="mac_options_form" data-placeholder="<?php echo esc_attr( ZB_T20_URL . '/assets/images/placeholder.png' ); ?>">
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'zb_twenty20' ); ?></label>
      <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
    </p>

    <p class="check">
      <label for="<?php echo esc_attr( $this->get_field_id( 'is_vertical' ) ); ?>">
        <input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'is_vertical' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'is_vertical' ) ); ?>" value="1" <?php checked( 1, $is_vertical, true ); ?> />
        <strong><?php esc_html_e( 'Set Vertical direction', 'zb_twenty20' ); ?></strong>
      </label>
    </p>

    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 't20_widget_before' ) ); ?>"><?php esc_html_e( 'Before:', 'zb_twenty20' ); ?></label>
      <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 't20_widget_before' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 't20_widget_before' ) ); ?>" type="text" value="<?php echo esc_attr( $t20_widget_before ); ?>">
    </p>
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 't20_widget_after' ) ); ?>"><?php esc_html_e( 'After:', 'zb_twenty20' ); ?></label>
      <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 't20_widget_after' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 't20_widget_after' ) ); ?>" type="text" value="<?php echo esc_attr( $t20_widget_after ); ?>">
    </p>

    <p>
      <strong><label for="<?php echo esc_attr( $this->get_field_id( 't20_widget_offset' ) ); ?>"><?php esc_html_e( 'Offset:', 'zb_twenty20' ); ?></label></strong>
      <select id="<?php echo esc_attr( $this->get_field_id( 't20_widget_offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 't20_widget_offset' ) ); ?>">
        <option value=""><?php esc_html_e( 'Select offset value', 'zb_twenty20' ); ?></option>
        <option value="0.1" <?php selected( $t20_widget_offset, '0.1', true ); ?>><?php esc_html_e( '0.1', 'zb_twenty20' ); ?></option>
        <option value="0.2" <?php selected( $t20_widget_offset, '0.2', true ); ?>><?php esc_html_e( '0.2', 'zb_twenty20' ); ?></option>
        <option value="0.3" <?php selected( $t20_widget_offset, '0.3', true ); ?>><?php esc_html_e( '0.3', 'zb_twenty20' ); ?></option>
        <option value="0.4" <?php selected( $t20_widget_offset, '0.4', true ); ?>><?php esc_html_e( '0.4', 'zb_twenty20' ); ?></option>
        <option value="0.5" <?php selected( $t20_widget_offset, '0.5', true ); ?>><?php esc_html_e( '0.5 (default)', 'zb_twenty20' ); ?></option>
        <option value="0.6" <?php selected( $t20_widget_offset, '0.6', true ); ?>><?php esc_html_e( '0.6', 'zb_twenty20' ); ?></option>
        <option value="0.7" <?php selected( $t20_widget_offset, '0.7', true ); ?>><?php esc_html_e( '0.7', 'zb_twenty20' ); ?></option>
        <option value="0.8" <?php selected( $t20_widget_offset, '0.8', true ); ?>><?php esc_html_e( '0.8', 'zb_twenty20' ); ?></option>
        <option value="0.9" <?php selected( $t20_widget_offset, '0.9', true ); ?>><?php esc_html_e( '0.9', 'zb_twenty20' ); ?></option>
        <option value="1" <?php selected( $t20_widget_offset, '1', true ); ?>><?php esc_html_e( '1.0', 'zb_twenty20' ); ?></option>
      </select>
    </p>

    <p>
      <strong><label for="<?php echo esc_attr( $this->get_field_id( 't20_widget_hover' ) ); ?>"><?php esc_html_e( 'Mouse over:', 'zb_twenty20' ); ?></label></strong>
      <select id="<?php echo esc_attr( $this->get_field_id( 't20_widget_hover' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 't20_widget_hover' ) ); ?>">
        <option value="false" <?php selected( $t20_widget_hover, 'false', true ); ?>><?php esc_html_e( 'No', 'zb_twenty20' ); ?></option>
        <option value="true" <?php selected( $t20_widget_hover, 'true', true ); ?>><?php esc_html_e( 'Yes', 'zb_twenty20' ); ?></option>
      </select>
      <br/><em>Move slider on mouse hover?</em>
    </p>

    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 't20_img_before' ) ); ?>"><?php esc_html_e( 'Before Image:', 'zb_twenty20' ); ?> <span class="mac-info" title="<?php esc_attr_e( 'Select t20_img_before or enter external image url.', 'zb_twenty20' ); ?>"></span></label><br/>
      <?php $preview_before = ! empty( $t20_img_before ) ? $t20_img_before : ZB_T20_URL . '/assets/images/placeholder.png'; ?>
      <img src="<?php echo esc_url( $preview_before ); ?>" width="150px" alt=""/>

      <input class="widefat mac-img-before" id="<?php echo esc_attr( $this->get_field_id( 't20_img_before' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 't20_img_before' ) ); ?>" type="hidden" value="<?php echo esc_attr( $t20_img_before ); ?>" />

      <span class="submit">
        <input type="button" data-t20="img-t20-before" name="submit" class="button button-primary mac-upload_image_button" value="Select image">
        <input type="button" name="submit" class="button delete button-secondary mac-remove-image-before" value="X">
      </span>
    </p>

    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 't20_img_after' ) ); ?>"><?php esc_html_e( 'After Image:', 'zb_twenty20' ); ?> <span class="mac-info" title="<?php esc_attr_e( 'Select Twenty20 Slider or enter external image url.', 'zb_twenty20' ); ?>"></span></label><br/>

      <?php $preview_after = ! empty( $t20_img_after ) ? $t20_img_after : ZB_T20_URL . '/assets/images/placeholder.png'; ?>
      <img src="<?php echo esc_url( $preview_after ); ?>" width="150px" alt=""/>

      <input class="widefat mac-img-after" id="<?php echo esc_attr( $this->get_field_id( 't20_img_after' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 't20_img_after' ) ); ?>" type="hidden" value="<?php echo esc_attr( $t20_img_after ); ?>" />

      <span class="submit">
        <input type="button" data-t20="img-t20-after" name="submit" class="button button-primary mac-upload_image_button" value="Select image">
        <input type="button" name="submit" class="button delete button-secondary mac-remove-image-after" value="X">
      </span>
    </p>

  </div>

<?php
  }
  // Save Data
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

    // Never persist the preview placeholder as a real selection (happens when the
    // uploader JS didn't run and the form posted the preview value).
    $before_url = isset( $new_instance['t20_img_before'] ) ? trim( (string) $new_instance['t20_img_before'] ) : '';
    $after_url  = isset( $new_instance['t20_img_after'] ) ? trim( (string) $new_instance['t20_img_after'] ) : '';
    if ( twenty20_widget_is_placeholder_url( $before_url ) ) {
      $before_url = '';
    }
    if ( twenty20_widget_is_placeholder_url( $after_url ) ) {
      $after_url = '';
    }
    $instance['t20_img_before'] = ( '' !== $before_url ) ? esc_url_raw( $before_url, array( 'http', 'https' ) ) : '';
    $instance['t20_img_after'] = ( '' !== $after_url ) ? esc_url_raw( $after_url, array( 'http', 'https' ) ) : '';
    $instance['is_vertical'] = ! empty( $new_instance['is_vertical'] ) ? 1 : 0;

    $offset_candidate = isset( $new_instance['t20_widget_offset'] ) ? $new_instance['t20_widget_offset'] : '0.5';
    $instance['t20_widget_offset'] = is_numeric( $offset_candidate ) ? (string) max( 0.1, min( 1.0, (float) $offset_candidate ) ) : '0.5';

    $hover_candidate = isset( $new_instance['t20_widget_hover'] ) ? strtolower( trim( (string) $new_instance['t20_widget_hover'] ) ) : 'false';
    $instance['t20_widget_hover'] = ( 'true' === $hover_candidate ) ? 'true' : 'false';

    $instance['t20_widget_before'] = isset( $new_instance['t20_widget_before'] ) ? sanitize_text_field( $new_instance['t20_widget_before'] ) : '';
    $instance['t20_widget_after'] = isset( $new_instance['t20_widget_after'] ) ? sanitize_text_field( $new_instance['t20_widget_after'] ) : '';

    return $instance;
  }
}
add_action( 'widgets_init', 'twenty20_slider_widget_register' );
