<?php

namespace Drupal\discoverable_entity_bundle_classes\Storage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A content entity database storage implementation.
 *
 * This class derives Drupal core's implementation to provide entity class
 * discoverability prior to instantiation. This is achbieved by using the plugin
 * manager `plugin.manager.discoverable_entity_bundle_classes`.
 */
class SqlContentEntityStorageBase extends SqlContentEntityStorage {

  /**
   * The entity bundle class manager.
   *
   * @var \Drupal\discoverable_entity_bundle_classes\ContentEntityBundleClassManagerInterface
   */
  protected $entityClassManager;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('entity.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('plugin.manager.discoverable_entity_bundle_classes')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityManagerInterface $entity_manager, CacheBackendInterface $cache, LanguageManagerInterface $language_manager, ContentEntityBundleClassManagerInterface $entity_class_manager = NULL) {
    parent::__construct($entity_type, $database, $entity_manager, $cache, $language_manager);
    $this->entityClassManager = $entity_class_manager;
  }

  /**
   * Returns the entity class name for instantiation.
   *
   * @param string $bundle
   *   The bundle name to retrieve specialized class for.
   *
   * @return string
   *   The class name.
   */
  protected function getEntityClass($bundle = NULL) {
    return $this->entityClassManager->getEntityClass($this->getEntityType(), $bundle);
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = []) {
    $entity_class = $this->getEntityClass();
    $entity_class::preCreate($this, $values);

    // Assign a new UUID if there is none yet.
    if ($this->uuidKey && $this->uuidService && !isset($values[$this->uuidKey])) {
      $values[$this->uuidKey] = $this->uuidService->generate();
    }

    $entity = $this->doCreate($values);
    $entity->enforceIsNew();

    $entity->postCreate($this);

    // Modules might need to add or change the data initially held by the new
    // entity object, for instance to fill-in default values.
    $this->invokeHook('create', $entity);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function doCreate(array $values) {
    // We have to determine the bundle first.
    $bundle = FALSE;
    if ($this->bundleKey) {
      if (!isset($values[$this->bundleKey])) {
        throw new EntityStorageException('Missing bundle for entity type ' . $this->entityTypeId);
      }
      $bundle = $values[$this->bundleKey];
    }

    $entity_class = $this->getEntityClass();
    $entity = new $entity_class([], $this->entityTypeId, $bundle);
    $this->initFieldValues($entity, $values);
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function mapFromStorageRecords(array $records, $load_from_revision = FALSE) {
    if (!$records) {
      return [];
    }

    $values = [];
    foreach ($records as $id => $record) {
      $values[$id] = [];
      // Skip the item delta and item value levels (if possible) but let the
      // field assign the value as suiting. This avoids unnecessary array
      // hierarchies and saves memory here.
      foreach ($record as $name => $value) {
        // Handle columns named [field_name]__[column_name] (e.g for field types
        // that store several properties).
        if ($field_name = strstr($name, '__', TRUE)) {
          $property_name = substr($name, strpos($name, '__') + 2);
          $values[$id][$field_name][LanguageInterface::LANGCODE_DEFAULT][$property_name] = $value;
        }
        else {
          // Handle columns named directly after the field (e.g if the field
          // type only stores one property).
          $values[$id][$name][LanguageInterface::LANGCODE_DEFAULT] = $value;
        }
      }
    }

    // Initialize translations array.
    $translations = array_fill_keys(array_keys($values), []);

    // Load values from shared and dedicated tables.
    $this->loadFromSharedTables($values, $translations);
    $this->loadFromDedicatedTables($values, $load_from_revision);

    $entities = [];
    foreach ($values as $id => $entity_values) {
      $bundle = $this->bundleKey ? $entity_values[$this->bundleKey][LanguageInterface::LANGCODE_DEFAULT] : FALSE;
      // Turn the record into an entity class.
      $entity_class = $this->getEntityClass($bundle);
      $entities[$id] = new $entity_class($entity_values, $this->entityTypeId, $bundle, array_keys($translations[$id]));
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  protected function postLoad(array &$entities) {
    $entity_class = $this->getEntityClass();
    $entity_class::postLoad($this, $entities);
    // Call hook_entity_load().
    foreach ($this->moduleHandler()->getImplementations('entity_load') as $module) {
      $function = $module . '_entity_load';
      $function($entities, $this->entityTypeId);
    }
    // Call hook_TYPE_load().
    foreach ($this->moduleHandler()->getImplementations($this->entityTypeId . '_load') as $module) {
      $function = $module . '_' . $this->entityTypeId . '_load';
      $function($entities);
    }
  }

}