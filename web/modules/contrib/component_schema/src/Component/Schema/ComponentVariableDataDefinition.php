<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\TypedData\DataDefinition;

/**
 * A typed data definition class for defining data based on defined data types.
 */
class ComponentVariableDataDefinition extends DataDefinition implements ComponentVariableDataDefinitionInterface {

  use ComponentVariableDataDefinitionTrait;

}
