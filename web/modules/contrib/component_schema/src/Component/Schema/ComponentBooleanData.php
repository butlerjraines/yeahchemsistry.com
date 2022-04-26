<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\TypedData\Plugin\DataType\BooleanData;

/**
 * The boolean data type with component methods.
 *
 * The plain value of a boolean is a regular PHP boolean. For setting the value
 * any PHP variable that casts to a boolean may be passed.
 */
class ComponentBooleanData extends BooleanData implements TypedComponentVariableInterface {

  use ComponentVariableTrait;
  use ComponentVariablePrimitiveElementTrait;

}
