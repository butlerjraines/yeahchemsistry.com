<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Template\Attribute;

/**
 * The boolean data type with component methods.
 *
 * The plain value of a boolean is a regular PHP boolean. For setting the value
 * any PHP variable that casts to a boolean may be passed.
 */
class ComponentBooleanDataAttributeProvider extends ComponentBooleanData implements TypedComponentVariableInterface {

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
    // Add a class if the value is TRUE.
    if ((bool) $value) {
      $provided_value = $this->getDataDefinition()->getProvidedValue();
      assert(!empty($provided_value), 'A value is set for provided_value when provides_class is true');
      if (!$attribute->hasClass($provided_value)) {
        $attribute->addClass($provided_value);
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
    // Add an attribute if the value is TRUE.
    if ((bool) $value) {
      $provided_name = $this->getDataDefinition()->getProvidedName();
      assert(!empty($provided_name), 'A value is set for provided_name when provides_attribute is true');
      $provided_value = $this->getDataDefinition()->getProvidedValue();
      assert(!empty($provided_value), 'A value is set for provided_value when provides_class is true');
      $attribute->setAttribute($provided_name, $provided_value);
    }

    return $this;
  }

}
