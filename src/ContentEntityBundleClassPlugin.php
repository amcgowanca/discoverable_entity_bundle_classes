<?php

namespace Drupal\discoverable_entity_bundle_classes;

use InvalidArgumentException;

/**
 * Defines a single bundle class instance definition.
 */
class ContentEntityBundleClassPlugin {

  /**
   * The ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The definition.
   *
   * @var array
   */
  protected $definition = [];

  /**
   * ContentEntityBundleClassPlugin constructor.
   *
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   */
  public function __construct(string $plugin_id, array $plugin_definition) {
    $this->id = $plugin_id;
    $this->definition = $plugin_definition;
  }

  /**
   * Returns the entity bundle class name.
   *
   * @return string
   *   The class name.
   *
   * @throws \InvalidArgumentException
   *   If the definition does not contain a `class`, an exception is thrown.
   */
  public function getEntityClass() {
    if (isset($this->definition['class'])) {
      return $this->definition['class'];
    }

    throw new InvalidArgumentException();
  }

  /**
   * Returns the handlers defined by plugin.
   *
   * @return array
   *   The handlers.
   */
  public function getHandlers() {
    return isset($this->definition['handlers']) ? $this->definition['handlers'] : [];
  }

}
