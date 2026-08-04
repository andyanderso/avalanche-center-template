(function ($) {
  'use strict';

  // Forecast tabs (Avalanche Forecast / Weather).
  Backdrop.behaviors.amForecastTabs = {
    attach: function (context) {
      $('.am-forecast .nac-nav-tabs', context).once('am-tabs', function () {
        var $tabs = $(this);
        var $forecast = $tabs.closest('#nac-app');
        $tabs.on('click', '.nac-nav-link', function (e) {
          e.preventDefault();
          var target = $(this).attr('data-am-tab');
          $tabs.find('.nac-nav-link').removeClass('active');
          $(this).addClass('active');
          $forecast.find('.am-tabpane').each(function () {
            $(this).css('display', $(this).attr('data-am-pane') === target ? '' : 'none');
          });
        });
      });
    }
  };

  // Mobile navigation toggle.
  Backdrop.behaviors.amNav = {
    attach: function (context) {
      $('.am-nav-toggle', context).once('am-nav', function () {
        var $btn = $(this);
        $btn.on('click', function () {
          var $header = $btn.closest('.am-header');
          var open = $header.toggleClass('nav-open').hasClass('nav-open');
          $btn.attr('aria-expanded', open ? 'true' : 'false');
        });
      });
    }
  };

})(jQuery);
