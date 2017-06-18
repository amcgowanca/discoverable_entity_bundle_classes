## Discoverable Entity Bundle Classes

Currently in Drupal 8, there is _no_ mechanism for deriving a base entity type's class implementation with a unique class type on a per-bundle basis. As a result, if overriding the entity type class, that class type will be used for all instances in which that entity type is created.

_**This**_ module provides a simple proof of concept that takes control of the `SqlContentEntityStorage` to allow for derived content entity type classes on a per-bundle basis which are discovered through the `@ContentEntityBundleClass` annotation.
 
### License

This Drupal module is licensed under the [GNU General Public License](./LICENSE.md) version 2.
