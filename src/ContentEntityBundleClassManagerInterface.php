<?php

namespace Drupal\discoverable_entity_bundle_classes;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Interface for defining content entity bundle class managers.
 */
interface ContentEntityBundleClassManagerInterface {

  /**
   * Returns the entity class name.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle machine name to retrieve a specialized class for. If empty,
   *   the entity provider's base entity class name is returned.
   *
   * @return string
   *   The class name.
   */
  public function getEntityClass(EntityTypeInterface $entity_type, $bundle = NULL);

}
