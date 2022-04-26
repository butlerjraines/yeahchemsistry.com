<?php

namespace Drupal\component_schema\Component;

use Drupal\component_schema\ComponentProcessor;
use Drupal\component_schema\Event\ComponentSchemaStorageEvents;
use Drupal\component_schema\Event\ComponentStorageTransformEvent;
use Drupal\Core\Config\MemoryStorage;
use Drupal\Core\Config\ReadOnlyStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\StorageManagerInterface;
use Drupal\Core\Config\StorageCopyTrait;
use Drupal\Core\Config\StorageTransformerException;
use Drupal\Core\Lock\LockBackendInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The component storage manager dispatches an event for the export storage.
 *
 * This class is not meant to be extended and is final to make sure the
 * constructor and the getStorage method are both changed when this pattern is
 * used in other circumstances.
 */
final class ComponentStorageManager implements StorageManagerInterface {

  use StorageCopyTrait;

  /**
   * The name used to identify the lock.
   */
  const LOCK_NAME = 'component_storage_manager';

  /**
   * The memory storage.
   *
   * @var \Drupal\Core\Config\MemoryStorage
   */
  protected $storage;

  /**
   * The provided component schema storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $provided;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The used lock backend instance.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The component processor.
   *
   * @var \Drupal\component_schema\ComponentProcessor
   */
  protected $componentProcessor;

  /**
   * ExportStorageManager constructor.
   *
   * @param \Drupal\Core\Config\StorageInterface $active
   *   The provided component schema storage to prime the component storage.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The used lock backend instance.
   * @param \Drupal\component_schema\ComponentProcessor $component_processor
   *   The component processor.
   */
  public function __construct(StorageInterface $provided, EventDispatcherInterface $event_dispatcher, LockBackendInterface $lock, ComponentProcessor $component_processor) {
    $this->provided = $provided;
    $this->eventDispatcher = $event_dispatcher;
    $this->lock = $lock;
    $this->componentProcessor = $component_processor;
    // The point of this service is to provide the storage and dispatch the
    // event when needed, so the storage itself can not be a service.
    $this->storage = new MemoryStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function getStorage() {
    // Copy from the provided component schema storage.
    self::replaceStorageContents($this->provided, $this->storage);

    // Load components
    $components = $this->componentProcessor->getComponents();
    foreach ($components as $name => $data) {
      // Translate component into array format.
      $data = [
        $name => $data,
      ];
      // The $name argument is arbitrary as it need only be unique.
      $this->storage->write($name, $data);
    }

    // Acquire a lock for the request to assert that the storage does not change
    // when a concurrent request transforms the storage.
    if (!$this->lock->acquire(self::LOCK_NAME)) {
      $this->lock->wait(self::LOCK_NAME);
      if (!$this->lock->acquire(self::LOCK_NAME)) {
        throw new StorageTransformerException("Cannot acquire config export transformer lock.");
      }
    }

    $this->eventDispatcher->dispatch(ComponentSchemaStorageEvents::TRANSFORM_COLLECT, new ComponentStorageTransformEvent($this->storage));

    return new ReadOnlyStorage($this->storage);
  }

}
