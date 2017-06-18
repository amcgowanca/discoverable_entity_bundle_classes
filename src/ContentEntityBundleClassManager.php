<?php

namespace Drupal\discoverable_entity_bundle_classes;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Defines a content entity bundle class manager.
 */
class ContentEntityBundleClassManager extends DefaultPluginManager implements ContentEntityBundleClassManagerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * An array of entity bundle classes.
   *
   * @var string[]
   */
  protected $entityTypeClasses = [];

  /**
   * Creates a new ContentEntityBundleClassManager instance.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Entity',
      $namespaces,
      $module_handler,
      'Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassInterface',
      'Drupal\discoverable_entity_bundle_classes\Annotation\ContentEntityBundleClass'
    );
    $this->setCacheBackend($cache_backend, 'content_entity_bundle_class_plugins');

    // @todo: get this into a service arg.
    $this->entityTypeManager = \Drupal::entityTypeManager();
  }

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
  public function getEntityClass(EntityTypeInterface $entity_type, $bundle = NULL) {
    if (!isset($this->entityTypeClasses[$entity_type->id()])) {
      $this->entityTypeClasses[$entity_type->id()] = [
        0 => $entity_type->getClass(),
      ];
    }

    if (empty($bundle)) {
      return $this->entityTypeClasses[$entity_type->id()][0];
    }

    if (!isset($this->entityTypeClasses[$entity_type->id()][$bundle])) {
      $plugin_id = $entity_type->id() . ':' . $bundle;
      if ($definition = $this->getDefinition($plugin_id, FALSE)) {
        $entity_bundle_class = $definition['class'];
        try {
          $reflection = new \ReflectionClass($entity_bundle_class);
          if (!$reflection->isSubclassOf($entity_type->getClass())) {
            throw new \InvalidArgumentException(t('Entity bundle class !class must inherit entity base class: !entity_class.', array(
              '!class' => $entity_bundle_class,
              '!entity_class' => $entity_type->getClass(),
            )));
          }

          if (!$reflection->implementsInterface('\Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassInterface')) {
            throw new \InvalidArgumentException(t('Entity bundle class !class must implement interface ContentEntityBundleClassInterface.', array(
              '!class' => $entity_bundle_class,
            )));
          }

          $this->entityTypeClasses[$entity_type->id()][$bundle] = $entity_bundle_class;
        }
        catch (\Exception $exception) {
          throw $exception;
        }
      }
    }

    return $this->entityTypeClasses[$entity_type->id()][$bundle];
  }

}
