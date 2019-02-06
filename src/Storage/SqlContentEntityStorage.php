<?php

namespace Drupal\discoverable_entity_bundle_classes\Storage;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage as DrupalSqlContentEntityStorage;

/**
 * A content entity database storage implementation.
 *
 * This class derives Drupal core's implementation to provide entity class
 * discoverability prior to instantiation. This is achbieved by using the plugin
 * manager `plugin.manager.discoverable_entity_bundle_classes`.
 */
class SqlContentEntityStorage extends DrupalSqlContentEntityStorage {

  use SqlContentEntityStorageTrait;

}
