<?php

namespace Drupal\discoverable_entity_bundle_classes\Storage\Comment;

use Drupal\comment\CommentStorageInterface;
use Drupal\comment\CommentStorage as CommentStorageBase;
use Drupal\discoverable_entity_bundle_classes\Storage\SqlContentEntityStorageTrait;

/**
 * Defines the Comment Storage class for discoverable entity bundle classes.
 */
class CommentStorage extends CommentStorageBase implements CommentStorageInterface {

  use SqlContentEntityStorageTrait;

}
