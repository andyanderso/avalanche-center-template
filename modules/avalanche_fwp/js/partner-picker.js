(function ($, Backdrop) {

  'use strict';

  /**
   * Fills a trip-member row from a picked teammate/partner, client-side.
   *
   * The picker select sits inside the field-collection item, so its own form
   * name gives the item's field-name prefix; we set the sibling inputs by name.
   */
  Backdrop.behaviors.avalancheFwpPartnerPicker = {
    attach: function (context, settings) {
      var people = (settings.avalancheFwp && settings.avalancheFwp.people) || {};

      $('select.avalanche-fwp-picker', context).once('afwp-picker').each(function () {
        $(this).on('change', function () {
          var person = people[this.value];
          if (!person) {
            return;
          }
          // e.g. "field_trip_members[und][0][avalanche_fwp_picker]" -> prefix.
          var prefix = this.name.replace(/\[avalanche_fwp_picker\]$/, '');

          function setField(suffix, value) {
            if (value === undefined || value === null || value === '') {
              return;
            }
            var el = document.querySelector('[name="' + prefix + suffix + '"]');
            if (el) {
              el.value = value;
            }
          }

          setField('[field_trip_member_name][und][0][value]', person.name);
          setField('[field_trip_member_phone_number][und][0][value]', person.phone);
          setField('[field_trip_member_email][und][0][value]', person.email);
          setField('[field_emergency_contact][und][0][field_emergency_contact_name][und][0][value]', person.em_name);
          setField('[field_emergency_contact][und][0][field_emergency_contact_phone_nu][und][0][value]', person.em_phone);
        });
      });
    }
  };

})(jQuery, Backdrop);
