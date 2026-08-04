/**
 * @file
 * JQuery to provide summary information inside vertical tabs.
 */

(function ($) {

/**
 * Provide summary information for vertical tabs.
 */
Backdrop.behaviors.scheduler_settings = {
  attach: function (context) {

    // Add the theme name as an additional class to the vertical-tabs div. This
    // was originally used in scheduler.css to rectify the style for collapsible
    // fieldsets. Left here in case any custom themes are still using it.
    // See https://www.drupal.org/node/1172040 and
    // https://www.drupal.org/node/2715479
    var theme = Backdrop.settings.ajaxPageState['theme'];
    $('div.vertical-tabs').addClass(theme);

    // Provide summary when editing a node.
    $('fieldset#edit-scheduler-settings', context).backdropSetSummary(function(context) {
      var vals = [];
      if ($('#edit-publish-on-date').val()) {
        vals.push(Backdrop.t('Scheduled for publishing'));
      }
      if ($('#edit-unpublish-on-date').val()) {
        vals.push(Backdrop.t('Scheduled for unpublishing'));
      }
      if (!vals.length) {
        vals.push(Backdrop.t('Not scheduled'));
      }
      return vals.join('<br/>');
    });

    // Provide summary during content type configuration.
    $('fieldset#edit-scheduler', context).backdropSetSummary(function(context) {
      var vals = [];
      if ($('#edit-scheduler-publish-enable', context).is(':checked')) {
        vals.push(Backdrop.t('Publishing enabled'));
      }
      if ($('#edit-scheduler-unpublish-enable', context).is(':checked')) {
        vals.push(Backdrop.t('Unpublishing enabled'));
      }
      return vals.join('<br/>');
    });
  }
};

})(jQuery);
