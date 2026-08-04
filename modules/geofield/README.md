Geofield
========

Geofield is a Backdrop module that provides a field types for storing
geographic data. This data can be attached to any entity, e.g., nodes, users
and taxonomy terms. Geofield provides different widgets for data input and
formatters for data output. The Geofield module can can store data as Latitude
and Longitude, Bounding Box and Well Known Text (WKT) and it supports all
types of geographical data: points, lines, polygons, multitypes et cetera.

Great documentation on Geofield can be found at http://drupal.org/node/1089574

## Geofield Map submodule

Geofield comes with the basic but easy-to-use submodule Geofield Map that
allows you to display geographical data in a Google map. Enable Geofield Map
at /admin/modules. Read more about Geofield Map at
http://drupal.org/node/1466490

## OpenLayers Integration

For more advanced and flexible data display you need to configure or create a
map in OpenLayers at /admin/structure/openlayers/maps. You can easily create
your own map by cloning an existing one. An introduction to OpenLayers can be
found here: http://drupal.org/node/1481374.

When you have configured a map in OpenLayers you must define to use the map.
Go to  /admin/structure/types and choose "Manage display".


Requirements <!-- Do not include this section if there are no requirements. -->
------------

This module requires that the following modules are also enabled:

* No Requirements (The GeoPHP Library is included in this module).


Installation <!-- This section is required. -->
------------

- Install this module and geophp using the official Backdrop CMS instructions
  at https://backdropcms.org/guide/modules

- Optionally install Open Layers 2: http://drupal.org/project/openlayers

- To add a geofield to a content type go to /admin/structure/types/ and choose
  "Manage fields" for the chosen content type.

- Add a new field of the field type "Geofield", and choose the preferred widget,
  e.g., "OpenLayers Map". Configure the field according ton the chosen options.

- Note: you can add a geofield to any fieldable entity including a user, a
  taxonomy term, or a comment.


API Notes
---------

Geofield fields contain nine columns of information about the geographic data
that is stores. At its heart is the 'wkt' column where it stores the full
geometry in the 'Well Known Text' (WKT) format. All other columns are metadata
derived from the WKT column. Columns are as follows:

  'geom'         Raw value. By default, stored as WKB, loaded as WKT
  'geo_type'     Type of geometry (point, linestring, polygon etc.)
  'lat'          Centroid (Latitude or Y)
  'lon'          Centroid (Longitude or X)
  'top'          Bounding Box Top (Latitude or Max Y)
  'bottom'       Bounding Box Bottom (Latitude or Min Y)
  'left'         Bounding Box Left (Longitude or Min X)
  'right'        Bounding Box Right (Longitude or Max X)
  'geohash'      Geohash equivalent of geom column value

When a geofield is saved using the provided widgets, these values are passed
through the geofield_compute_values function in order to compute dependent
values. By default dependent values are computed based on WKT, but this may be
overriden to compute values based on other columns. For example,
geofield_compute_values may be called like so:

  geofield_compute_values($values, 'latlon');

This will compute the wkt field (and all other fields) based on the lat/lon
columns, resulting in a point. As a developer this is important to remember if
you modify geofield information using node_load and node_save. Make sure to
run any modified geofield instances through geofield_compute_values in order
to make all columns consistent.


Documentation
-------------

Additional documentation is located in the Wiki:
https://github.com/backdrop-contrib/geofield/wiki/Documentation.

Issues
------

Bugs and Feature requests should be reported in the Issue Queue:
https://github.com/backdrop-contrib/geofield/issues.

Current Maintainers
-------------------

- Wes Jones (https://github.com/earthday47)
- Jen Lampton (https://github.com/jenlampton)
- indigoxela (https://github.com/indigoxela)

Credits
-------

- Ported to Backdrop CMS by gifad (https://github.com/gifad)
- Originally written for Drupal by: Tristan O'Neil
- Contributors to Drupal project: Alex Barth, Jeff Miccolis, Young Hahn, Tom
  MacWright, Patrick Hayes, Dave Tarc, Nikhil Trivedi, Marek Sotak, Khalid
  Jebbari, Brandon Morrison, David Peterson

License <!-- This section is required. -->
-------

* This project is GPL v2 software.
  See the LICENSE.txt file in this directory for complete text.

* The bundled GeoPHP Library is dual-licensed under both GPL v2 and the Modified
  BSD License. See the file libraries/geophp/LICENSE for complete text.
