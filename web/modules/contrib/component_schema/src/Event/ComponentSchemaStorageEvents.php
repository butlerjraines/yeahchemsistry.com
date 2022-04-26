<?php

namespace Drupal\component_schema\Event;

/**
 * Defines events for the component schema system.
 */
final class ComponentSchemaStorageEvents {

  /**
   * Name of the event fired when assembling component schemas.
   *
   * This event allows subscribers to add or modify components. The event listener method receives a
   * \Drupal\Core\Config\ComponentStorageTransformEvent instance. This event
   * contains a config storage which subscribers can interact with and which
   * will finally be used to provide component data.
   *
   * @code
   *   $storage = $event->getStorage();
   * @endcode
   *
   * @Event
   *
   * @see \Drupal\Core\Config\StorageTransformEvent
   * @see \Drupal\Core\Config\ConfigEvents::STORAGE_TRANSFORM_EXPORT
   *
   * @var string
   */
  const TRANSFORM_COLLECT = 'component_schema.transform.collect';

}
