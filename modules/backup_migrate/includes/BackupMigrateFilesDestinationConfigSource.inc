<?php
/**
 * @file
 * A destination type for saving locally to the server.
 */

require_once dirname(__FILE__) . '/sources.filesource.inc';

/**
 * A destination type for saving active/staging config folders to the server.
 *
 * @ingroup backup_migrate_destinations
 */
class BackupMigrateFilesDestinationConfigSource extends backup_migrate_destination_filesource {
  /**
   * {@inheritdoc}
   */
  function backup_settings_default() {
    return array(
      'exclude_filepaths' => '',
    );
  }
}
