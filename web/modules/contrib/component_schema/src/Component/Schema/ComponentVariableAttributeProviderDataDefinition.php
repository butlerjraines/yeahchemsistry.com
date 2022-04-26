<?php

namespace Drupal\component_schema\Component\Schema;

class ComponentVariableAttributeProviderDataDefinition extends ComponentVariableDataDefinition implements ComponentVariableAttributeProviderDataDefinitionInterface {

  /**
   * {@inheritdoc}
   */
  public function providesClass() {
    return !empty($this->definition['provides_class']);
  }

  /**
   * {@inheritdoc}
   */
  public function providesAttribute() {
    return !empty($this->definition['provides_attribute']);
  }

  /**
   * {@inheritdoc}
   */
  public function getProvidedName() {
    return $this->definition['provided_name'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getProvidedValue() {
    return $this->definition['provided_value'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeTarget() {
    return $this->definition['attribute_target'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsBreakpoints() {
    return $this->definition['supports_breakpoints'] ?? NULL;
  }

}
