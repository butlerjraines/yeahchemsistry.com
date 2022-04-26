<?php

namespace Drupal\component_schema;

/**
 * Component Schema factory class.
 *
 * @package Drupal\component_schema
 */
class ComponentSchema {

  /**
   * Gets component processor instance.
   *
   * @return \Drupal\component_schema\ComponentProcessor
   *   Component Schema processor instance.
   */
  public static function getProcessor() {
    return \Drupal::service('component_schema.processor');
  }

  /**
   * Gets typed component manager instance.
   *
   * @return \Drupal\component_schema\Component\TypedComponentManager
   *   Component Schema processor instance.
   */
  public static function getTypedManager() {
    return \Drupal::service('component_schema.typed');
  }

  /**
   * Get component schema.
   *
   * @param string $component_type
   *   Component type ID.
   * @param array $variables
   *   (optional) An array of variable data.
   *
   * @return \Drupal\Core\TypedData\TraversableTypedDataInterface|NULL
   *   Component schema instance or NULL if none.
   */
  public static function getComponentSchema($component_type, $variables = []) {
    $typed_component_manager = \Drupal::service('component_schema.typed');
    $component_definitions = $typed_component_manager->getComponentDefinitions();
    if (array_key_exists($component_type, $component_definitions)) {
      return $typed_component_manager->createFromNameAndData($component_type, $variables);
    }
    return NULL;
  }

}
