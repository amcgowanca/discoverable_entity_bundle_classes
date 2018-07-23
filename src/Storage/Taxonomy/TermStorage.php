<?php

namespace Drupal\discoverable_entity_bundle_classes\Storage\Taxonomy;

use Drupal\discoverable_entity_bundle_classes\Storage\SqlContentEntityStorageTrait;
use Drupal\taxonomy\TermStorage as TermStorageBase;
use Drupal\taxonomy\TermStorageInterface;

/**
 * Defines the Term storage class for discoverable entity bundle classes.
 */
class TermStorage extends TermStorageBase implements TermStorageInterface {

  use SqlContentEntityStorageTrait;

}
