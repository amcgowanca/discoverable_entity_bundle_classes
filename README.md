## Discoverable Entity Bundle Classes

Currently in Drupal 8, there is _no_ mechanism for deriving a base entity type's class implementation with a unique class type on a per-bundle basis. As a result, if overriding the entity type class, that class type will be used for all instances in which that entity type is created.

_**This**_ module provides a simple proof of concept that takes control of the `SqlContentEntityStorage` to allow for derived content entity type classes on a per-bundle basis which are discovered through the `@ContentEntityBundleClass` annotation.
 
### Usage

Point the storage handler for the entity type to DEBC. Here is an example for nodes:

```
/**
 * Set the storage handler for nodes to an alternate implementation.
 *
 * @param Drupal\Core\Entity\EntityTypeInterface[] $entity_types
 *   Entity types list.
 */
function mass_bundle_classes_entity_type_alter(array &$entity_types) {
  $entity_types['node']
    ->setStorageClass('\Drupal\discoverable_entity_bundle_classes\Storage\Node\NodeStorage');
}
```

Create your bundle class with appropriate annotation. Add getters and other methods as desired. For example, docroot/modules/custom/mass_bundle_classes/src/Entity/Alert.php

```
<?php

namespace Drupal\mass_bundle_classes\Entity;

use Drupal\discoverable_entity_bundle_classes\ContentEntityBundleInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\Routing\Route;

/**
 * Defines an Alert entity.
 *
 * @ContentEntityBundleClass(
 *   label = "Alert",
 *   entity_type = "node",
 *   bundle = "alert"
 * )
 */
class Alert extends Node implements ContentEntityBundleInterface {

  /**
   * Get the pages configured for this alert.
   */
  public function getPages() {
    // @todo.
  }

  /**
   * Should this alert display for a given route.
   *
   * @param Route $route
   *   A route.
   */
  public function isVisible(Route $route) {
    // @todo.
  }
}

```

### License

This Drupal module is licensed under the [GNU General Public License](./LICENSE.md) version 2.
