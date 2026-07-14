/**
 * @file
 * "Center setup" install form: default the danger-scale preset from the
 * chosen language. Spanish centers are South American, so picking Spanish
 * selects the SAC preset (colors + travel advice + Sudamérica danger-scale
 * page); English selects NAC. The operator can still override either radio
 * afterward - this only sets the default to the sensible pairing.
 */
(function ($) {
  Backdrop.behaviors.avalancheSetupDangerScale = {
    attach: function (context) {
      var $scale = $(context).find('input[name="danger_scale"]');
      if (!$scale.length) {
        return;
      }
      var $form = $scale.closest('form');
      $form.once('av-danger-scale', function () {
        var $f = $(this);
        var sync = function () {
          var preset = ($f.find('input[name="language"]:checked').val() === 'es') ? 'SAC' : 'NAC';
          $f.find('input[name="danger_scale"][value="' + preset + '"]').prop('checked', true);
        };
        $f.find('input[name="language"]').on('change', sync);
        // Apply once so the initial language selection sets the matching preset.
        sync();
      });
    }
  };
})(jQuery);
