<?php

namespace Drupal\component_schema\Component\Schema;

trait ComponentVariableDataDefinitionTrait {

  /**
   * {@inheritdoc}
   */
  public function getDocumentationUrl() {
    return $this->definition['documentation_url'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefault() {
    return $this->definition['default'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreview() {
    return $this->definition['preview'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->definition['options'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isNullable() {
    return isset($this->definition['nullable']) && $this->definition['nullable'] == TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function takesUi() {
    return $this->definition['takes_ui'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getUiType() {
    return $this->definition['ui_type'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getUiClasses() {
    return $this->definition['ui_classes'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isInheritable() {
    return $this->definition['is_inheritable'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getInheritedFrom() {
    return $this->definition['inherited_from'] ?? FALSE;
  }
}
