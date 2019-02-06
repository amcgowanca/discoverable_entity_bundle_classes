<?php

namespace Drupal\discoverable_entity_bundle_classes;

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Interface for defining content entity bundle class managers.
 */
interface ContentEntityBundleClassManagerInterface {

  /**
   * Retrieves the plugin instance given an entity type & bundle.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle name.
   *
   * @return \Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassPlugin
   *   Returns an object representation of the definition if a plugin exists.
   *   Should no plugin exist, NULL is returned.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   Thrown at time of plugin instance creation.
   */
  public function getPlugin(EntityTypeInterface $entity_type, $bundle);

  /**
   * Returns the class name for the entity type and bundle.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param string $bundle
   *   The name of the entity bundle. If not specified, the entity provider's
   *   base class name will be returned.
   *
   * @return string
   *   The entity class name.
   *
   * @throws \Exception
   *   Thrown if the specialized implementation does not meet criteria.
   */
  public function getEntityClass(EntityTypeInterface $entity_type, $bundle = NULL);

}
