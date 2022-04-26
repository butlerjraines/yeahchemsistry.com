<?php

namespace Drupal\component_schema_ui_patterns\Plugin\Deriver;

use Drupal\component_schema\Component\TypedComponentManager;
use Drupal\component_schema\Component\Schema\ComponentAttribute;
use Drupal\component_schema\Component\Schema\ComponentMapping;
use Drupal\component_schema\Component\Schema\ComponentVariableAttributeProviderDataDefinition;
use Drupal\component_schema\Component\Schema\ComponentVariableDataDefinitionInterface;
use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\Config\Schema\Sequence;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\TypedData\Plugin\DataType\BooleanData;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\ui_patterns\Plugin\Deriver\AbstractPatternsDeriver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ComponentSchemaDeriver.
 */
class ComponentSchemaDeriver extends AbstractPatternsDeriver {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The typed component manager.
   *
   * @var \Drupal\component_schema\Component\TypedComponentManager
   */
  protected $typedComponentManager;

  /**
   * The component schema wrapper object for the current component object.
   *
   * @var \Drupal\Core\Config\Schema\Element
   */
  protected $schema;

  /**
   * ComponentSchemaDeriver constructor.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   Typed data manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messenger service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\component_schema\Component\TypedComponentManager $typed_component_manager
   *   The typed component manager.
   */
  public function __construct($base_plugin_id, TypedDataManager $typed_data_manager, MessengerInterface $messenger, ModuleHandlerInterface $module_handler, TypedComponentManager $typed_component_manager) {
    parent::__construct($base_plugin_id, $typed_data_manager, $messenger);
    $this->moduleHandler = $module_handler;
    $this->typedComponentManager = $typed_component_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('typed_data_manager'),
      $container->get('messenger'),
      $container->get('module_handler'),
      $container->get('component_schema.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPatterns() {
    $patterns = [];

    $component_type_definitions = $this
      ->typedComponentManager
      ->getComponentDefinitions();

    foreach (array_keys($component_type_definitions) as $component_type) {
      if ($pattern_array = $this->getPatternDefinitionArray($component_type)) {
        if ($pattern = $this->getPatternDefinition($pattern_array)) {
          $patterns[] = $pattern;
        }
      }
    }
    return $patterns;
  }

  /**
   * Returns a pattern definition array based on the given component definition.
   *
   * @param string $component_type
   *   The component type machine name.
   *
   * @return array|FALSE
   *   A pattern definition array or FALSE if the component type doesn't support
   *   an admin UI.
   */
  protected function getPatternDefinitionArray($component_type) {
    $this->schema = $this->typedComponentManager
      ->getComponentTypeSchema($component_type);

    $component_definition = $this->schema->getDataDefinition();
    if ($component_definition->takesUi()) {
      $pattern_definition = [
        // Use the component type machine name as the pattern key.
        'id' => $component_type,
      ];

      // base_path and file_name are required properties of a pattern definition.
      // In our case they're not particularly relevant. The following call uses`
      // the template.
      // @todo If continuing to use template file here, parse the relative path.
      // @todo Consider switching to using the variables_yml file.
      $template_path = $this->schema
        ->getTypedDataManager()
        ->getTwigEnvironment()
        ->load($component_definition->getComponentTemplate())
        ->getSourceContext()
        ->getPath();
      $pattern_definition['base path'] = dirname($template_path);
      $pattern_definition['file name'] = basename($template_path);

      // Copy over equivalent properties.
      // Keys are source (component schema) methods, values are destination
      // (pattern) keys.
      $mapping = [
        'getLabel' => 'label',
        'getDescription' => 'description',
        'getLibraries' => 'libraries',
        'getProvider' => 'provider',
        'getComponentTemplate' => 'use',
      ];
      foreach ($mapping as $source_method => $destination_key) {
        if ($source_value = $component_definition->{$source_method}()) {
          $pattern_definition[$destination_key] = $source_value;
        }
      }

      // Process variables, which will be parsed as pattern fields or settings.
      $elements = $this->schema->getElements();
      foreach (array_keys($elements) as $key) {
        $this->processVariableItem($pattern_definition, $key);
      }

      return $pattern_definition;
    }

    return FALSE;
  }

  /**
   * Parses fields and settings from component variables.
   *
   * In this method we derive a set of fields and/or settings from the
   * component's variables. To do so, we need to convert our nested structure
   * into flat arrays.
   *
   * @param array[] &$pattern_definition
   *   The pattern definition array.
   * @param string $key
   *   The component variable key.
   * @param string[] $ancestor_keys
   *   The keys of ancestor elements. Used internally.
   */
  protected function processVariableItem(array &$pattern_definition, $key, $ancestor_keys = []) {
    $element = $this->schema
      ->get($key);

    // @todo Support Sequence elements.
    if ($element instanceof Sequence) {
      return;
    }

    $definition = $element->getDataDefinition();

    // Iterate through a mapping.
    if ($element instanceof Mapping) {
      $ancestor_keys[] = $key;
      $child_elements = $element->getElements();
      foreach (array_keys($child_elements) as $child_key) {
        $this->processVariableItem($pattern_definition, $key . '.' . $child_key, $ancestor_keys);
      }
      return;
    }

    // Return early if the element doesn't take a UI.
    // @todo: move this test before the handling of sequences and mappings.
    if (!$definition->takesUi()) {
      return;
    }

    // Construct a key using replacements.
    $element_key = $this->getPropertyKey($key);

    $item = [];

    // Copy over equivalent properties that are used by both fields and
    // settings.
    // Keys are source (component schema) methods, values are destination
    // (field or setting) keys.
    $mapping = [
      'getLabel' => 'label',
      'getDescription' => 'description',
      'getPreview' => 'preview',
    ];
    foreach ($mapping as $source_method => $destination_key) {
      if ($source_value = $definition->{$source_method}()) {
        $item[$destination_key] = $source_value;
      }
    }

    $collection = NULL;
    $root = $element->getRoot();

    switch ($definition->getUiType()) {
      case ComponentVariableDataDefinitionInterface::UI_TYPE_FIELD:
        $collection = 'fields';
        $item['type'] = 'string';

        foreach ($ancestor_keys as $ancestor_key) {
          // Prepend child variable labels with the parent label.
          $item['label'] = $root->get($ancestor_key)->getDataDefinition()->getLabel() . ' » ' . $item['label'];
        }

        break;
      case ComponentVariableDataDefinitionInterface::UI_TYPE_SETTING:
        $collection = 'settings';
        if ($element instanceof BooleanData) {
          $item['type'] = 'boolean';
        }
        elseif ($definition instanceof ComponentVariableAttributeProviderDataDefinition) {
          if ($options = $definition->getOptions()) {
            if ((strpos($key, 'color') !== FALSE) && ($ui_classes = $definition->getUiClasses()) && $this->moduleHandler->moduleExists('colorwidget')) {
              $item['type'] = 'colorwidget';
              foreach ($options as $option_key => $option_value) {
                if (isset($ui_classes[$option_key])) {
                  $item['options'][$option_key] = $option_value . '/' . $ui_classes[$option_key] . '/css_class';
                }
              }
            }
            else {
              $item['type'] = 'select';
              $item['options'] = $options;
            }

          }
          else {
            $item['type'] = 'textfield';
          }
        }
        elseif ($element instanceof ComponentAttribute) {
          $item['type'] = 'attributes';
        }

        // Add wrapping groups for inherited settings. We add theem here rather
        // than when we're handling mapping or sequence elements because at that
        // earlier stage we don't yet know if there will be one or more
        // contained settings.
        $component_parent = NULL;
        if ($component_ancestors = $definition->getInheritedFrom()) {
          foreach (array_reverse($component_ancestors) as $component_ancestor) {
            $ancestor_type = $this
              ->typedComponentManager
              ->getComponentTypeSchema($component_ancestor);
            // If not already present, add a group for each ancestor.
            $ancestor_key = $component_ancestor . '_component';
            if (!isset($pattern_definition[$collection][$ancestor_key])) {
              $ancestor_definition = $ancestor_type->getDataDefinition();
              $pattern_definition[$collection][$ancestor_key] = [
                'type' => 'group',
                'label' => $ancestor_definition->getLabel(),
                'description' => $ancestor_definition->getDescription(),
                'group_type' => 'details',
              ];
              if (!is_null($component_parent)) {
                $pattern_definition[$collection][$ancestor_key]['group'] = $component_parent;
              }
            }
            $component_parent = $ancestor_key;
          }
          // Add the last and therefore innermost group.
          $item['group'] = $component_parent;
          unset($component_parent);
        }
        // Nest in groups if there are ancestors.
        elseif (!empty($ancestor_keys)) {
          $element_grandparent_key = NULL;
          foreach ($ancestor_keys as $ancestor_key) {
            $element_ancestor_key = $this->getPropertyKey($ancestor_key);
            if (!isset($pattern_definition[$collection][$element_ancestor_key])) {
              $pattern_definition[$collection][$element_ancestor_key] = [
                'type' => 'group',
                'label' => $root->get($ancestor_key)->getDataDefinition()->getLabel(),
                'group_type' => 'details',
              ];
              // If there was a grandparent element, assign this group to its
              // group.
              if (!is_null($element_grandparent_key)) {
                $pattern_definition[$collection][$element_ancestor_key]['group'] = $element_grandparent_key;
              }
            }
            $element_grandparent_key = $element_ancestor_key;
          }

          $item['group'] = $element_ancestor_key;
        }
        break;
    }
    if ($collection) {
      $pattern_definition[$collection][$element_key] = $item;
    }
  }

  /**
   * Gets a
   *
   * @param string $key
   *   A key potentially containing period characters.
   *
   * @return string
   *   A key suitable to be used in a render array.
   */
  protected function getPropertyKey($key) {
    return str_replace('.', '__', $key);
  }

}
