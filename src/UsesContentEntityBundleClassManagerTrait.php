<?php

namespace Drupal\discoverable_entity_bundle_classes;

trait UsesContentEntityBundleClassManagerTrait {

  public function getEntityClassManager() {
    return \Drupal::service('plugin.manager.discoverable_entity_bundle_classes');
  }

}