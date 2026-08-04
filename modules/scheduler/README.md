# Scheduler

This module allows content to be published and unpublished on specified dates.

Dates can be entered either as plain text or using a HTML Datetime field.

**NOTE**: A simplified version of the scheduled publishing functionality is now
available in Backdrop core. If that is all you need, you may not need this
module. It might get confusing to try to use both at once.

## Installation

 - Install this module and its dependencies using the official
  [Backdrop CMS instructions](https://backdropcms.org/guide/modules)

## Configuration and Usage

 - Grant users the permission "Schedule content publication" to allow them to
   set when the content they create is to be (un)published.
 - Checkboxes are provided when configuring content types, allowing you to
 enable scheduled publishing and/or unpublishing for that content type:
 ![Scheduler Configuration](https://github.com/backdrop-contrib/scheduler/blob/1.x-1.x/images/scheduler-content-type-configuration.jpg "Scheduler Configuration")
 - Further documentation may be available in the
 [Wiki](https://github.com/backdrop-contrib/scheduler/wiki).

## Issues

Bugs and Feature requests should be reported in the
[Issue Queue](https://github.com/backdrop-contrib/scheduler/issues). **Please
check if cron is running correctly if scheduler does not (un)publish your
scheduled content.**

## Current Maintainers

 - [Laryn Kragt Bakker](https://github.com/laryn)

## Credits

 - Ported to Backdrop CMS by [Laryn Kragt Bakker](https://github.com/laryn).
 - Early Backdrop work was sponsored by [CEDC.org](https://cedc.org).
 - Scheduler for Drupal is the work of many people.
[Some of them are listed here](https://www.drupal.org/node/3292/committers),
but there are even more: All the people who created patches but did not check
them in themselves, who posted bug or feature requests and those who provided
translations and documentation.
 - This module was originally written for Drupal 4.5.0 by Moshe Weitzman, Gabor
Hojtsy, and Tom Dobes. It was completely rewritten for Drupal 4.7 by Ted Serbinski.

## License

This project is GPL v2 software. See the [LICENSE.txt](https://github.com/backdrop-contrib/scheduler/blob/1.x-1.x/LICENSE.txt)
file in this directory for complete text.
