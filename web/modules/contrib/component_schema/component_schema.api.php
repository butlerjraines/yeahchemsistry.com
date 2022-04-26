<?php

/**
 * @file
 * Describes hooks and plugins provided by the component_schema module.
 */

/**
 * Alter component typed data definitions.
 *
 * For example you can alter the typed data types representing each
 * component schema type to change default labels.
 *
 * If implementations of this hook add or remove component schema a
 * ConfigSchemaAlterException will be thrown.
 *
 * For adding new data types use component schema YAML files instead.
 *
 * @param $definitions
 *   Associative array of component type definitions keyed by schema type
 *   names. The elements are themselves array with information about the type.
 *
 * @see \Drupal\component_schema\Component\TypedComponentManager
 * @see \Drupal\Core\Config\TypedConfigManager
 * @see \Drupal\Core\Config\Schema\ConfigSchemaAlterException
 */
function hook_component_schema_info_alter(&$definitions) {
  $definitions['boolean']['label'] = 'Yes or no';
}
