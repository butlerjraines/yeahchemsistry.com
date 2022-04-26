<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\Template\Attribute;

/**
 * The template data type for components.
 */
class ComponentTemplate extends ComponentMapping implements TypedComponentVariableInterface {

  /**
   * {@inheritdoc}
   */
  public function initializeValue(&$value) {
    if (isset($value) && !is_array($value)) {
      throw new \InvalidArgumentException("Invalid values given. Values must be represented as an associative array.");
    }

    if (!is_null($value)) {
      assert(!empty($value['template']), 'Required template property is present');
      $template = $value['template'];
      $variables = $value['variables'] ?? [];

      /** @var \Drupal\Core\Template\TwigEnvironment $twig */
      $twig = $this
        ->getTypedDataManager()
        ->getTwigEnvironment();

      $value = $twig
        ->load($template)
        ->render($variables);
    }
  }

}
