<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Config\Schema\Element;
use Drupal\Core\Template\Attribute;
use Drupal\Core\TypedData\ComplexDataInterface;

/**
 * An Attribute component element.
 */
class ComponentAttribute extends Element implements TypedComponentVariableInterface {

  use ComponentVariableTrait;

  /**
   * Overrides \Drupal\Core\TypedData\TypedData::applyDefaultValue().
   */
  public function applyDefaultValue($notify = TRUE) {
    // Apply a default if specified in the definition.
    $this
      ->setValue(new Attribute(), $notify);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function initializeValue(&$value) {
    // Set a default value.
    if (is_null($value)) {
      $value = new Attribute();
    }
    // Attributes data may be passed in array form rather than Attribute.
    // Standardize on Attribute.
    elseif (is_array($value)) {
      $value = new Attribute($value);
    }

    $this->setValue($value);
  }

}
