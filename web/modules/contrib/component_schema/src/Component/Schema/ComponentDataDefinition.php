<?php

namespace Drupal\component_schema\Component\Schema;

use Drupal\Core\TypedData\MapDataDefinition;

/**
 * A typed component data definition class for defining maps.
 */
class ComponentDataDefinition extends MapDataDefinition implements ComponentDataDefinitionInterface {

  /**
   * {@inheritdoc}
   */
  public function getStyleguideTemplate() {
    return $this->definition['styleguide_template'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponentTemplate() {
    assert(!empty($this->definition['component_template']), 'Required component_template property is present');
    return $this->definition['component_template'];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup() {
    assert(!empty($this->definition['group']), 'Required group property is present');
    return $this->definition['group'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDocumentationUrl() {
    return $this->definition['documentation_url'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getProvider() {
    assert(!empty($this->definition['provider']), 'Required provider property is present');
    return $this->definition['provider'];
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderType() {
    assert(!empty($this->definition['provider_type']), 'Required provider_type property is present');
    return $this->definition['provider_type'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return $this->definition['libraries'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponentType() {
    return $this->definition['component_type'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttributeTarget() {
    return $this->definition['attribute_target'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getHtmlClass() {
    return $this->definition['html_class'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getInheritsFrom() {
    return $this->definition['inherits_from'] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function takesUi() {
    return $this->definition['takes_ui'] ?? FALSE;
  }

}
