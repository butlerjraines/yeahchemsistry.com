<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\Template\Attribute;

/**
 * The mapping data type for components.
 */
class ComponentMapping extends Mapping implements TypedComponentVariableInterface {

  use ComponentVariableTrait;
  use ComponentVariableArrayElementTrait;

  /**
   * {@inheritdoc}
   */
  public function getProperties($include_computed = FALSE) {
    $properties = [];
    foreach ($this->getAllKeys() as $name) {
      $properties[$name] = $this->get($name);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  protected function getAllKeys() {
    return array_keys($this->definition['mapping'] ?? []);
  }

  /**
   * Overrides \Drupal\Core\TypedData\TypedData::setValue().
   *
   * @param array|null $value
   *   An array of property values.
   */
  public function setValue($value, $notify = TRUE) {
    if (isset($value) && !is_array($value)) {
      throw new \InvalidArgumentException("Invalid values given. Values must be represented as an associative array.");
    }

    static::initializeValue($value);

    parent::setValue($value, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public function initializeValue(&$value) {
    // Don't iterate the child elements if a null value is passed in.
    // Otherwise we might end up with non-empty properties, making it harder in
    // templates to skip elements that have no renderable content.
    if (!is_null($value)) {
      $this->doInitializeValue($value);
    }
  }

}
