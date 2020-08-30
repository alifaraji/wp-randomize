(function($) {
  function initColorPicker(widget) {
    widget.find('.color-picker').not('[id*="__i__"]').wpColorPicker({
      change: _.throttle(function() {
        $(this).trigger('change');
      }, )
    });
  }

  function onFormUpdate(event, widget) {
    initColorPicker(widget);
  }

  $(document).on('widget-added widget-updated', onFormUpdate);

  $(document).ready(function() {
    $('.widget-inside:has(.color-picker)').each(function() {
      initColorPicker($(this));
    });
  });

}(jQuery));
