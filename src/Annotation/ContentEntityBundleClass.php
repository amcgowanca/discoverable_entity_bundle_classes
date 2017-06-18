<?php

namespace Drupal\discoverable_entity_bundle_classes\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the discoverable entity bundle class annotation.
 *
 * @Annotation
 */
class ContentEntityBundleClass extends Plugin {

  /**
   * The human readable label of this derived entity class.
   *
   * @var string
   */
  public $label;

  /**
   * The machine name of the entity type that is being derived (e.g. node).
   *
   * @var string
   */
  public $entity_type;

  /**
   * The machine name of the bundle this class applies too.
   *
   * @var string
   */
  public $bundle;

  /**
   * Returns the Plugin ID.
   *
   * @return string
   *   The unique plugin id, created from the entity type and bundle.
   */
  public function getId() {
    return $this->definition['entity_type'] . ':' . $this->definition['bundle'];
  }

}
