<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Interface for typed component variable data definitions.
 */
interface ComponentVariableDataDefinitionInterface extends DataDefinitionInterface {

  /**
   * Denotes a field UI type.
   */
  const UI_TYPE_FIELD = 'field';

  /**
   * Denotes a setting UI type.
   */
  const UI_TYPE_SETTING = 'setting';

  /**
   * Gets the URL of a page with documentation on the variable.
   *
   * @return string|NULL
   *   The URL value or NULL if none.
   */
  public function getDocumentationUrl();

  /**
   * Gets the default value for a variable.
   *
   * @return string|NULL
   *   The default value or NULL if none.
   */
  public function getDefault();

  /**
   * Gets the preview value for a variable.
   *
   * A preview value is suitable for applying to a component as sample content
   * in a preview context.
   *
   * @return string|NULL
   *   The preview value or NULL if none.
   */
  public function getPreview();

  /**
   * Gets the valid options for a variable.
   *
   * @return array|NULL
   *   The options value where keys are machine names and values are
   *   human-readable lables, or NULL if none.
   */
  public function getOptions();

  /**
   * Determines if this element allows NULL as a value.
   *
   * @return bool
   *   TRUE if NULL is a valid value, FALSE otherwise.
   */
  public function isNullable();

  /**
   * Determines if the variable is appropriate for an admin-facing UI.
   *
   * @return bool
   *   TRUE if the variable takes a UI, otherwise FALSE.
   */
  public function takesUi();

  /**
   * Returns the type of UI representation that an element takes.
   *
   * @return string|NULL
   *   The type of UI representation that an element takes or NULL if none.
   *   Valid types are:
   *   - \Drupal\component_schema\Component\Schema\ComponentVariableDataDefinitionInterface::UI_TYPE_FIELD
   *   - \Drupal\component_schema\Component\Schema\ComponentVariableDataDefinitionInterface::UI_TYPE_SETTING
   */
  public function getUiType();

  /**
   * Returns class names that are applicable to admin UI elements.
   *
   * Keys are the same as the keys in the 'options' property.
   *
   * @return array|NULL
   *   An array of class names keyed by value.
   */
  public function getUiClasses();

  /**
   * Determines if the variable is inheritable via component inheritance.
   *
   * @return bool
   *   TRUE if the variable is inheritablee, otherwise FALSE.
   */
  public function isInheritable();

  /**
   * Gets an array of component types that the variable is inherited from.
   *
   * It is not necessary to manually set this property as the value is
   * calculate programmatically.
   *
   * @return string[]|NULL
   *   An array of component types.
   */
  public function getInheritedFrom();

}
