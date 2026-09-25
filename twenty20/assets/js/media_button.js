jQuery(function($) {
  $(document).ready(function(){
    $('#insert-t20-media').click(open_media_window);
    $('#insert-t20-sc').click(insert_twenty20_shortcode);
  });

  function open_media_window() {
    if (this.window === undefined) {
      this.window = wp.media({
        title: 'Select Two images for Twenty20 Slider',
        library: {type: 'image'},
        multiple: 'add',
        button: {text: 'Insert'}
      });


      var self = this; // Needed to retrieve our variable in the anonymous function below
      this.window.on('select', function() {
        var first = self.window.state().get('selection').first().toJSON();
        var last = self.window.state().get('selection').last().toJSON();
        var im = self.window.state().get('selection').toJSON();

        if(im.length != 2){
          alert("Please select any two images");
          return false;
        }

        $('#t20_img1').val(parseInt(first.id, 10) || '');
        //$('img.timg-before').attr("src", first.url);
        $('#t20_img2').val(parseInt(last.id, 10) || '');
        tb_show("Twenty20 Shortcode", "#TB_inline?height=500&amp;width=600&amp;inlineId=twenty20_select");
        //wp.media.editor.insert('[twenty20 img1="' + first.url + '" img2="' + last.url + '" width="100%" direction="horizontal" offset="0.5"]');
      });
    }

    this.window.open();
    return false;
  }

  // Escape shortcode attribute values so quotes in captions don't break the shortcode.
  // Server-side still re-validates everything (defense in depth).
  function t20EscAttr(val) {
    return String(val == null ? '' : val).replace(/["\[\]\\]/g, '');
  }

  function insert_twenty20_shortcode() {
    var img1 = parseInt($('#t20_img1').val(), 10) || '';
    var img2 = parseInt($('#t20_img2').val(), 10) || '';
    var before = t20EscAttr($('#t20_sc_before_caption').val());
    var after = t20EscAttr($('#t20_sc_after_caption').val());
    var twidth = t20EscAttr($('#t20_sc_width').val());
    var direction = $('#t20_sc_direction').val() === 'vertical' ? 'vertical' : '';
    var hover = $('#t20_sc_hover').val() === 'true' ? 'true' : '';
    var offset = parseFloat($('#t20_sc_offset').val());
    if (isNaN(offset) || offset < 0.1 || offset > 1) { offset = 0.5; }
    var align = $('#t20_sc_align').val();
    align = (align === 'right' || align === 'left') ? align : '';


    if(before == null || before == ''){ before = ''; }else{ before = ' before="'+before+'"'; }
    if(after == null || after == ''){ after = ''; }else{ after = ' after="'+after+'"'; }
    if(align == null || align == ''){ align = ''; }else{ align = ' align="'+align+'"'; }
    if(direction === ''){ direction = ''; }else{ direction = ' direction="'+direction+'"'; }
    if(hover === '' || hover === 'false'){ hover = ''; }else{ hover = ' hover="'+hover+'"'; }
    if(twidth == '' || twidth == null){ twidth = ''; }else{ twidth = ' width="'+twidth+'"'; }

    wp.media.editor.insert('[twenty20 img1="' + img1 + '" img2="' + img2 + '"'+twidth+direction+' offset="'+offset+'"'+ align + before + after+hover+']');
    return false;
  }
});
