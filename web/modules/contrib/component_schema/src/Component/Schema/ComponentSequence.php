<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Config\Schema\Sequence;
use Drupal\Core\Template\Attribute;

/**
 * The mapping data type for components.
 */
class ComponentSequence extends Sequence implements TypedComponentVariableInterface {

  use ComponentVariableTrait;
  use ComponentVariableArrayElementTrait;

  /**
   * {@inheritdoc}
   */
  public function initializeValue(&$value) {
    // We need to set the value to be able to iterate through child elements.
    $this->value = $value;

    $this->doInitializeValue($value);

    $this->value = $value;
  }

}
