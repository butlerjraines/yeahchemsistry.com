<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\TypedData\MapDataDefinition;

/**
 * A component typed data definition class for defining maps.
 */
class ComponentVariableMapDataDefinition extends MapDataDefinition implements ComponentVariableDataDefinitionInterface {

  use ComponentVariableDataDefinitionTrait;

}
