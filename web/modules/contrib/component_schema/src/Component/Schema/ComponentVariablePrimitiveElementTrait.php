<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Template\Attribute;

trait ComponentVariablePrimitiveElementTrait {

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    // Apply a default if specified in the definition.
    $this
      ->setValue($this->getDataDefinition()->getDefault(), $notify);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function initializeValue(&$value) {
    // Set a default value.
    if (empty($value)) {
      $value = $this->getDataDefinition()->getDefault();
    }
  }

}
