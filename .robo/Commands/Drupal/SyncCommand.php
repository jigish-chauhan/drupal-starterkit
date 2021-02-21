<?php

// @codingStandardsIgnoreStart

use Robo\Robo;
require '../../RoboFile.php';

/**
 * Defines the sync commands.
 */
class SyncCommand extends RoboFile {

  /**
   * Sync database from remote server.
   */
  public function syncDb() {
    $this->say('sync:db');
    $config = Robo::config();
    $remote_alias = '@' . $config->get('project.machine_name') . '.' . $config->get('sync.remote');
    $local_alias = '@self';
    $collection = $this->collectionBuilder();
    $collection->addTask(
      $this->drush()
        ->args('sql-sync')
        ->arg($remote_alias)
        ->arg($local_alias)
        ->option('--target-dump', sys_get_temp_dir() . '/tmp.target.sql.gz')
        ->option('structure-tables-key', 'lightweight')
        ->option('create-db')
    );
    if ($config->get('sync.sanitize') === TRUE) {
      $collection->addTask(
        $this->drush()
          ->args('sql-sanitize')
      );
    }
    $result = $collection->run();

    return $result;
  }

  /**
   * Sync files from remote server.
   */
  public function syncFiles() {
    $this->say('sync:files');
    $config = Robo::config();
    $remote_alias = '@' . $config->get('project.machine_name') . '.' . $config->get('sync.remote');
    $local_alias = '@self';
    $task = $this->drush()
      ->args('core-rsync')
      ->arg($remote_alias . ':%files')
      ->arg($local_alias . ':%files');
    $result = $task->run();

    return $result;
  }

}
