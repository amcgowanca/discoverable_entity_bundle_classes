## Paragraphs

This module provides support for the Paragraphs contributed module and allows for derived Paragraph entity class implementations through the Discoverable Entity Bundle Classes module.

### How to use

To implement derived `\Drupal\paragraphs\Entity\Paragraph` classes that are recognized, simply create your new entity class which will provide a specialized implementation in `my_module/src/Entity` and annotate it with `@ContentEntityBundleClass`. For example:

```php

namespace Drupal\my_module\Entity;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\discoverable_entity_bundle_classes\ContentEntityBundleInterface;


/**
 * Defines a SpecializedParagraph type class.
 *
 * @ContentEntityBundleClass(
 *   label = "Specialized paragraph",
 *   entity_type = "paragraph",
 *   bundle = "specialized_bundle"
 * )
 */
class SpecializedParagraph extends Paragraph implements ContentEntityBundleInterface {
  
  /**
   * The speciality.
   * 
   * @var string
   */
  protected $foo = 'bar';
  
  /**
   * Returns this paragraph's speciality.
   *
   * @return string
   *   The speciality.
   */
  public function getMySpeciality() {
    return $this->foo;
  }
  
}

```

In this particular case, the paragraph type named `specialized_paragraph` will now be represented as instances of `SpecializedParagraph`.
