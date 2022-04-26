<?php

namespace Drupal\component_schema_ui_patterns\Plugin\UiPatterns\Pattern;

use Drupal\ui_patterns\Plugin\PatternBase;

/**
 * The UI Pattern plugin.
 *
 * @UiPattern(
 *   id = "component_schema",
 *   label = @Translation("Component Schema"),
 *   description = @Translation("Pattern defined from a component schema."),
 *   deriver = "\Drupal\component_schema_ui_patterns\Plugin\Deriver\ComponentSchemaDeriver"
 * )
 */
class ComponentSchemaPattern extends PatternBase {

}
