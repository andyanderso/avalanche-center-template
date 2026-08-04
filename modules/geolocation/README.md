Geolocation
===========

Port of the Drupal 7 module [*Geolocation Field*](https://www.drupal.org/project/geolocation).

Geolocation provides a field type to store geographical locations as pairs of latitude and longitude (lan,lng). Geolocation fields can be used with all fieldable entities like nodes, users, comments, taxonomy terms, etc.

Geolocation aims to provide a light-weight, easy-to-use and robust alternative to more complex solutions.


Install
-------

Install this module using the official Backdrop CMS instructions at https://backdropcms.org/guide/modules


Configure
---------

To add a geofield to a content type go to `/admin/structure/types/` and choose "Manage fields" for the chosen content type. 
Add a new field of the field type *Geolocation*.

**Note:** you can also add a geolocation field to a user, a taxonomy term or a comment.


Technical background
--------------------

At the moment Geolocation stores the following values in the database:

- `lat` (latitude),
- `lng` (longitude),
- `lat_sin` (precalculated latitude sine),
- `lat_cos` (precalculated latitude cosine),
- `lng_rad` (precalculated radian longitude).

The precalculated values will speed up proximity SQL look ups.


License
-------

This project is GPL v2 software. See the LICENSE.txt file in this directory for complete text.


Current Maintainers
-------------------

- [opi](https://github.com/opi)


Credits
-------

- Ported to Backdrop CMS by [opi](https://github.com/opi)
- Originally written for Drupal by: [Jochen Meyer (derjochenmeyer)](https://www.drupal.org/u/derjochenmeyer) and [Christian Adamski (ChristianAdamski)](https://www.drupal.org/u/christianadamski)
- Every contributors to Drupal project: https://www.drupal.org/node/241478/committers
