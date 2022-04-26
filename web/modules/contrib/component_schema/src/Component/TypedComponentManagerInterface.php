<?php

namespace Drupal\component_schema\Component;

use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Template\TwigEnvironment;

/**
 * Defines an interface for managing component schema type plugins.
 *
 * @see \Drupal\Core\Config\TypedConfigManager
 * @see \Drupal\component_schema\Component\TypedComponentManager
 */
interface TypedComponentManagerInterface extends TypedConfigManagerInterface {

  /**
   * Gets the definition of all component plugins for this type.
   *
   * @return mixed[]
   *   An array of plugin definitions (empty array if no definitions were
   *   found). Keys are plugin IDs.
   */
  public function getComponentDefinitions();

  /**
   * Gets the built object of a component type.
   *
   * @param string $component_type
   *   The component type.
   *
   * @return \Drupal\component_schema\Component\Schema\ComponentMapping
   *   The schema object.
   */
  public function getComponentTypeSchema($component_type);

  /**
   * Sets the Twig environment.
   *
   * @param \Drupal\Core\Template\TwigEnvironment $twig
   *   The Twig environment.
   */
  public function setTwigEnvironment(TwigEnvironment $twig);

  /**
   * Gets the Twig environment.
   *
   * @return \Drupal\Core\Template\TwigEnvironment
   *   The Twig environment.
   */
  public function getTwigEnvironment();

  /**
   * Clears cached definitions if in Twig debug mode.
   */
  public function handleTwigDebug();

}
