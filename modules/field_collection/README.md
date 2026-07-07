Field Collection
================

Provides a field for entities (nodes, users, taxonomies, comments, etc.) to
which a collection or group of any number of fields can be included and treated
as a whole or single unit.

Each field collection item is internally represented as an entity, which is
referenced via the Field Collection field in the host entity. While
conceptually field collections are treated as part of the host entity, each
field collection item may also be viewed and edited separately.


Requirements
------------

This module requires that the following modules are also enabled:

- [Entity Plus](https://backdropcms.org/project/entity_plus)


Installation
------------

- Install this module using the official Backdrop CMS instructions at
  https://docs.backdropcms.org/documentation/extend-with-modules.


Configuration
-------------

- Use an entity's "Manage fields" interface to add a Field Collection field,
  e.g., "Administration > Structure > Content types > Post > Manage fields"

- After adding the field, go to the "Field collections" page (located at
  "Administration > Structure > Field collections") to define some fields for
  the created field collection (a direct link to the field collection is
  provided in the "Machine name" column on the "Manage fields" page)

- @todo This item might be wrong.
  By the default, the field collection is not shown during editing of the host
  entity. However, some links for adding, editing, or deleting field collection
  items are shown when the host entity is viewed.

- Widgets for embedding the form for creating field collections in the
  host-entity can be provided by any module.


@todo This section needs to be verified, and if still needed, moved to the wiki.
USING FIELD COLLECTION WITH ENTITY TRANSLATION
-----------

  * Field Collection items must be selected as a translatable entity type at
    Admin -> Config -> Regional -> Entity Translation.

  * The common use case is to leave the field collection field untranslatable
    and set the necessary fields inside it to translatable.  There is currently
    a known issue where a host can not be translated unless it has at least
    one other field that is translatable, even if some fields inside one of
    its field collections are translatable.

  * The alternate use case is to make the field collection field in the host
    translatable.  If this is done it does not matter whether the inner fields
    are set to translatable or not, they will all be translatable as every
    language for the host will have a completely separate copy of the field
    collection item(s).

  * When using nested field collections, the configuration of the fields and
    field collections is very important. The recommended approach is to first
    enable entity translation as defined in step 1, and set the outer
    field collection field as translatable and all its sub-fields to language
    undefined. The sub-field collections and its sub-fields should also be
    set to language undefined. This will ensure that every language of the host
    will have a completely separate copy of the field collection item(s) and
    its fields.


Issues
------

Bugs and feature requests should be reported in [the Issue Queue](https://github.com/backdrop-contrib/field_collection/issues).


Current Maintainers
-------------------

- [Jason Flatt (oadaeh)](https://github.com/oadaeh)
- [Eli Lisseck](https://github.com/elisseck)
- [Anthony Nemirovsky](https://github.com/anemirovsky)
- [Peter Haight](https://github.com/Dawnthorn)


Credits
-------

- Ported to Backdrop CMS by [Jason Flatt (oadaeh)](https://github.com/oadaeh)
- Originally written for Drupal by [Wolfgang Ziegler (fago)](https://www.drupal.org/u/fago)
- Inital development sponsored by [drunomics](https://drunomics.com/)
- Drupal maintainers:
  - [Renato Gonçalves (RenatoG)](https://www.drupal.org/u/renatog)
  - [Ra Mänd (ram4nd)](https://www.drupal.org/u/ram4nd)
  - [Joel Muzzerall (jmuzz)](https://www.drupal.org/u/jmuzz)
  - [Nedjo Rogers (nedjo)](https://www.drupal.org/u/nedjo)
  - [Joel Farris (Senpai)](https://www.drupal.org/u/senpai)
  - [Lee Rowlands (larowlan)](https://www.drupal.org/u/larowlan)


License
-------

This project is GPL v2 software.
See the LICENSE.txt file in this directory for complete text.
