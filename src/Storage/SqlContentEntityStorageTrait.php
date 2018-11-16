<?php

namespace Drupal\discoverable_entity_bundle_classes\Storage;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\discoverable_entity_bundle_classes\UsesContentEntityBundleClassManagerTrait;

/**
 * Provides common SqlContentEntityStorage overrides for enabling discovery.
 */
trait SqlContentEntityStorageTrait {

  use UsesContentEntityBundleClassManagerTrait;

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
    return $this->getEntityClassManager()->getEntityClass($this->getEntityType(), $bundle);
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = []) {
    $entity_class = $this->getEntityClass($this->getBundleFromValues($values));
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

    $entity_class = $this->getEntityClass($bundle);
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

    // Document the type of the table mapping.
    /** @var \Drupal\Core\Entity\Sql\TableMappingInterface $table_mapping */
    $table_mapping = $this->tableMapping;

    // Get the names of the fields that are stored in the base table and, if
    // applicable, the revision table. Other entity data will be loaded in
    // loadFromSharedTables() and loadFromDedicatedTables().
    $field_names = $table_mapping->getFieldNames($this->baseTable);
    if ($this->revisionTable) {
      $field_names = array_unique(array_merge($field_names, $table_mapping->getFieldNames($this->revisionTable)));
    }

    $values = [];
    foreach ($records as $id => $record) {
      $values[$id] = [];
      // Skip the item delta and item value levels (if possible) but let the
      // field assign the value as suiting. This avoids unnecessary array
      // hierarchies and saves memory here.
      foreach ($field_names as $field_name) {

        $field_columns = $table_mapping->getColumnNames($field_name);
        // Handle field types that store several properties.
        if (count($field_columns) > 1) {
          foreach ($field_columns as $property_name => $column_name) {
            if (property_exists($record, $column_name)) {
              $values[$id][$field_name][LanguageInterface::LANGCODE_DEFAULT][$property_name] = $record->{$column_name};
              unset($record->{$column_name});
            }
          }
        }
        // Handle field types that store only one property.
        else {
          $column_name = reset($field_columns);
          if (property_exists($record, $column_name)) {
            $values[$id][$field_name][LanguageInterface::LANGCODE_DEFAULT] = $record->{$column_name};
            unset($record->{$column_name});
          }
        }
      }

      // Handle additional record entries that are not provided by an entity
      // field, such as 'isDefaultRevision'.
      foreach ($record as $name => $value) {
        $values[$id][$name][LanguageInterface::LANGCODE_DEFAULT] = $value;
      }
    }

    // Initialize translations array.
    $translations = array_fill_keys(array_keys($values), []);

    // Load values from shared and dedicated tables.
    $this->loadFromSharedTables($values, $translations, $load_from_revision);
    $this->loadFromDedicatedTables($values, $load_from_revision);

    $entities = [];
    foreach ($values as $id => $entity_values) {
      $bundle = $this->bundleKey ? $entity_values[$this->bundleKey][LanguageInterface::LANGCODE_DEFAULT] : FALSE;
      // Turn the record into an entity class by bundle.
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

  /**
   * Retrieve the bundle from an array for early type instantiation.
   *
   * @param array $values
   *   The values.
   *
   * @return string
   *   Returns the name of the bundle if specified by its key in $values. If no
   *   bundle key is specified, then NULL is returned.
   */
  protected function getBundleFromValues(array $values = []) {
    return isset($this->bundleKey) && isset($values[$this->bundleKey]) ? $values[$this->bundleKey] : NULL;
  }

}
