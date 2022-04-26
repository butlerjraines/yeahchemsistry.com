<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Interface for typed component definitions.
 */
interface ComponentDataDefinitionInterface extends DataDefinitionInterface {

  /**
   * Gets a twig-namespaced path to a styleguide template for the component.
   *
   * @return string|NULL
   *   The twig-namespaced path to a styleguide template or NULL if none.
   */
  public function getStyleguideTemplate();

  /**
   * Gets a twig-namespaced path to the component template.
   *
   * @return string
   *   The twig-namespaced path to a template.
   */
  public function getComponentTemplate();

  /**
   * Gets the name of the group that this component belongs to.
   *
   * @return string
   *   The name of a group.
   */
  public function getGroup();

  /**
   * Gets the URL of a page with documentation on the variable.
   *
   * @return string|NULL
   *   The URL value or NULL if none.
   */
  public function getDocumentationUrl();

  /**
   * Gets the name of the extension that provides the component.
   *
   * @return string
   *   The extension name.
   */
  public function getProvider();

  /**
   * Gets the type of the extension that provides the component.
   *
   * @return string
   *   The extension type.
   */
  public function getProviderType();

  /**
   * Gets the libraries used by the component.
   *
   * @return array|NULL
   *   The libraries value or NULL if none.
   */
  public function getLibraries();

  /**
   * Gets the component type of a variable.
   *
   * @return string|NULL
   *   The name of a component type or NULL if none.
   */
  public function getComponentType();

  /**
   * Gets the name of the target child attribute element.
   *
   * @return string|NULL
   *   The element name or NULL if none.
   */
  public function getAttributeTarget();

  /**
   * Gets the primary HTML class of a component.
   *
   * This class is expected to be applied to a top-level HTML element such as a
   * wrapping div.
   *
   * @return string|NULL
   *   The primary HTML class for a component, or NULL if none.
   */
  public function getHtmlClass();

  /**
   * Gets an array of components that this component inherits.
   *
   * @return array|NULL
   *   An array of component names.
   */
  public function getInheritsFrom();

  /**
   * Determines if the component type is appropriate for an admin-facing UI.
   *
   * @return bool
   *   TRUE if the component type takes a UI, otherwise FALSE.
   */
  public function takesUi();

}
