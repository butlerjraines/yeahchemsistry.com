<?php

namespace Drupal\component_schema;

use Drupal\component_schema\Component\Schema\TypedComponentVariableInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Provides the available components based on yml files.
 *
 * To define components you can use a $name.component_schema.yml file. This
 * file defines machine names, human-readable names, and optionally
 * descriptions for each component type. The machine names are the canonical
 * way to refer to components.
 */
class ComponentProcessor {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The YAML discovery classes to find all .component_schema.yml files.
   *
   * @var \Drupal\Core\Discovery\YamlDiscovery[]
   */
  protected $yamlDiscovery = [];

  /**
   * Constructs a new ComponentProcessor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, TranslationInterface $string_translation) {
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Gets the YAML discovery.
   *
   * @param string $provider_type
   *   The type of extension.
   *
   * @return \Drupal\Core\Discovery\YamlDiscovery
   *   The YAML discovery.
   */
  protected function getYamlDiscovery($provider_type) {
    if (!isset($this->yamlDiscovery[$provider_type])) {
      switch ($provider_type) {
        case 'module':
          $directories = $this->moduleHandler->getModuleDirectories();
          break;
        case 'theme':
          $directories = $this->themeHandler->getThemeDirectories();
          break;
      }
      $this->yamlDiscovery[$provider_type] = new YamlDiscovery('component_schema', $directories);
    }

    return $this->yamlDiscovery[$provider_type];
  }

  /**
   * {@inheritdoc}
   */
  public function getComponents() {
    $components = $this->buildComponentsYaml();
    uasort($components, function ($a, $b) {
      return strnatcasecmp($a['label'], $b['label']);
    });
    return $components;
  }

  /**
   * Builds all components provided by .components.yml files.
   *
   * @return array[]
   *   Each return component is an array with the following keys:
   *   - label: The label (title) of the component.
   *   - description: The description of the component, defaults to NULL.
   *   - group: The group of the component.
   *   - provider: The name of the module or theme that provides the
   *     component.
   *   - provider_type: The type (module or theme) of the extension that
   *     provides the component.
   *   - mapping: An array of variables.
   *   - settings: An array of settings.
   */
  protected function buildComponentsYaml() {
    $all_components = [];

    foreach (['module', 'theme'] as $provider_type) {
      foreach ($this->getYamlDiscovery($provider_type)->findAll() as $provider => $data) {
        $components = $data['components'];
        switch ($provider_type) {
          case 'module':
            $extension = $this->moduleHandler->getModule($provider);
            break;
          case 'theme':
            $extension = $this->themeHandler->getTheme($provider);
            break;
        }
        foreach ($components as &$component) {
          assert(
            isset($component['label']) &&
            isset($component['description']) &&
            isset($component['group']) &&
            isset($component['variables_yml'])
          , 'Component has required keys: label, description, group, and variables_yml.');
          $component['type'] = 'type_mapping';
          $component['provider'] = $provider;
          $component['provider_type'] = $provider_type;
          // Derive an AllowedValues constraint from the options.
          if (!empty($component['options'])) {
            assert(is_array($variable['options']), 'If not empty, the options key is set to an array.');
            $component['constraints']['AllowedValues'] = array_keys($component['options']);
          }
          $file = $extension->getPath() . '/' . $component['variables_yml'];
          $variables = Yaml::decode(file_get_contents($file)) ?: [];
          $this->processVariables($variables);
          $component['mapping'] = $variables;
          // Merge global settings with any specific to the component.
          $component['settings'] = NestedArray::mergeDeep($data['settings'], $component['settings'] ?? []);
        }

        $all_components += $components;
      }
    }
    foreach ($all_components as $key => &$component) {
      if (!empty($component['mapping'])) {
        $this->processComponentTypeVariables($component['mapping'], $all_components);
      }
    }

    return $all_components;
  }

  /**
   * Processes variables.
   *
   * Recurses through mappings and sequences.
   *
   * @param array &$variables
   *   An array of variables.
   */
  protected function processVariables(&$variables) {
    foreach ($variables as $name => &$variable) {
      $this->processVariable($variable, $name);
    }
  }

  /**
   * Processes a variable.
   *
   * Translates the variable's label property and recurses through mappings and
   * sequences.
   *
   * @param array &$variable
   *   A single variable.
   * @param string $name
   *   The machine name of the variable.
   */
  protected function processVariable(&$variable, $name = NULL) {
    // The component_type key is processed in a subsequent pass.
    if (!empty($variable['component_type'])) {
      return;
    }

    // Validate
    assert(isset($variable['label']) && isset($variable['type']), 'Variable has required keys: label, type.');
    if (!empty($variable['provides_attribute'])) {
      assert(isset($variable['attribute_name']), 'A value is set for attribute_name when provides_attribute is true');
    }

    switch ($variable['type']) {
      // Validate.
      case 'boolean_attribute_provider':
        if (!empty($variable['provides_class'])) {
          assert(isset($variable['value']), 'A value is set for boolean_attribute_provider variables when provides_class is true');
        }
        if (!empty($variable['provides_attribute'])) {
          assert(isset($variable['value']), 'A value is set for boolean_attribute_provider variables when provides_attribute is true');
        }
        break;
      // Recurse through any child variables.
      case 'mapping':
        assert(isset($variable['mapping']) && is_array($variable['mapping']), 'Value for mapping is an array.');
        $this->processVariables($variable['mapping']);
        break;
      case 'sequence':
        assert(isset($variable['sequence']), 'Variable of type sequence has a sequence key');
        assert(is_array($variable['sequence']), 'Value for sequence is an array.');
        $this->processVariable($variable['sequence']);
        break;
    }
  }

  /**
   * Processes component type variables.
   *
   * @param array &$variables
   *   An array of variables.
   * @param array $components
   *   An array of components.
   */
  protected function processComponentTypeVariables(&$variables, $components) {
    foreach ($variables as $name => &$variable) {
      $this->processComponentTypeVariable($variable, $components);
    }
  }

  /**
   * Processes a component type variable.
   *
   * @param array &$variable
   *   A single variable.
   * @param array $components
   *   An array of components.
   */
  protected function processComponentTypeVariable(&$variable, $components) {
    if (!empty($variable['component_type'])) {
      $label = $components[$variable['component_type']]['label'] . ' component';
      $variable += [
        'label' => $label,
      ];
      // If the component type provides variables, embed them.
      if (!empty($components[$variable['component_type']]['mapping'])) {
        // Allow overriding of the component.
        $variable = NestedArray::mergeDeep($components[$variable['component_type']], $variable);
      }

      return;
    }

    // Recurse through any child variables.
    switch ($variable['type']) {
      case 'mapping':
        $this->processComponentTypeVariables($variable['mapping'], $components);
        break;
      case 'sequence':
        $this->processComponentTypeVariable($variable['sequence'], $components);
        break;
    }
  }
}
