<?php

namespace Drupal\discoverable_entity_bundle_classes\Storage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A content entity database storage implementation.
 *
 * This class derives Drupal core's implementation to provide entity class
 * discoverability prior to instantiation. This is achbieved by using the plugin
 * manager `plugin.manager.discoverable_entity_bundle_classes`.
 */
class SqlContentEntityStorageBase extends SqlContentEntityStorage {

  use SqlContentEntityStorageTrait;

}
