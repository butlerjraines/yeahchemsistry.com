<?php

namespace Drupal\component_schema\Component;

use Drupal\Component\Graph\Graph;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManager;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Template\TwigEnvironment;

/**
 * Extends TypedConfigManager for use with component template data.
 */
class TypedComponentManager extends TypedConfigManager implements TypedComponentManagerInterface {

  /**
   * The Twig environment.
   *
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected $twig;

  /**
   * The directed acyclic graph.
   *
   * @var array
   */
  protected $graph;

  /**
   * The component schema wrapper objects for components.
   *
   * @var \Drupal\Core\Config\Schema\Element[]
   */
  protected $schemas = [];

  /**
   * Creates a new typed configuration manager.
   *
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   The storage object to use for reading schema data
   * @param \Drupal\Core\Config\StorageInterface $schemaStorage
   *   The storage object to use for reading schema data
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to use for caching the definitions.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   */
  public function __construct(StorageInterface $configStorage, StorageInterface $schemaStorage, CacheBackendInterface $cache, ModuleHandlerInterface $module_handler, ClassResolverInterface $class_resolver) {
    parent::__construct($configStorage, $schemaStorage, $cache, $module_handler, $class_resolver);

    $this->setCacheBackend($cache, 'typed_component_definitions');
    $this->alterInfo('component_schema_info');
  }

  /**
   * {@inheritdoc}
   */
  public function getComponentDefinitions() {
    $definitions = $this->getDefinitions();
    // The definitions include base types. We only want the component types.
    $definitions = array_filter($definitions, function($definition) {
      return !empty($definition['type']) && ($definition['type'] === 'type_mapping');
    });

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponentTypeSchema($component_type) {
    if (!isset($this->schemas[$component_type])) {
      $this->schemas[$component_type] = $this
        ->createFromNameAndData($component_type, []);
    }
    return $this->schemas[$component_type];
  }

  /**
   * {@inheritdoc}
   */
  public function setTwigEnvironment(TwigEnvironment $twig) {
    $this->twig = $twig;
  }

  /**
   * {@inheritdoc}
   */
  public function getTwigEnvironment() {
    return $this->twig;
  }

  /**
   * {@inheritdoc}
   */
  public function handleTwigDebug() {
    // If in debug mode, we don't want caching.
    if ($this->twig->isDebug()) {
      $this->clearCachedDefinitions();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    // Parent calls $this->schemaStorage->reset(), a method that doesn't
    // exist on our storage.
    // Therefore, fork the ::clearCachedDefinitions() code from all ancestors
    // except the parent.
    $this->prototypes = [];
    if ($this->cacheBackend) {
      if ($this->cacheTags) {

        // Use the cache tags to clear the cache.
        Cache::invalidateTags($this->cacheTags);
      }
      else {
        $this->cacheBackend
          ->delete($this->cacheKey);
      }
    }
    $this->definitions = NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterDefinitions(&$definitions) {
    parent::alterDefinitions($definitions);
    // Apply inheritance to fully altered component definitions.
    $this->applyInheritance($definitions);
  }

  /**
   * Gets the dependency graph of all the components.
   *
   * @param mixed[] &$definitions
   *   An array of plugin definitions (empty array if no definitions were
   *   found). Keys are plugin IDs.
   */
  protected function applyInheritance(array &$definitions) {
    // Inherit the variable definitions.
    $graph = $this->getGraph($definitions);
    foreach ($graph as $destination_component_type => $data) {
      foreach ($data['edges'] as $source_component_type) {
        if (!empty($definitions[$source_component_type]['mapping'])) {
          // Allow variables to be designated as non-inheritable.
          // Unlike other properties, this one is not inherited from parent
          // component variable types because we're acting at the definition
          // stage, before the data objects are built.
          $inheritable_variables = array_filter($definitions[$source_component_type]['mapping'], function($variable) {
            return $variable['is_inheritable'] ?? TRUE;
          });
          // Record the inheritance source.
          foreach ($inheritable_variables as &$variable) {
            $variable['inherited_from'][] = $source_component_type;
          }
          $definitions[$destination_component_type]['mapping'] += $inheritable_variables;
        }
      }
    }
  }

  /**
   * Gets the dependency graph of all the components.
   *
   * @param mixed[] $definitions
   *   An array of plugin definitions (empty array if no definitions were
   *   found). Keys are plugin IDs.
   *
   * @return array
   *   The dependency graph of all the components.
   */
  protected function getGraph(array $definitions) {
    if (!isset($this->graph)) {
      // Generate a graph object in order to generate and sort a dependency graph.
      $graph = [];
      foreach ($definitions as $component_type => $definition) {
        if (!empty($definition['inherits_from'])) {
          if (!isset($graph[$component_type])) {
            $graph[$component_type] = [
              'edges' => [],
            ];
          }
          foreach ($definition['inherits_from'] as $extended_component_type) {
            $graph[$component_type]['edges'][$extended_component_type] = $extended_component_type;
          }
        }
      }
      $this->graph = (new Graph($graph))
        ->searchAndSort();
    }

    return $this->graph;
  }

}
