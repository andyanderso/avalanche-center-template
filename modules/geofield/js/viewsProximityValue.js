/**
 * @file
 * Dynamically update Views admin UI form items fetched via POST request.
 */
(function ($) {

  "use strict";

  Backdrop.behaviors.viewsProximityValue = {
    /**
     * @param array data
     */
    updateFormItem: function (data) {
      let formItemsWrapped = data[1].data;
      let formItems = $(formItemsWrapped).find('.geofield-proximity-field-wrapper');
      if (formItems.length) {
        $('.geofield-proximity-field-wrapper').replaceWith(formItems);
      }
    },
    updateMultipleFormItems: function (data) {
      let fullHTML = data[1].data;
      let nodes = $.parseHTML(fullHTML);
      let index = 1;
      for (const node of nodes) {
        if ($(node).hasClass('geofield-proximity-field-wrapper')) {
          $('tr#views-row-' + index + ' .geofield-proximity-field-wrapper').replaceWith(node);
          index++;
        }
      }
    },
    /**
     * @return bool
     */
    formHasRange: function () {
      let checkedValue = $('#edit-options-operator').find(':checked').val();
      if (checkedValue === 'between' || checkedValue === 'not between') {
        return true;
      }
      return false;
    },
    /**
     *
     */
    attach: function (context, settings) {
      const widget = this;
      let isGrouped = false;
      if ($('#edit-options-group-button-radios :checked').val() === '1') {
        isGrouped = true;
      }
      let hasRange = widget.formHasRange();
      $.ajaxSetup({
        type: 'POST',
        url: $('#edit-options-source').data('path'),
        dataType: 'json'
      });

      $('#edit-options-source').on('change', function() {
        let postData = {
          plugin: this.value
        };
        $.ajax( { data: postData } )
          .done( function (data) {
            if (isGrouped === false) {
              widget.updateFormItem(data);
            }
            else {
              widget.updateMultipleFormItems(data);
            }
        });
      });

      $('#edit-options-operator').on('change', function() {
        // Only post if necessary.
        if (hasRange === widget.formHasRange()) {
          return;
        }
        let postData = {
          plugin: $('#edit-options-source').val(),
          operator: $(this).find(':checked').val()
        };
        $.ajax( { data: postData } )
          .done( function (data) {
            widget.updateFormItem(data);
            hasRange = widget.formHasRange();
        });
      });
    }
  };
})(jQuery);
