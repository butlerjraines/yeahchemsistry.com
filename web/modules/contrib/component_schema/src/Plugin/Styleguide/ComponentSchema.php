<?php

namespace Drupal\component_schema\Plugin\Styleguide;

use Drupal\component_schema\Component\Schema\ComponentAttribute;
use Drupal\component_schema\Component\Schema\ComponentDataDefinitionInterface;
use Drupal\component_schema\Component\TypedComponentManager;
use Drupal\styleguide\Plugin\StyleguidePluginBase;
use Drupal\Core\Config\Schema\Ignore;
use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\Config\Schema\Sequence;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\TypedData\Plugin\DataType\BooleanData;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\TraversableTypedDataInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Component Schema items implementation.
 *
 * @Plugin(
 *   id = "component_schema",
 *   label = @Translation("Component Schema elements")
 * )
 */
class ComponentSchema extends StyleguidePluginBase {

  /**
   * The module handler service.
   *
   * @var ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * The theme extension list.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected $themeExtensionList;

  /**
   * The info parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

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
   * Constructs a new ComponentSchema.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   The module extension list
   * @param \Drupal\Core\Extension\ThemeExtensionList $extension_list_theme
   *   The theme extension list
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser.
   * @param \Drupal\component_schema\Component\TypedComponentManager $typed_component_manager
   *   The typed component manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, ModuleExtensionList $extension_list_module, ThemeExtensionList $extension_list_theme, InfoParserInterface $info_parser, TypedComponentManager $typed_component_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->moduleHandler = $module_handler;
    $this->moduleExtensionList = $extension_list_module;
    $this->themeExtensionList = $extension_list_theme;
    $this->infoParser = $info_parser;
    $this->typedComponentManager = $typed_component_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('extension.list.module'),
      $container->get('extension.list.theme'),
      $container->get('info_parser'),
      $container->get('component_schema.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function items() {
    $items = [];

    foreach (array_keys($this->typedComponentManager->getComponentDefinitions()) as $component_type) {
      $this->schema = $this->typedComponentManager
        ->getComponentTypeSchema($component_type);
      $component_definition = $this->schema->getDataDefinition();
      // Only add to the styleguide if there is a template.
      if ($styleguide_template = $component_definition->getStyleguideTemplate()) {
        switch ($component_definition->getProviderType()) {
          case 'module':
            $extension = $this->moduleExtensionList->get($component_definition->getProvider());
            break;
          case 'theme':
            $extension = $this->themeExtensionList->get($component_definition->getProvider());
            break;
        }
        $extension_info = $this->getExtensionInfo($extension);
        $description = [
          '#type' => 'details',
          '#title' => $this->t('Component details'),
          '#open' => FALSE,
        ];
        $settings = $component_definition->getSettings();
        if (isset($settings['content_class'])) {
          $description['#attributes'] = [
            'class' => [$settings['content_class']],
          ];
        }
        $description_items = [];
        $description_items['description'] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t($component_definition->getDescription()),
        ];
        $description_items['template'] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $this->t('Component template: <code>@component_template</code>', ['@component_template' => $component_definition->getComponentTemplate()]),
        ];

        $extension_args = [
          '@type' => $this->t($component_definition->getProviderType()),
          '@title' => $extension_info['name'],
          '@name' => $component_definition->getProvider(),
        ];
        $description_items['provider'] = [
          '#type' => 'html_tag',
          '#tag' => 'p',
        ];
        // If data from drupal.org is present, assume this is a contributed
        // extension and link to it on drupal.org.
        if (isset($extension_info['project']) && isset($extension_info['version']) && isset($extension_info['datestamp'])) {
          $extension_args[':project'] = 'https://www.drupal.org/project/' . $extension_info['project'];
          $description_items['provider']['#value'] = $this->t('Provided by the @type <strong><a href=":project">@title</a></strong> (<code>@name</code>)', $extension_args);
        }
        // Otherwise, just give the extension name.
        else {
          $description_items['provider']['#value'] = $this->t('Provided by the @type <strong>@title</strong> (<code>@name</code>)', $extension_args);
        }

        if ($documentation_url = $component_definition->getDocumentationUrl()) {
          $description_items['documentation'] = [
            '#type' => 'link',
            '#title' => $this->t('View component documentation'),
            '#url' => Url::fromUri($documentation_url),
            '#attributes' => ['target' => '_blank'],
          ];
        }
        $description['basics'] = [
          '#theme' => 'item_list',
          '#items' => $description_items,
          '#title' => $this->t('Basics'),
        ];
        $variable_items = [];
        $elements = $this->schema->getElements();
        foreach (array_keys($elements) as $key) {
          $variable_items[] = $this->getVariableItem($key);
        }
        $description['variables'] = [
          '#title' => $this->t('Variables'),
          '#theme' => 'item_list',
          '#items' => $variable_items,
        ];

        // Prefix the key to minimize collisions with items provided by other
        // modules.
        $items['component_' . $component_type] = [
          'title' => $this->t($component_definition->getLabel()),
          'description' => $description,
          'content' => [
            '#type' => 'inline_template',
            '#template' => '{% include(styleguide_template) %}',
            '#context' => [
              'styleguide_template' => $styleguide_template,
              'base_path' => base_path(),
              'extension_path' => $extension->getPath(),
            ],
          ],
          'group' => $this->t($component_definition->getGroup()),
        ];
      }
    }

    return $items;
  }

  /**
   * Gets a nested #item_list element for variables.
   *
   * @param string $key
   *   The component variable key.
   *
   * @return array
   *   A render array of variables suitable for passing to theme(item_list).
   */
  protected function getVariableItem($key) {
    $element = $this->schema
      ->get($key);
    $definition = $element->getDataDefinition();
    $data_type = $definition->getDataType();

    $item = [];

    // Convert types as needed. We're repurposing types from Drupal core's
    // configuration schema. Substitute type names suitable to Twig
    // templates.
    // @see https://www.drupal.org/docs/8/api/configuration-api/configuration-schemametadata

    if ($element instanceof Mapping) {
      $type = 'hash';
    }
    elseif ($element instanceof Sequence) {
      $type = 'array';
    }
    elseif ($element instanceof StringData) {
      $type = 'string';
    }
    elseif ($element instanceof BooleanData) {
      $type = 'bool';
    }
    elseif ($element instanceof ComponentAttribute) {
      $type = '\Drupal\Core\Template\Attribute';
    }
    elseif ($element instanceof Ignore) {
      $type = 'mixed';
    }
    else {
      $type = $data_type;
    }

    // The variable name is the last segment of the key.
    $keys = explode('.', $key);
    $name = array_pop($keys);
    $options = [
      '@name' => $name,
      '@type' => $type,
      '@label' => $this->t($definition->getLabel() ?? ''),
    ];

    $is_component = ($definition instanceof ComponentDataDefinitionInterface);
    if ($is_component) {
      $item['description'] = [
        '#type' => 'markup',
        '#markup' => $this->t('<code>@name</code> (<code>@type</code>): @label', $options),
      ];
    }
    else {
      if (!is_null($definition->getDefault())) {
        $options['%default'] = $definition->getDefault();
      }
      // Add a description of the variable.
      $item['description'] = [
        '#type' => 'markup',
        '#markup' => $this->t('<code>@name</code> (<code>@type</code>): @label', $options),
      ];
      // Vary the output according to whether the variable is optional
      // (nullable) and whether there's a default value.
      if ($definition->isNullable()) {
        if (is_null($definition->getDefault())) {
          $item['description']['#markup'] = $this->t('<code>@name</code> (<code>@type</code>): @label', $options);
        }
        else {
          $item['description']['#markup'] = $this->t('<code>@name</code> (<code>@type</code>, defaults to %default): @label', $options);
        }
      }
      else {
        if (is_null($definition->getDefault())) {
          $item['description']['#markup'] = $this->t('<code>@name</code> (<code>@type</code>, optional): @label', $options);
        }
        else {
          $item['description']['#markup'] = $this->t('<code>@name</code> (<code>@type</code>, optional, defaults to %default): @label', $options);
        }
      }
    }

    // Link to the styleguide documentation.
    if ($is_component) {
      $item['link'] = [
        '#prefix' => ' ',
        '#type' => 'markup',
        '#markup' => $this->t('<a href="#component_@type">View component</a>', ['@type' => $definition->getComponentType()]),
      ];
    }

    // Add an optional documentation URL. This property is a custom addition
    // to the schema format used in Drupal core.
    elseif (!empty($definition->getDocumentationUrl())) {
      $item['link'] = [
        '#prefix' => ' » ',
        '#type' => 'link',
        '#title' => $this->t('View variable documentation'),
        '#url' => Url::fromUri($definition->getDocumentationUrl()),
        '#attributes' => ['target' => '_blank'],
      ];
    }

    // Iterate through child elements.
    if ($element instanceof TraversableTypedDataInterface) {

      if ($element instanceof Sequence) {
        $item['sequence'] = [
          '#prefix' => ' » ',
          '#type' => 'markup',
          '#markup' => $this->t('An array of items'),
        ];
      }

      $child_items = [];
      $child_elements = $element->getElements();
      foreach (array_keys($child_elements) as $child_key) {
        $child_items[] = $this->getVariableItem($key . '.' . $child_key);
      }
      $item['children'] = [
        '#theme' => 'item_list',
        '#items' => $child_items,
      ];
    }

    return $item;
  }

  /**
   * Gets the info for a given module or theme extension.
   *
   * @param \Drupal\Core\Extension\Extension $extension
   *   A module or theme extension.
   *
   * @return array
   *   An array of information parsed from the extension's .info.yml file.
   */
  protected function getExtensionInfo(Extension $extension) {
    static $info = [];

    $type = $extension->getType();
    $name = $extension->getName();
    if (!isset($info[$type])) {
      $info[$type] = [];
    }
    if (!isset($info[$type][$name])) {
      $extension_info = $this->infoParser->parse($extension->getPathname());
      // Invoke hook_system_info_alter() to give installed modules a chance to
      // modify the data in the .info.yml files if necessary.
      $this->moduleHandler->alter('system_info', $extension_info, $extension, $type);
      $info[$type][$name] = $extension_info;
    }

    return $info[$type][$name];
  }

}
