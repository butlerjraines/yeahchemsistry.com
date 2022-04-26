<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Config\Schema\SequenceDataDefinition;

/**
 * A component typed data definition class for defining maps.
 */
class ComponentVariableSequenceDataDefinition extends SequenceDataDefinition implements ComponentVariableDataDefinitionInterface {

  use ComponentVariableDataDefinitionTrait;

}
