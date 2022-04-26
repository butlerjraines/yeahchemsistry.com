<?php

namespace Drupal\component_schema\Component\Schema;

/**
 * Interface for data definitions for varibles that provide HTML attributes.
 */
interface ComponentVariableAttributeProviderDataDefinitionInterface extends ComponentVariableDataDefinitionInterface {

  /**
   * Determines whether the specified data object provides a class attribute.
   *
   * @return bool
   *   Whether or not the data object provides a class attribute (TRUE if
   *   provides).
   */
  public function providesClass();

  /**
   * Determines whether the specified data object provides an HTML attribute.
   *
   * @return bool
   *   Whether or not the data object provides an HTML attribute (TRUE if
   *   provides).
   */
  public function providesAttribute();

  /**
   * Gets the provided name for a variable attribute.
   *
   * @return string|NULL
   *   The provided name or NULL if none.
   */
  public function getProvidedName();

  /**
   * Gets the provided value for a class or attribute.
   *
   * @return string|NULL
   *   The provided value or NULL if none.
   */
  public function getProvidedValue();

  /**
   * Gets the name of the target sibling attribute element.
   *
   * @return string|NULL
   *   The element name or NULL if none.
   */
  public function getAttributeTarget();

  /**
   * Determines whether the specified data object supports breakpoints.
   *
   * @return bool
   *   Whether or not the data object supports breakpoints (TRUE if supports).
   */
  public function supportsBreakpoints();

}
