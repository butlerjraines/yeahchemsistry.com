<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Template\Attribute;

/**
 * The component data type for components.
 */
class ComponentComponent extends ComponentMapping implements TypedComponentVariableInterface {

  use ComponentVariableTrait;

  /**
   * {@inheritdoc}
   */
  public function initializeValue(&$value) {
    if (isset($value) && !is_array($value)) {
      throw new \InvalidArgumentException("Invalid values given. Values must be represented as an associative array.");
    }

    if (!is_null($value)) {
      assert(!empty($value['component_type']), 'Required component_type property is present');
      $component_type = $value['component_type'];
      $variables = $value['variables'] ?? [];

      // Get the definition.
      // Only pass relevant data for processing and validation.
      $definition = $this->typedDataManager->getDefinition($component_type);
      $process_variables = array_intersect_key($variables, $definition['mapping']);

      // Process the variables.
      $schema = $this->typedDataManager->createFromNameAndData($component_type, $process_variables);

      $process_variables = $schema->getValue();

      /** @var \Drupal\Core\Template\TwigEnvironment $twig */
      $twig = $this
        ->getTypedDataManager()
        ->getTwigEnvironment();

      $value = $twig
        ->load($definition['component_template'])
        ->render($process_variables);
    }
  }

}
