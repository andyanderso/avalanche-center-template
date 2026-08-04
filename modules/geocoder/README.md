Geocoder
========

Geocoder is a module that will extract geographical data (geocode) from
addresses, GPX files, Geotags from EXIF data in photos, and KML files.

Use this module in combination with the Addressfield module
(https://github.com/backdrop-contrib/addressfield) and Geofield module
(https://github.com/backdrop-contrib/geofield) for a convenient way to get
geo data.

Geocoder uses external geocoding services from Google, Yahoo, and Yandex.

```
                        (
 (  (    (               )\ )  (  (
 )\))(  ))\ (    (  (   (()/( ))\ )(
((_))\ /((_))\   )\ )\   ((_))((_|()\
 (()(_|_)) ((_) ((_|(_)  _| (_))  ((_)
/ _` |/ -_) _ \/ _/ _ \/ _` / -_)| '_|
\__, |\___\___/\__\___/\__,_\___||_|
|___/
```

Requirements
------------

- [Geofield module](https://backdropcms.org/project/geofield).


Installation
------------

- Install this module and the required geoPHP module using the official
  Backdrop CMS instructions at https://backdropcms.org/guide/modules.

- Install the (optional) Addressfield module.

- Assign any necessary permissions.

- If you have enabled the modules Addressfield and Geofield you can start using
  Geocoder on a content type.

  - Add a new address field. Go to Administration > Structure > Content types
    (admin/structure/types), choose the desired content type, and select
    "Manage fields".

    - Add a Postal address field and configure the field however you like.

    - Add a Geofield and select "Geocode from another field" as the widget.

      - In the settings for the geofield you can now choose the source field to
        geocode from. You can also choose which geocoding service (Google etc)
        to use and configure the service.

  - Now you have a place where Geocoder can store its result (geofield) and the
    input to the geocode operation (addressfield).


Documentation
-------------

Documentation for Geocoder is available on Drupal.org:
http://drupal.org/node/1355780

Additional documentation is located in the Wiki:
https://github.com/backdrop-contrib/geocoder/wiki/Documentation.

Issues
------

Bugs and Feature requests should be reported in the Issue Queue:
https://github.com/backdrop-contrib/geocoder/issues.


Current Maintainers
-------------------

- Jen Lampton (https://github.com/jenlampton)
- Wes Jones (https://github.com/earthday47)
- Seeking additional maintainers.

Credits
-------

- Port to Backdrop CMS completed Juan Olalla (https://github.com/juanolalla)
- Port to Backdrop CMS improved by Wes Jones (https://github.com/earthday47)
- Port to Backdrop CMS started by gifad (https://github.com/gifad)
- Originally written for Drupal by: cspiker, phayes, henryblyth, jeff h,
  Les Lim, mikeytown2, fago, patrickavella & michaelfavia

License
-------

This project is GPL v2 software. See the LICENSE.txt file in this directory for
complete text.
