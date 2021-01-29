<?php

namespace Drupal\discoverable_entity_bundle_classes\Storage\Media;

use Drupal\discoverable_entity_bundle_classes\Storage\SqlContentEntityStorageTrait;
use Drupal\media\MediaStorage as MediaStorageBase;
use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines the Media Storage class for discoverable entity bundle classes.
 */
class MediaStorage extends MediaStorageBase implements ContentEntityStorageInterface {

  use SqlContentEntityStorageTrait;

}
