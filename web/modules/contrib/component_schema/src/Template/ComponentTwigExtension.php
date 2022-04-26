<?php

namespace Drupal\component_schema\Template;

use Drupal\component_schema\Component\TypedComponentManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Template\TwigEnvironment;

/**
 * A class providing component Twig extensions.
 */
class ComponentTwigExtension extends \Twig_Extension {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The typed component manager.
   *
   * @var \Drupal\component_schema\Component\TypedComponentManager
   */
  protected $typedComponentManager;

  /**
   * Constructs a new ComponentTwigExtension.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\component_schema\Component\TypedComponentManager $typed_component_manager
   *   The typed component manager.
   */
  public function __construct(RendererInterface $renderer, TypedComponentManager $typed_component_manager) {
    $this->renderer = $renderer;
    $this->typedComponentManager = $typed_component_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('prepend_class', [
        $this,
        'prependClass',
      ]),
      new \Twig_SimpleFunction('process_component', [
        $this,
        'processComponent',
      ], [
        'needs_context' => TRUE,
      ]),
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'component_schema';
  }

  /**
   * Adds a class to the beginning of an attribute's classes.
   *
   * This method is used to produce more easily read markup where there is a
   * primary class that is associated with the element it's being added to.
   *
   * @param \Drupal\Core\Template\Attribute $attributes
   *   An attributes object.
   * @param string $class
   *   A class to prepend.
   *
   * @return \Drupal\Core\Template\Attribute
   *   An attributes object that has the given attributes.
   */
  public function prependClass(Attribute $attributes, $class) {
    // Create a new attributes object and add our class.
    $class_attribute = new Attribute();
    $class_attribute->addClass($class);
    // Merge in the existing attributes object so its values (including class)
    // are subsequent to ours.
    $attributes = $class_attribute->merge($attributes);

    return $attributes;
  }

  /**
   * Processes the variables passed to a component.
   *
   * @param array $template_variables
   *   The variables available to a component template.
   *
   * @return array|bool|NULL
   *   If in debug mode, an array of errors or TRUE if no errors or FALSE if
   *   the component schema is missing.. Otherwise, NULL, since the work was
   *   done in the argument passed by reference.
   */
  public function processComponent(&$template_variables) {
    assert(!empty($template_variables['component_type']), 'The component_type variable is set');

    $component_type = $template_variables['component_type'];

    // Only pass relevant data for processing and validation.
    $definition = $this->typedComponentManager->getDefinition($component_type);
    $process_variables = array_intersect_key($template_variables, $definition['mapping']);

    // Process the variables.
    /** @var \Drupal\component_schema\Component\Schema\ComponentMapping $schema */
    $schema = $this->typedComponentManager->createFromNameAndData($component_type, $process_variables);

    $process_variables = $schema->getValue();

    // Attach libraries.
    if ($libraries = $schema->getDataDefinition()->getLibraries()) {
      foreach ($libraries as $library) {
        $this->attachLibrary($library);
      }
    }

    $return = NULL;

    // @todo Validate the schema data.
    // @see https://www.drupal.org/project/component_schema/issues/3154370

    $template_variables = array_merge($template_variables, $process_variables);

    return $return;
  }


  /**
   * Attaches an asset library to the template, and hence to the response.
   *
   * @param string $library
   *   An asset library.
   *
   * @see \Drupal\Core\Template\TwigExtension::attachLibrary()
   */
  protected function attachLibrary($library) {
    assert(is_string($library), 'Argument must be a string.');

    // Use Renderer::render() on a temporary render array to get additional
    // bubbleable metadata on the render stack.
    $template_attached = [
      '#attached' => [
        'library' => [
          $library,
        ],
      ],
    ];
    $this->renderer
      ->render($template_attached);
  }

}
