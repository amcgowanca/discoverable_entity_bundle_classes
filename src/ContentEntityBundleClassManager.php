<?php

namespace Drupal\discoverable_entity_bundle_classes;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines a content entity bundle class manager.
 */
class ContentEntityBundleClassManager extends DefaultPluginManager implements ContentEntityBundleClassManagerInterface {

  use StringTranslationTrait;

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
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $definition = $this->getDefinition($plugin_id);
    return new ContentEntityBundleClassPlugin($plugin_id, $definition, $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin(EntityTypeInterface $entity_type, $bundle) {
    $plugin_id = $entity_type->id() . ':' . $bundle;
    if ($this->hasDefinition($plugin_id)) {
      return $this->createInstance($plugin_id);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
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
            throw new \InvalidArgumentException($this->t('Entity bundle class !class must inherit entity base class: !entity_class.', [
              '!class' => $entity_bundle_class,
              '!entity_class' => $entity_type->getClass(),
            ]));
          }

          if (!$reflection->implementsInterface('\Drupal\discoverable_entity_bundle_classes\ContentEntityBundleInterface')) {
            throw new \InvalidArgumentException($this->t('Entity bundle class !class must implement interface ContentEntityBundleClassInterface.', [
              '!class' => $entity_bundle_class,
            ]));
          }

          $this->entityTypeClasses[$entity_type->id()][$bundle] = $entity_bundle_class;
        }
        catch (\Exception $exception) {
          throw $exception;
        }
      }
      else {
        $this->entityTypeClasses[$entity_type->id()][$bundle] = $entity_type->getClass();
      }
    }

    return $this->entityTypeClasses[$entity_type->id()][$bundle];
  }

}
