<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Template\Attribute;

/**
 * The string component data type.
 *
 * The plain value of a string is a regular PHP string. For setting the value
 * any PHP variable that casts to a string may be passed.
 */
class ComponentStringDataAttributeProvider extends ComponentStringData implements TypedComponentVariableInterface {

  use ComponentVariablePrimitiveElementTrait, ComponentVariableAttributeProviderTrait {
    ComponentVariableAttributeProviderTrait::initializeValue insteadof ComponentVariablePrimitiveElementTrait;
  }

  /**
   * Overrides \Drupal\component_schema\Component\Schema\ComponentVariableAttributeProviderTrait::addClass()
   *
   * @param string $value
   *   The class to add.
   * @param \Drupal\Core\Template\Attribute $attribute
   *   An Attribute object.
   *
   * @return $this
   */
  protected function addClass($value, Attribute $attribute) {
    if (!$this->getDataDefinition()->providesClass()) {
      throw new SchemaBadMethodCallException(__METHOD__ . " is not allowed on a data object that doesn't provide a class");
    }
    // Add a class equal to the value.
    if ($string = (string) $value) {
      if (!$attribute->hasClass($string)) {
        $attribute->addClass($string);
      }
    }

    return $this;
  }

  /**
   * Overrides \Drupal\component_schema\Component\Schema\ComponentVariableAttributeProviderTrait::setAttribute()
   *
   * @param string $value
   *   The value to set.
   * @param \Drupal\Core\Template\Attribute $attribute
   *   An Attribute object.
   *
   * @return $this
   */
  protected function setAttribute($value, Attribute $attribute) {
    if (!$this->getDataDefinition()->providesAttribute()) {
      throw new SchemaBadMethodCallException(__METHOD__ . " is not allowed on a data object that doesn't provide an attribute");
    }
    // Add an attribute if a value is set.
    if ($value && is_string($value)) {
      $provided_name = $this->getDataDefinition()->getProvidedName();
      assert(!empty($provided_name), 'A value is set for provided_name when provides_attribute is true');
      $attribute->setAttribute($provided_name, $value);
    }

    return $this;
  }

}
