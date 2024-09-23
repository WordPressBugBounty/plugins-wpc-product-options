(function($) {
  'use strict';

  $(function() {
    // ready
    init_sortable();
    init_apply();
    init_single($('#wpcpo-select-display').val());
    image_selector();
    date_picker();
  });

  $(document).on('change', '.wpcpo-apply-for', function() {
    init_apply();
  });

  $(document).on('change', '.wpcpo-apply', function() {
    var $this = $(this);
    var val = $this.val();
    var apply = $('.wpcpo-apply-for').val();

    $this.data(apply, val.join());
  });

  $(document).on('click touch', '.wpcpo-item-header', function(e) {
    if (($(e.target).closest('.wpcpo-item-duplicate').length === 0) &&
        ($(e.target).closest('.wpcpo-item-remove').length === 0)) {
      $(this).closest('.wpcpo-item').toggleClass('active');
    }
  }).on('click touch', '.wpcpo-item-remove', function() {
    var r = confirm(
        'Do you want to remove this field? This action cannot undo.');
    if (r == true) {
      $(this).closest('.wpcpo-item').remove();
    }
  }).on('click touch', '.wpcpo-item-new', function() {
    let $this = $(this), type = $('#wpcpo-item-type').val();
    $this.prop('disabled', true);

    $.post(ajaxurl, {
      action: 'wpcpo_add_field', type: type,
    }, function(response) {
      $('.wpcpo-items-wrapper .wpcpo-items').append(response);
      init_sortable();
      $this.prop('disabled', false);
    });
  }).on('keyup change keypress', '.wpcpo-item-line .sync-label', function() {
    // sync label
    let $this = $(this), value = $this.val(),
        $label = $this.closest('.wpcpo-item').
            find('.wpcpo-item-label .title');
    $label.text(value);
  }).on('change', '.wpcpo-item-line .checkbox-required', function() {
    // required
    let $this = $(this), $label = $this.closest('.wpcpo-item').
        find('.wpcpo-item-label .required');

    if ($this.is(':checked')) {
      $label.text('*');
    } else {
      $label.text('');
    }
  }).on('click touch', '.nav-tab-wrapper a', function(e) {
    e.preventDefault();
    let $this = $(this), id = $this.attr('href'),
        $wrapper = $this.closest('.wpcpo-item-content');
    $wrapper.find('.nav-tab-wrapper > a').removeClass('nav-tab-active');
    $wrapper.find('.nav-tab-content').removeClass('active');
    $this.addClass('nav-tab-active');
    $(id).addClass('active');
  }).on('click touch', '.inner-option-remove button', function() {
    let $this = $(this), $wrapper = $this.closest('.inner-option');
    $wrapper.remove();
  }).on('click touch', '.wpcpo-add-new-option', function() {
    let $this = $(this),
        $wrapper = $this.closest('.wpcpo-inner-options').find('.inner-content'),
        field_id = $this.data('id'),
        type = $this.closest('.wpcpo-item').find('.wpcpo-type-val').val();

    $this.prop('disabled', true);

    $.post(ajaxurl, {
      action: 'wpcpo_add_option', field_id: field_id, type: type,
    }, function(response) {
      $wrapper.append(response);
      init_sortable();
      image_selector();
      date_picker();
      $this.prop('disabled', false);
    });
  }).on('click', 'input[type="submit"]', function() {
    // Validate Form
    let inputs = $('.wpcpo-items-wrapper .wpcpo-input-not-empty').get(),
        _submit = true;
    for (let input of inputs) {
      let $input = $(input);
      if ($input.val() === '') {
        _submit = false;
        $input.addClass('wpcpo-has-error').
            closest('.wpcpo-item').
            addClass('wpcpo-has-error');
      }
    }
    return _submit;
  }).on('change', '#wpcpo-select-display', function() {
    let state = $(this).val();
    init_single(state);
  }).on('change', '.wpcpo-item-line .option-type', function() {
    let $select = $(this), value = $select.val();

    $select.removeClass(function(index, className) {
      return (className.match(/(^|\s)type-\S+/g) || []).join(' ');
    }).addClass('type-' + value);
  }).on('keyup change keypress', '.wpcpo-price', function() {
    let $this = $(this), value = $this.val();

    value = value.replace(/[^0-9\-\.%]/g, '');
    $this.val(value);
  }).on('keyup change keypress', '.wpcpo-price-custom', function() {
    let $this = $(this), value = $this.val();

    value = value.replace(/[^0-9\+\-\*\/\(\)\.pqlvws]/g, '');
    $this.val(value);

    // validate custom price
    let check = validate_custom_price(value);

    if (!check) {
      $this.addClass('wpcpo-has-error');
    } else {
      $this.removeClass('wpcpo-has-error');
    }
  }).on('click touch', 'a.wpcpo-image-remove', function(e) {
    e.preventDefault();
    $(this).
        closest('.wpcpo-image-selector').
        find('.wpcpo-image-id').val('').trigger('change');
    $(this).
        closest('.wpcpo-image-selector').
        find('.wpcpo-image-preview').html('');
    $(this).hide();
  });

  function init_sortable() {
    $('.wpcpo-items').sortable({
      handle: '.wpcpo-item-move',
    });
    $('.wpcpo-inner-options .inner-content').sortable({
      handle: '.inner-option-move',
    });
  }

  function init_single(state) {
    if (state === 'global' || state === 'disable') {
      $('#wpcpo_settings .wpcpo-fields-single-product').hide();
    } else {
      $('#wpcpo_settings .wpcpo-fields-single-product').show();
    }
  }

  function init_apply() {
    var apply = $('.wpcpo-apply-for').val();

    if (apply === 'none' || apply === 'all') {
      $('.wpcpo-apply-val').hide();
    } else {
      $('.wpcpo-apply-val').show();
    }

    $('.wpcpo-apply').each(function() {
      var $this = $(this);
      var apply = $('.wpcpo-apply-for').val();

      $this.selectWoo({
        ajax: {
          url: ajaxurl, dataType: 'json', delay: 250, data: function(params) {
            return {
              q: params.term, action: 'wpcpo_search_term', taxonomy: apply,
            };
          }, processResults: function(data) {
            var options = [];
            if (data) {
              $.each(data, function(index, text) {
                options.push({id: text[0], text: text[1]});
              });
            }
            return {
              results: options,
            };
          }, cache: true,
        }, minimumInputLength: 1,
      });

      if ($this.data(apply) !== undefined && $this.data(apply) !== '') {
        $this.val(String($this.data(apply)).split(',')).change();
      } else {
        $this.val([]).change();
      }
    });
  }

  function image_selector() {
    $('.wpcpo-image-preview').on('click touch', function(e) {
      e.preventDefault();

      var $preview = $(this);
      var $image_id = $(this).
          closest('.wpcpo-image-selector').
          find('.wpcpo-image-id');
      var media_popup = wp.media.frames.mediaFrame = wp.media({
        title: 'Choose an image', button: {
          text: 'Choose',
        }, library: {
          type: 'image',
        }, multiple: true,
      });

      media_popup.on('select', function() {
        var selection = media_popup.state().get('selection');

        selection.map(function(attachment) {
          attachment = attachment.toJSON();

          if (attachment.id) {
            var url = attachment.sizes.thumbnail ?
                attachment.sizes.thumbnail.url :
                attachment.url;
            $preview.html('<img src="' + url + '" />');
            $image_id.val(attachment.id).trigger('change');
            $preview.next('.wpcpo-image-remove').show();
          }
        });
      });

      media_popup.open();
    });
  }

  function date_picker() {
    $('.wpcpo-multiple-dates').wpcdpk({
      multipleDates: true,
      multipleDatesSeparator: ', ',
    });
  }

  function validate_custom_price(custom_price) {
    let check = false;

    custom_price = custom_price.toLowerCase().
        replace(/(v|q|p|l|w|s)+/gi, function(match, tag, char) {
          switch (tag) {
            case 'q':
            case 'p':
            case 'l':
            case 'v':
            case 'w':
            case 's':
              return 1;
          }
        });

    try {
      custom_price = parseFloat(
          eval(custom_price.replace(/[^-()\d/*+.]/g, '')));
      if (!isNaN(custom_price) && (custom_price !== Infinity) &&
          (custom_price !== -Infinity)) {
        check = true;
      }
    } catch (e) {
      console.log('Error: ' + e);
    }

    return check;
  }
})(jQuery);
