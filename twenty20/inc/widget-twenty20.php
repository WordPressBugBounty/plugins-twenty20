<?php
//Widget Registration.
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
    add_action('adminesc_html_enqueue_scripts', array(&$this, 'mac_admin_scripts'));
  }

  function mac_admin_scripts($hook) {
    if ($hook != 'widgets.php')
        return;
    wpesc_html_enqueue_media();
    wp_register_style( 'mac_style', ZB_T20_URL . '/assets/css/admin.css', false, ZB_T20_VER );
    wpesc_html_enqueue_style( 'mac_style' );
    wp_register_script('mac_widget_admin', ZB_T20_URL . '/assets/js/admin.js', array('jquery'), ZB_T20_VER, true);
    wp_register_script('mac_widget_img', ZB_T20_URL . '/assets/js/image-uploader.js', array('jquery'), ZB_T20_VER, true);
    wpesc_html_enqueue_script('mac_widget_admin');
    wpesc_html_enqueue_script('mac_widget_img');
  }

  // Front-end View
  public function widget( $args, $instance ) {
    echo esc_attr( $args['before_widget'] );
    if ( ! empty( $instance['title'] ) ) {
      echo esc_attr( $args['before_title'] ) . esc_html( apply_filters( 'widget_title', $instance['title'] ) ) . esc_attr( $args['after_title'] );
    }
    ?>
    <div class="mac-wrap">
      <?php
        $t20ID = $args['widget_id'];
        $isVertical = '';
        if($instance['is_vertical'] == true) {
          $isVertical = ' data-orientation="vertical"';
        }else{
          $isVertical = '';
        }
        if( $instance['t20_widget_hover'] === 'true'){
          $isHover = ',move_slider_on_hover: true';
          $yesHover = "t20-hover";
        }else{
          $isHover = '';
          $yesHover = '';
        }
      ?>
      <?php if (!empty($instance['t20_img_before']) && !empty($instance['t20_img_after'])): ?>
      <div class="twenty20">
        <div class="twentytwenty-container <?php echo esc_attr( $t20ID );?>"<?php echo esc_attr( $isVertical ); ?>>
          <img src="<?php echo esc_url($instance['t20_img_before']) ?>">
          <img src="<?php echo esc_url($instance['t20_img_after']) ?>">
        </div>
        <script>
          jQuery(window).on("load", function(){
            
            <?php if($instance['is_vertical'] == true) { ?>
              jQuery(".twentytwenty-container.<?php echo esc_js($t20ID);?>[data-orientation=\'vertical\']").twentytwenty({default_offset_pct: <?php echo esc_js($instance['t20_widget_offset']); ?>, orientation: 'vertical' <?php echo esc_js($isHover);?>});
            <?php }else{ ?>
            jQuery(".twentytwenty-container.<?php echo esc_js($t20ID);?>[data-orientation!=\'vertical\']").twentytwenty({default_offset_pct: <?php echo esc_js($instance['t20_widget_offset']); ?> <?php echo esc_js($isHover);?>});
            <?php } ?>
            <?php if(!empty($instance['t20_widget_before'])) { ?>
              jQuery(".<?php echo esc_js($t20ID);?> .twentytwenty-before-label").html("<?php echo esc_js($instance['t20_widget_before']);?>");
            <?php }else{ ?>
              jQuery(".<?php echo esc_js($t20ID);?> .twentytwenty-before-label").hide();
            <?php } ?>
            <?php if(!empty($instance['t20_widget_after'])) { ?>
              jQuery(".<?php echo esc_js($t20ID);?> .twentytwenty-after-label").html("<?php echo esc_js($instance['t20_widget_after']);?>");
            <?php }else{ ?>
              jQuery(".<?php echo esc_js($t20ID);?> .twentytwenty-after-label").hide();
            <?php } ?>
          });
        </script>
      </div>
      <?php endif ?>
    </div>
  <?php echo esc_attr( $args['after_widget'] ); }

  // Widget Layout
  public function form( $instance ) {

    $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
    $t20_widget_before = ! empty( $instance['t20_widget_before'] ) ? $instance['t20_widget_before'] : '';
    $t20_widget_after = ! empty( $instance['t20_widget_after'] ) ? $instance['t20_widget_after'] : '';
    $t20_img_before = ( isset( $instance['t20_img_before'] ) ? $instance['t20_img_before'] : '' );
    $t20_img_after = ( isset( $instance['t20_img_after'] ) ? $instance['t20_img_after'] : '' );
    $is_vertical = isset( $instance[ 'is_vertical' ] ) ? esc_attr( $instance[ 'is_vertical' ] ) : 1;
    $t20_widget_offset = ! empty( $instance['t20_widget_offset'] ) ? $instance['t20_widget_offset'] : __( '0.5', 'zb_twenty20' );
    $t20_widget_hover = ! empty( $instance['t20_widget_hover'] ) ? $instance['t20_widget_hover'] : __( 'false', 'zb_twenty20' );

  ?>

  <div class="mac_options_form">
    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'zb_twenty20' ); ?></label>
      <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
    </p>

    <p class="check">
      <label for="<?php echo esc_attr( $this->get_field_id("is_vertical") ); ?>" />
        <input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_name("is_vertical") ); ?>" name="<?php echo esc_attr( $this->get_field_name("is_vertical") ); ?>" value="1" <?php checked( 1, isset($instance['is_vertical']), true ); ?> />
        <strong><label for="<?php echo esc_attr(  $this->get_field_name("is_vertical") ); ?>"><?php esc_html_e( 'Set Vertical direction', 'zb_twenty20'); ?></label></strong>
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
      <strong><label for="<?php echo esc_attr( $this->get_field_id('t20_widget_offset') ); ?>"><?php esc_html_e('Offset:', 'zb_twenty20'); ?></label></strong>
      <select id="<?php echo esc_attr( $this->get_field_id('t20_widget_offset') ); ?>" name="<?php echo esc_attr( $this->get_field_name('t20_widget_offset') ); ?>">
        <option value=""><?php esc_html_e('Select offset value', 'zb_twenty20'); ?></option>
        <option value="0.1" <?php selected($t20_widget_offset, '0.1', true); ?>><?php esc_html_e('0.1', 'zb_twenty20'); ?></option>
        <option value="0.2" <?php selected($t20_widget_offset, '0.2', true); ?>><?php esc_html_e('0.2', 'zb_twenty20'); ?></option>
        <option value="0.3" <?php selected($t20_widget_offset, '0.3', true); ?>><?php esc_html_e('0.3', 'zb_twenty20'); ?></option>
        <option value="0.4" <?php selected($t20_widget_offset, '0.4', true); ?>><?php esc_html_e('0.4', 'zb_twenty20'); ?></option>
        <option value="0.5" <?php selected($t20_widget_offset, '0.5', true); ?>><?php esc_html_e('0.5 (default)', 'zb_twenty20'); ?></option>
        <option value="0.6" <?php selected($t20_widget_offset, '0.6', true); ?>><?php esc_html_e('0.6', 'zb_twenty20'); ?></option>
        <option value="0.7" <?php selected($t20_widget_offset, '0.7', true); ?>><?php esc_html_e('0.7', 'zb_twenty20'); ?></option>
        <option value="0.8" <?php selected($t20_widget_offset, '0.8', true); ?>><?php esc_html_e('0.8', 'zb_twenty20'); ?></option>
        <option value="0.9" <?php selected($t20_widget_offset, '0.9', true); ?>><?php esc_html_e('0.9', 'zb_twenty20'); ?></option>
        <option value="1" <?php selected($t20_widget_offset, '1', true); ?>><?php esc_html_e('1.0', 'zb_twenty20'); ?></option>
      </select>
    </p>

    <p>
      <strong><label for="<?php echo esc_attr( $this->get_field_id('t20_widget_hover') ); ?>"><?php esc_html_e('Mouse over:', 'zb_twenty20'); ?></label></strong>
      <select id="<?php echo esc_attr( $this->get_field_id('t20_widget_hover') ); ?>" name="<?php echo esc_attr( $this->get_field_name('t20_widget_hover') ); ?>">
        <option value="false" <?php selected($t20_widget_hover, 'false', true); ?>><?php esc_html_e('No', 'zb_twenty20'); ?></option>
        <option value="true" <?php selected($t20_widget_hover, 'true', true); ?>><?php esc_html_e('Yes', 'zb_twenty20'); ?></option>
      </select>
      <br/><em>Move slider on mouse hover?</em>
    </p>

    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 't20_img_before' ) ); ?>"><?php esc_html_e( 'Before Image:', 'zb_twenty20' ); ?> <span class="mac-info" title="<?php esc_html_e('Select t20_img_before or enter external image url.', 'zb_twenty20'); ?>"></span></label><br/>
      <?php if(empty( $t20_img_before )){ $t20_img_before = ZB_T20_URL . '/assets/images/placeholder.png'; } ?>
      <img src="<?php echo esc_url( $t20_img_before ); ?>" width="150px"/>

      <input class="widefat mac-img-before" id="<?php echo esc_attr( $this->get_field_id( 't20_img_before' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 't20_img_before' ) ); ?>" type="hidden" value="<?php echo esc_attr( $t20_img_before ); ?>" />

      <span class="submit">
        <input type="button" data-t20="img-t20-before" name="submit" id="submit" class="button button-primary mac-upload_image_button" value="Select image">
        <input type="button" name="submit" id="submit" class="button delete button-secondary mac-remove-image-before" value="X">
      </span>
    </p>

    <p>
      <label for="<?php echo esc_attr( $this->get_field_id( 't20_img_after' ) ); ?>"><?php esc_html_e( 'After Image:', 'zb_twenty20' ); ?> <span class="mac-info" title="<?php esc_html_e('Select Twenty20 Slider  or enter external image url.', 'zb_twenty20'); ?>"></span></label><br/>

      <?php if( empty( $t20_img_after )){ $t20_img_after = ZB_T20_URL . '/assets/images/placeholder.png';} ?>
      <img src="<?php echo esc_url( $t20_img_after ); ?>" width="150px"/>

      <input class="widefat mac-img-after" id="<?php echo esc_attr( $this->get_field_id( 't20_img_after' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 't20_img_after' ) ); ?>" type="hidden" value="<?php echo esc_attr( $t20_img_after ); ?>" />

      <span class="submit">
        <input type="button" data-t20="img-t20-after" name="submit" id="submit" class="button button-primary mac-upload_image_button" value="Select image">
        <input type="button" name="submit" id="submit" class="button delete button-secondary mac-remove-image-after" value="X">
      </span>
    </p>

  </div>

<?php
  }
  // Save Data
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
    $instance['t20_img_before'] = ( ! empty( $new_instance['t20_img_before'] ) ) ? esc_url( wp_strip_all_tags( $new_instance['t20_img_before'] ) ) : '';
    $instance['t20_img_after'] = ( ! empty( $new_instance['t20_img_after'] ) ) ? esc_url( wp_strip_all_tags( $new_instance['t20_img_after'] ) ) : '';
    $instance['is_vertical'] = $new_instance['is_vertical'];
    $instance['t20_widget_offset'] = $new_instance['t20_widget_offset'];
    $instance['t20_widget_hover'] = $new_instance['t20_widget_hover'];
    $instance['t20_widget_before'] = $new_instance['t20_widget_before'];
    $instance['t20_widget_after'] = $new_instance['t20_widget_after'];
    
    return $instance;
  }
}
add_action( 'widgets_init', 'twenty20_slider_widget_register' );