/**
 * Keep left-aligned TB Mega Menu panels within the viewport.
 *
 * The top-level items use "left" submenu alignment, so each mega panel opens
 * with its left edge under its parent item. Wide panels triggered by items in
 * the centre/right of a centred menu would overflow the right edge of the
 * screen, so this behaviour nudges an open panel left (or right) just enough to
 * stay fully visible. Disabled on the mobile/collapsed layout.
 */
(function ($, Backdrop) {
  'use strict';

  var BREAKPOINT = 980;
  var PAD = 10;

  function repositionPanel(panel) {
    if (!panel) {
      return;
    }
    var $panel = $(panel);
    // Always clear any prior nudge before measuring.
    $panel.css('margin-left', '');

    if (document.documentElement.clientWidth < BREAKPOINT) {
      return;
    }

    var vw = document.documentElement.clientWidth;
    var rect = panel.getBoundingClientRect();
    var shift = 0;

    if (rect.right > vw - PAD) {
      shift = -(rect.right - (vw - PAD));
    }
    if (rect.left + shift < PAD) {
      shift = PAD - rect.left;
    }
    if (shift) {
      $panel.css('margin-left', Math.round(shift) + 'px');
    }
  }

  function panelOf(li) {
    var child = li.children;
    for (var i = 0; i < child.length; i++) {
      if (child[i].className && child[i].className.indexOf('mega-dropdown-menu') !== -1) {
        return child[i];
      }
    }
    return null;
  }

  Backdrop.behaviors.tbMegaMenuKeepInView = {
    attach: function (context) {
      $('.tb-megamenu .tb-megamenu-item.mega.dropdown', context).once('tb-keepinview').each(function () {
        var li = this;
        var open = function () {
          window.setTimeout(function () {
            repositionPanel(panelOf(li));
          }, 0);
        };
        $(li).on('mouseenter focusin', open);
      });

      $('body').once('tb-keepinview-resize').each(function () {
        $(window).on('resize', function () {
          $('.tb-megamenu .tb-megamenu-item.mega.dropdown').each(function () {
            repositionPanel(panelOf(this));
          });
        });
      });
    }
  };
})(jQuery, Backdrop);
