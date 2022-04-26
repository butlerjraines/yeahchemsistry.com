<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\component_schema\Component\Schema\TypedComponentVariableInterface;
use Drupal\Core\Template\Attribute;

trait ComponentVariableArrayElementTrait {

  /**
   * Helper method to initialize array-type values.
   *
   * @param array|null $value
   *   An array of property values.
   */
  protected function doInitializeValue(&$value) {
    if (isset($value) && !is_array($value)) {
      throw new \InvalidArgumentException("Invalid values given. Values must be represented as an associative array.");
    }

    $elements = $this->getElements();

    // First seed attributes, as the other element types may reference them.
    foreach ($elements as $name => $element) {
      if ($element instanceOf ComponentAttribute) {
        $element_value = $value[$name] ?? NULL;
        $element->initializeValue($element_value);
        $value[$name] = $element_value;
      }
    }

    // Conditionally add an HTML class.
    $definition = $this->getDataDefinition();
    if (($definition instanceof ComponentDataDefinitionInterface) && ($class = $definition->getHtmlClass())) {
      $attribute_name = $definition->getAttributeTarget();
      // We're targetting a child element.
      $attribute = $this
        ->get($attribute_name);
      if ($attribute && $attribute_target = $attribute->getValue()) {
        $attribute_target->addClass($class);
      }
    }

    // Iterate through non-attribute child elements.
    foreach ($elements as $name => $element) {
      if ($element instanceOf ComponentAttribute) {
        continue;
      }
      $element_value = $value[$name] ?? NULL;

      // ::initializeValue() is a method of TypedComponentVariableInterface.
      if ($element instanceOf TypedComponentVariableInterface) {
        $element->initializeValue($element_value);
      }

      $value[$name] = $element_value;
    }
  }

}
