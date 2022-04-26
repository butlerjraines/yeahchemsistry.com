<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Template\Attribute;
use Drupal\Core\TypedData\Plugin\DataType\StringData;

/**
 * The string component data type.
 *
 * The plain value of a string is a regular PHP string. For setting the value
 * any PHP variable that casts to a string may be passed.
 */
class ComponentStringData extends StringData implements TypedComponentVariableInterface {

  use ComponentVariableTrait;
  use ComponentVariablePrimitiveElementTrait;

}
