/**
 * Twenty20 Before-After — Gutenberg block editor (no build step, plain JS).
 *
 * - Image settings live in the sidebar ("Images" panel).
 * - The canvas always shows something: pickers when empty, a live slider
 *   preview once images are set. The preview is rendered client-side from
 *   block attributes (no REST round-trip, so it can never come back blank);
 *   the FRONTEND is still rendered by the hardened PHP renderer, so the
 *   2.0.6 XSS protections apply where it matters.
 * - An "Edit images / Preview" toggle exists both as an overlay button on the
 *   preview and as a block toolbar button, so users can always get back to
 *   the image pickers.
 */
(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var Fragment = wp.element.Fragment;
  var useEffect = wp.element.useEffect;
  var useRef = wp.element.useRef;
  var useState = wp.element.useState;
  var useSelect = wp.data.useSelect;
  var __ = wp.i18n.__;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var BlockControls = wp.blockEditor.BlockControls;
  var MediaUpload = wp.blockEditor.MediaUpload;
  var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var RangeControl = wp.components.RangeControl;
  var SelectControl = wp.components.SelectControl;
  var ToolbarGroup = wp.components.ToolbarGroup;
  var ToolbarButton = wp.components.ToolbarButton;
  var Button = wp.components.Button;
  var Placeholder = wp.components.Placeholder;

  var ATTRS = {
    img1: { type: 'number', default: 0 },
    img2: { type: 'number', default: 0 },
    before: { type: 'string', default: '' },
    after: { type: 'string', default: '' },
    offset: { type: 'number', default: 0.5 },
    direction: { type: 'string', default: 'horizontal' },
    align: { type: 'string', default: '' },
    width: { type: 'string', default: '' },
    hover: { type: 'string', default: 'false' }
  };

  function useImageUrl(id) {
    return useSelect(
      function (select) {
        if (!id) return '';
        var media = select('core').getMedia(id);
        if (!media) return '';
        if (media.source_url) return media.source_url;
        if (
          media.media_details &&
          media.media_details.sizes &&
          media.media_details.sizes.thumbnail &&
          media.media_details.sizes.thumbnail.source_url
        ) {
          return media.media_details.sizes.thumbnail.source_url;
        }
        return '';
      },
      [id]
    );
  }

  // Display-only mirror of the PHP width/align logic (frontend PHP revalidates).
  function previewWrapStyle(attrs) {
    var style = { width: '100%' };
    var w = (attrs.width || '').trim();
    var m;
    if (w !== '') {
      if (/^\d+(\.\d+)?$/.test(w)) {
        var n = parseFloat(w);
        if (n > 0 && n <= 100) style = { width: n + '%' };
      } else if ((m = w.match(/^(\d+(\.\d+)?)(%|px)$/i))) {
        var num = parseFloat(m[1]);
        var u = m[3].toLowerCase();
        if (u === '%' && num > 0 && num <= 100) style = { width: num + '%' };
        else if (u === 'px' && num > 0 && num <= 3000) style = { width: num + 'px' };
      }
    }
    if (attrs.align === 'right') {
      if (!w) style = { width: '50%' };
      style.float = 'right';
      style.marginLeft = '20px';
    }
    if (attrs.align === 'left') {
      if (!w) style = { width: '50%' };
      style.float = 'left';
      style.marginRight = '20px';
    }
    return style;
  }

  function ImagePicker(props) {
    return el(
      MediaUploadCheck,
      {},
      el(MediaUpload, {
        onSelect: function (media) {
          props.onSelect(media && media.id ? media.id : 0);
        },
        allowedTypes: ['image'],
        value: props.id || undefined,
        render: function (obj) {
          var preview = props.url
            ? el('img', { src: props.url, alt: '', className: 'twenty20-block-thumb' })
            : el('div', { className: 'twenty20-block-empty' }, props.label);
          return el(
            'div',
            { className: 'twenty20-block-pick' },
            preview,
            el(
              'div',
              { className: 'twenty20-block-pick-actions' },
              el(
                Button,
                { variant: 'secondary', onClick: obj.open },
                props.url ? __('Replace image', 'zb_twenty20') : __('Select image', 'zb_twenty20')
              ),
              props.url
                ? el(
                    Button,
                    {
                      variant: 'tertiary',
                      isDestructive: true,
                      onClick: function () {
                        props.onSelect(0);
                      }
                    },
                    __('Remove', 'zb_twenty20')
                  )
                : null
            )
          );
        }
      })
    );
  }

  // Initialize every not-yet-initialized preview container. Returns true when
  // there is nothing left to do.
  function initPreview(node) {
    if (typeof jQuery === 'undefined') return false;
    var allReady = true;
    (function ($) {
      if (typeof $.fn.twentytwenty === 'undefined') {
        allReady = false;
        return;
      }
      $(node)
        .find('.twentytwenty-container')
        .each(function () {
          var $c = $(this);
          if ($c.data('twenty20-init')) return;
          // Wait until both images have real dimensions — twentytwenty
          // measures the first image at init, so early init = broken slider.
          var ready = true;
          $c.find('img').each(function () {
            if (!this.complete || (typeof this.naturalWidth !== 'undefined' && this.naturalWidth === 0)) {
              ready = false;
            }
          });
          if (!ready) {
            allReady = false;
            return;
          }
          var offset = parseFloat($c.attr('data-offset'));
          if (isNaN(offset) || offset < 0.1 || offset > 1) offset = 0.5;
          var cfg = { default_offset_pct: offset };
          if ($c.attr('data-orientation') === 'vertical') cfg.orientation = 'vertical';
          if ($c.attr('data-hover') === 'true') cfg.move_slider_on_hover = true;
          try {
            $c.twentytwenty(cfg);
          } catch (e) {
            return;
          }
          $c.data('twenty20-init', true);
          // Real class for CSS (jQuery .data() is invisible to stylesheets).
          $c.addClass('is-t20-ready');
          var b = $c.attr('data-before') || '';
          var a = $c.attr('data-after') || '';
          if (b) {
            $c.find('.twentytwenty-before-label').text(b);
          } else {
            $c.find('.twentytwenty-before-label').hide();
          }
          if (a) {
            $c.find('.twentytwenty-after-label').text(a);
          } else {
            $c.find('.twentytwenty-after-label').hide();
          }
          if (!b && !a) {
            $c.find('.twentytwenty-overlay').hide();
          }
        });
    })(jQuery);
    return allReady;
  }

  // Push label text into already-initialized preview sliders without
  // rebuilding them (used for live typing feedback).
  function syncPreviewLabels(node, before, after) {
    if (typeof jQuery === 'undefined') return;
    (function ($) {
      $(node)
        .find('.twentytwenty-container')
        .each(function () {
          var $c = $(this);
          if (!$c.data('twenty20-init')) return;
          var $b = $c.find('.twentytwenty-before-label');
          var $a = $c.find('.twentytwenty-after-label');
          if (!$b.length || !$a.length) return;
          if (before) {
            $b.text(before).show();
          } else {
            $b.hide();
          }
          if (after) {
            $a.text(after).show();
          } else {
            $a.hide();
          }
          $c.find('.twentytwenty-overlay').toggle(!!(before || after));
        });
    })(jQuery);
  }

  // Open the block inspector sidebar (post editor, site editor, widgets
  // editor) so a canvas click lands users on the Twenty20 properties.
  // The block is explicitly selected first: opening the inspector with no
  // selection is what produces an empty sidebar.
  function isBlockInspectorOpen() {
    var stores = ['core/edit-post', 'core/edit-widgets', 'core/edit-site'];
    for (var i = 0; i < stores.length; i++) {
      try {
        var select = wp.data.select(stores[i]);
        if (select && typeof select.getActiveGeneralSidebarName === 'function') {
          var name = select.getActiveGeneralSidebarName();
          if (name && name.toLowerCase().indexOf('block') !== -1) return true;
        }
      } catch (e) {
        // Try the next editor store.
      }
    }
    return false;
  }

  function focusBlockInspector(clientId) {
    try {
      var blockEditor = wp.data.dispatch('core/block-editor');
      if (blockEditor && typeof blockEditor.selectBlock === 'function' && clientId) {
        blockEditor.selectBlock(clientId);
      }
    } catch (e) {
      // Selection stays as-is; still try opening the sidebar below.
    }
    if (isBlockInspectorOpen()) return;
    var attempts = [
      ['core/edit-post', 'edit-post/block'],
      ['core/edit-widgets', 'edit-widgets/block-inspector'],
      ['core/edit-site', 'edit-site/block-inspector'],
      ['core/edit-site', 'edit-site/block']
    ];
    for (var i = 0; i < attempts.length; i++) {
      try {
        var store = attempts[i][0];
        var sidebarName = attempts[i][1];
        var dispatch = wp.data.dispatch(store);
        if (dispatch && typeof dispatch.openGeneralSidebar === 'function') {
          dispatch.openGeneralSidebar(sidebarName);
          return;
        }
      } catch (e) {
        // Try the next editor store.
      }
    }
  }

  function Edit(props) {
    var attrs = props.attributes;
    var setAttributes = props.setAttributes;
    var previewRef = useRef(null);
    var beforeUrl = useImageUrl(attrs.img1);
    var afterUrl = useImageUrl(attrs.img2);
    var hasImages = !!(attrs.img1 && attrs.img2);
    var isVertical = attrs.direction === 'vertical';
    var isHover = attrs.hover === 'true';
    var clientId = props.clientId;

    // Editing mode = pickers on canvas. Defaults to pickers until both
    // images are chosen, then switches to the live preview automatically.
    var useStateResult = useState(!hasImages);
    var isEditing = useStateResult[0];
    var setIsEditing = useStateResult[1];

    useEffect(
      function () {
        if (!hasImages) {
          setIsEditing(true);
        }
      },
      // eslint-disable-next-line react-hooks/exhaustive-deps
      [attrs.img1, attrs.img2]
    );

    // Initialize the preview slider; retry because images load asynchronously.
    // Labels sync live without a rebuild; structural changes remount the
    // preview via structuralKey below so the slider re-inits with new values
    // (twentytwenty has no "update options" API).
    useEffect(
      function () {
        var node = previewRef.current;
        if (!node || isEditing) return undefined;
        syncPreviewLabels(node, attrs.before, attrs.after);
        initPreview(node);
        var iv = setInterval(function () {
          syncPreviewLabels(node, attrs.before, attrs.after);
          if (initPreview(node)) clearInterval(iv);
        }, 500);
        var stop = setTimeout(function () {
          clearInterval(iv);
        }, 10000);
        return function () {
          clearInterval(iv);
          clearTimeout(stop);
        };
      },
      [
        attrs.img1,
        attrs.img2,
        attrs.offset,
        attrs.direction,
        attrs.hover,
        attrs.align,
        attrs.width,
        attrs.before,
        attrs.after,
        isEditing,
        beforeUrl,
        afterUrl
      ]
    );

    function pickBox(kind, id, url, labelText) {
      return el(
        'div',
        { className: 'twenty20-block-image', key: kind },
        el('span', { className: 'twenty20-block-tag' }, labelText),
        el(ImagePicker, {
          id: id,
          url: url,
          label: labelText,
          onSelect: function (newId) {
            var patch = {};
            patch[kind] = newId;
            setAttributes(patch);
          }
        })
      );
    }

    var inspector = el(
      InspectorControls,
      {},
      el(
        PanelBody,
        { title: __('Images', 'zb_twenty20'), initialOpen: true },
        el(
          'div',
          { className: 'twenty20-block-side-image' },
          el('span', { className: 'twenty20-block-tag' }, __('Before image', 'zb_twenty20')),
          el(ImagePicker, {
            id: attrs.img1,
            url: beforeUrl,
            label: __('Before image', 'zb_twenty20'),
            onSelect: function (id) {
              setAttributes({ img1: id });
            }
          })
        ),
        el(
          'div',
          { className: 'twenty20-block-side-image' },
          el('span', { className: 'twenty20-block-tag' }, __('After image', 'zb_twenty20')),
          el(ImagePicker, {
            id: attrs.img2,
            url: afterUrl,
            label: __('After image', 'zb_twenty20'),
            onSelect: function (id) {
              setAttributes({ img2: id });
            }
          })
        )
      ),
      el(
        PanelBody,
        { title: __('Slider settings', 'zb_twenty20'), initialOpen: true },
        el(RangeControl, {
          label: __('Starting position', 'zb_twenty20'),
          value: attrs.offset,
          min: 0.1,
          max: 1,
          step: 0.05,
          onChange: function (v) {
            setAttributes({ offset: v });
          }
        }),
        el(SelectControl, {
          label: __('Direction', 'zb_twenty20'),
          value: attrs.direction,
          options: [
            { label: __('Horizontal', 'zb_twenty20'), value: 'horizontal' },
            { label: __('Vertical', 'zb_twenty20'), value: 'vertical' }
          ],
          onChange: function (v) {
            setAttributes({ direction: v });
          }
        }),
        el(SelectControl, {
          label: __('Move on mouse hover', 'zb_twenty20'),
          value: attrs.hover,
          options: [
            { label: __('No', 'zb_twenty20'), value: 'false' },
            { label: __('Yes', 'zb_twenty20'), value: 'true' }
          ],
          onChange: function (v) {
            setAttributes({ hover: v });
          }
        }),
        el(SelectControl, {
          label: __('Alignment', 'zb_twenty20'),
          value: attrs.align,
          options: [
            { label: __('None', 'zb_twenty20'), value: '' },
            { label: __('Left', 'zb_twenty20'), value: 'left' },
            { label: __('Right', 'zb_twenty20'), value: 'right' }
          ],
          onChange: function (v) {
            setAttributes({ align: v });
          }
        }),
        el(TextControl, {
          label: __('Width (e.g. 60% or 500px — empty = full width)', 'zb_twenty20'),
          value: attrs.width,
          onChange: function (v) {
            setAttributes({ width: v });
          }
        })
      ),
      el(
        PanelBody,
        { title: __('Labels', 'zb_twenty20'), initialOpen: false },
        el(TextControl, {
          label: __('Before label', 'zb_twenty20'),
          value: attrs.before,
          onChange: function (v) {
            setAttributes({ before: v });
          }
        }),
        el(TextControl, {
          label: __('After label', 'zb_twenty20'),
          value: attrs.after,
          onChange: function (v) {
            setAttributes({ after: v });
          }
        })
      )
    );

    var toolbar = hasImages
      ? el(
          BlockControls,
          {},
          el(
            ToolbarGroup,
            {},
            el(ToolbarButton, {
              icon: isEditing ? 'visibility' : 'edit',
              label: isEditing
                ? __('Preview slider', 'zb_twenty20')
                : __('Edit images', 'zb_twenty20'),
              onClick: function () {
                setIsEditing(!isEditing);
              },
              isActive: isEditing
            })
          )
        )
      : null;

    var canvas;
    if (!hasImages || isEditing) {
      canvas = el(
        'div',
        { className: 'twenty20-block-editing' },
        el(
          Placeholder,
          {
            icon: 'image-flip-horizontal',
            label: __('Twenty20 Before-After', 'zb_twenty20'),
            instructions: hasImages
              ? __('Adjust the images, then go back to the live preview.', 'zb_twenty20')
              : __('Select the before and after images in the block settings to build your comparison slider.', 'zb_twenty20')
          },
          el(
            'div',
            { className: 'twenty20-block-images' },
            pickBox('img1', attrs.img1, beforeUrl, __('Before', 'zb_twenty20')),
            pickBox('img2', attrs.img2, afterUrl, __('After', 'zb_twenty20'))
          )
        ),
        hasImages
          ? el(
              Button,
              {
                variant: 'primary',
                className: 'twenty20-block-done',
                onClick: function () {
                  setIsEditing(false);
                }
              },
              __('Done — show preview', 'zb_twenty20')
            )
          : null
      );
    } else {
      // Live preview, rendered client-side (React escapes all values).
      // Frontend output still comes from the hardened PHP renderer.
      // structuralKey forces a remount (fresh slider init) whenever a
      // structural setting changes; labels sync live via syncPreviewLabels.
      var structuralKey = [
        attrs.img1,
        attrs.img2,
        attrs.offset,
        attrs.direction,
        attrs.hover,
        attrs.align,
        attrs.width
      ].join('|');
      var containerClass =
        'twentytwenty-container' + (isHover ? ' t20-hover' : '');
      canvas = el(
        'div',
        {
          className: 'twenty20-block-preview-wrap',
          title: __('Click to open Twenty20 settings', 'zb_twenty20'),
          onClick: function () {
            focusBlockInspector(clientId);
          }
        },
        el(
          'div',
          { className: 'twenty20-block-preview', ref: previewRef },
          el(
            'div',
            { className: 'twenty20', style: previewWrapStyle(attrs), key: structuralKey },
            el(
              'div',
              {
                className: containerClass,
                'data-orientation': isVertical ? 'vertical' : undefined,
                'data-offset': String(
                  isNaN(parseFloat(attrs.offset)) ? 0.5 : attrs.offset
                ),
                'data-before': attrs.before || '',
                'data-after': attrs.after || '',
                'data-hover': isHover ? 'true' : 'false'
              },
              el('img', {
                src: beforeUrl,
                alt: attrs.before || 'Before image'
              }),
              el('img', {
                src: afterUrl,
                alt: attrs.after || 'After image'
              })
            )
          )
        ),
        el(
          'div',
          { className: 'twenty20-block-edit-overlay' },
          el(
            Button,
            {
              variant: 'secondary',
              size: 'small',
              onClick: function (e) {
                if (e && e.stopPropagation) e.stopPropagation();
                setIsEditing(true);
              }
            },
            __('Edit images', 'zb_twenty20')
          )
        )
      );
    }

    return el(Fragment, {}, inspector, toolbar, el('div', { className: 'twenty20-block' }, canvas));
  }

  registerBlockType('twenty20/before-after', {
    attributes: ATTRS,
    edit: Edit,
    save: function () {
      return null;
    }
  });
})();
