<?php

namespace Drupal\component_schema_ui_patterns\Element;

use Drupal\component_schema\ComponentSchema;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\TypedData\TraversableTypedDataInterface;

/**
 * Renders a pattern element.
 */
class ComponentPattern implements TrustedCallbackInterface {

  /**
   * The component schema wrapper object for the current component object.
   *
   * @var \Drupal\Core\Config\Schema\Element
   */
  protected $schema;

  /**
   * Expands variables into the format defined for a component.
   *
   * @param array $element
   *   Render array.
   *
   * @return array
   *   Render array.
   */
  public static function expandVariables(array $element) {
    if ($component = self::getComponent($element)) {
      foreach (array_keys($component->getElements()) as $key) {
        self::processElement($element, $component, $key);
      }
    }

    return $element;
  }

  /**
   * Gets a component.
   *
   * @param array $element
   *   Render array.
   *
   * @return \Drupal\Core\TypedData\TraversableTypedDataInterface|NULL
   *   Component schema instance or NULL if none.
   */
  public static function getComponent($element) {
    return ComponentSchema::getComponentSchema($element['#id']);
  }

  /**
   * Processees a component variable element.
   *
   * @param array $element
   *   Render array.
   * @param \Drupal\Core\TypedData\TraversableTypedDataInterface $component
   *   Component schema instance.
   * @param string $key
   *   The component variable key.
   */
  public static function processElement(array &$element, TraversableTypedDataInterface $component, $key) {
    // Construct a key using replacements.
    $variable_key = '#' . str_replace('.', '__', $key);

    $variable_keys = explode('__', $variable_key);
    $target =& $element;

    if ((count($variable_keys) > 1) && array_key_exists($variable_key, $element)) {
      while($parent_key = array_shift($variable_keys)) {
        // If we're at the last level, extract the value.
        if (empty($variable_keys)) {
          $target[$parent_key] = $element[$variable_key];
          unset($element[$variable_key]);
          break;
        }
        // If this level hasn't been initialized, do so.
        if (!isset($target[$parent_key])) {
          $target[$parent_key] = [];
        }
        // Target the parent that we're about to iterate into.
        $target =& $target[$parent_key];
      }
    }

    $component_element = $component->get($key);
    // Iterate through child elements.
    if ($component_element instanceof TraversableTypedDataInterface) {
      foreach (array_keys($component_element->getElements()) as $child_key) {
        self::processElement($element, $component, $key . '.' . $child_key);
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['expandVariables'];
  }

}
