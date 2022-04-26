<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Template\Attribute;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Interface for typed component data objects.
 */
interface TypedComponentVariableInterface extends TypedDataInterface {

  /**
   * The name of a property for the attribute data type.
   */
  const ATTRIBUTES_PROPERTY_NAME = 'attributes';

  /**
   * Initializes a value.
   *
   * @param mixed &$value
   *   A component variable value.
   */
  public function initializeValue(&$value);

}
