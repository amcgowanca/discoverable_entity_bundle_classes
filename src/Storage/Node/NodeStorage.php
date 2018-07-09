<?php

namespace Drupal\discoverable_entity_bundle_classes\Storage\Node;

use Drupal\discoverable_entity_bundle_classes\Storage\SqlContentEntityStorageTrait;
use Drupal\node\NodeStorage as NodeStorageBase;
use Drupal\node\NodeStorageInterface;

/**
 * Defines the Node Storage class for discoverable entity bundle classes.
 */
class NodeStorage extends NodeStorageBase implements NodeStorageInterface {

  use SqlContentEntityStorageTrait;

}
