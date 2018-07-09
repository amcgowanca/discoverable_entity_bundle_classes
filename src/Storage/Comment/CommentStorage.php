<?php

namespace Drupal\discoverable_entity_bundle_classes\Storage\Comment;

use Drupal\comment\CommentStorageInterface;
use Drupal\discoverable_entity_bundle_classes\Storage\SqlContentEntityStorageTrait;
use Drupal\comment\CommentStorage as CommentStorageBase;

class CommentStorage extends CommentStorageBase implements CommentStorageInterface {

  use SqlContentEntityStorageTrait;

}
