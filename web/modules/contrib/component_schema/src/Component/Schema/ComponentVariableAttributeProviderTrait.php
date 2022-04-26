<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Template\Attribute;

trait ComponentVariableAttributeProviderTrait {

  /**
   * Adds classes or merges them on to array of existing CSS classes.
   *
   * @param string $value
   *   The class to add.
   * @param \Drupal\Core\Template\Attribute $attribute
   *   An Attribute object.
   *
   * @return $this
   */
  protected abstract function addClass($value, Attribute $attribute);

  /**
   * Sets values for an HTML attribute.
   *
   * @param string $value
   *   The value to set.
   * @param \Drupal\Core\Template\Attribute $attribute
   *   An Attribute object.
   *
   * @return $this
   */
  protected abstract function setAttribute($value, Attribute $attribute);

  /**
   * {@inheritdoc}
   */
  public function initializeValue(&$value) {
    // Set a default value.
    if (empty($value)) {
      $value = $this->getDataDefinition()->getDefault();
    }

    $attribute_name = $this->getDataDefinition()->getAttributeTarget();

    // We're targetting a sibling element.
    $attribute = $this->getParent()
      ->get($attribute_name)
      ->getValue();

    // Provide a class.
    if ($this->getDataDefinition()->providesClass()) {
      $this->addClass($value, $attribute);
    }

    // Provide an attribute.
    if ($this->getDataDefinition()->providesAttribute()) {
      $this->setAttribute($value, $attribute);
    }

    $this->processBreakpoints($value, $attribute);
  }

  /**
   * Processes breakpoints.
   *
   * @param mixed &$value
   *   A component variable value.
   * @param \Drupal\Core\Template\Attribute $attribute
   *   An Attribute object.
   *
   * @todo Write this method.
   * @see https://www.drupal.org/project/component_schema/issues/3154376
   */
  protected function processBreakpoints(&$value, Attribute $attribute) {

  }

}
