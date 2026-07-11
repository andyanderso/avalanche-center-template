/**
 * @file
 * Conditions Alerts field: checking a child term auto-checks its parent(s).
 *
 * The parent map (child tid => parent tid) is provided from the server in
 * Backdrop.settings.avalancheConditionsAlerts.parents.
 */
(function ($) {
  "use strict";

  Backdrop.behaviors.avalancheConditionsAlerts = {
    attach: function (context, settings) {
      var parents = (settings.avalancheConditionsAlerts && settings.avalancheConditionsAlerts.parents) || {};
      var selector = 'input[type="checkbox"][name^="field_conditions_alerts_tax_term[und]"]';

      $(selector, context).once('avalanche-conditions-alerts').on('change', function () {
        if (!this.checked) {
          return;
        }
        var tid = this.value;
        var guard = {};
        // Walk up the hierarchy, checking each ancestor.
        while (parents[tid] && !guard[tid]) {
          guard[tid] = true;
          var parentTid = parents[tid];
          $(selector).filter('[value="' + parentTid + '"]').prop('checked', true);
          tid = String(parentTid);
        }
      });
    }
  };

})(jQuery);
