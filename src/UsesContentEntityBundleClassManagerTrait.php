<?php

namespace Drupal\discoverable_entity_bundle_classes;

/**
 * Utility trait to easily access the discoverable entity class service.
 */
trait UsesContentEntityBundleClassManagerTrait {

  /**
   * Returns the entity class manager service.
   *
   * @return \Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassManagerInterface
   *   The class manager.
   */
  public function entityClassManager() {
    return \Drupal::service('plugin.manager.discoverable_entity_bundle_classes');
  }

}
