<?php

namespace Drupal\default_content_deploy;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\default_content\Importer as DCImporter;
use Drupal\default_content\ScannerInterface;
use Drupal\hal\LinkManager\LinkManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Serializer;

/**
 * A service for handling import of default content.
 *
 * The importContent() method is almost duplicate of
 *   \Drupal\default_content\Importer::importContent with injected code for
 *   content update. We are waiting for better DC code structure in a future.
 */
class Importer extends DCImporter {

  /**
   * Deploy manager.
   *
   * @var \Drupal\default_content_deploy\DeployManager
   */
  protected $deployManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * The default_content_deploy logger channel.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $logger;

  /**
   * Scanned files.
   *
   * @var object[]
   */
  private $files;

  /**
   * Directory to import.
   *
   * @var string
   */
  private $folder;

  /**
   * Data to import.
   *
   * @var array
   */
  private $dataToImport = [];

  /**
   * Is remove changes of an old content.
   *
   * @var bool
   */
  protected $forceOverride;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * The Entity repository manager.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The cache data.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructs the default content deploy manager.
   *
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   The serializer service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\hal\LinkManager\LinkManagerInterface $link_manager
   *   The link manager service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\default_content\ScannerInterface $scanner
   *   The file scanner.
   * @param string $link_domain
   *   Defines relation domain URI for entity links.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The account switcher.
   * @param \Drupal\default_content_deploy\DeployManager $deploy_manager
   *   Deploy manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The current request.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The Entity repository manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache data.
   */
  public function __construct(Serializer $serializer, EntityTypeManagerInterface $entity_type_manager, LinkManagerInterface $link_manager, EventDispatcherInterface $event_dispatcher, ScannerInterface $scanner, $link_domain, AccountSwitcherInterface $account_switcher, DeployManager $deploy_manager, ModuleHandlerInterface $module_handler, LoggerInterface $logger, RequestStack $request_stack, EntityRepositoryInterface $entity_repository, CacheBackendInterface $cache) {
    parent::__construct($serializer, $entity_type_manager, $link_manager, $event_dispatcher, $scanner, $link_domain, $account_switcher);

    $this->deployManager = $deploy_manager;
    $this->moduleHandler = $module_handler;
    $this->logger = $logger;
    $this->request = $request_stack->getCurrentRequest();
    $this->entityRepository = $entity_repository;
    $this->cache = $cache;
  }

  /**
   * Is remove changes of an old content.
   *
   * @param bool $is_override
   *
   * @return \Drupal\default_content_deploy\Importer
   */
  public function setForceOverride(bool $is_override) {
    $this->forceOverride = $is_override;
    return $this;
  }

  /**
   * Set directory to import.
   *
   * @param string $folder
   *   The content folder.
   *
   * @return \Drupal\default_content_deploy\Importer
   */
  public function setFolder($folder) {
    $this->folder = $folder;
    return $this;
  }

  /**
   * Get directory to import.
   *
   * @return string
   *   The content folder.
   *
   * @throws \Exception
   */
  protected function getFolder() {
    $folder = $this->folder ?: $this->deployManager->getContentFolder();
    return $folder;
  }

  /**
   * Get Imported data result.
   *
   * @return array
   */
  public function getResult() {
    return $this->dataToImport;
  }

  /**
   * Import data from JSON and create new entities, or update existing.
   *
   * @return $this
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function prepareForImport() {
    $this->files = $this->scanner->scan($this->getFolder());

    foreach ($this->files as $file) {
      $uuid = str_replace('.json', '', $file->name);

      if (!isset($this->dataToImport[$uuid])) {
        $this->decodeFile($file);
      }
    }

    return $this;
  }

  /**
   * Import to entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function import() {
    $files = $this->dataToImport;

    if (PHP_SAPI === 'cli') {
      $root_user = $this->entityTypeManager->getStorage('user')->load(1);
      $this->accountSwitcher->switchTo($root_user);
    }

    foreach ($files as $file) {
      if ($file['status'] != 'skip') {
        $entity_type = $file['entity_type_id'];
        $class = $this->entityTypeManager->getDefinition($entity_type)->getClass();
        $this->preDenormalize($file);

        /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
        $entity = $this->serializer->denormalize($file['data'], $class, 'hal_json', ['request_method' => 'POST']);
        $entity->enforceIsNew($file['is_new']);
        $entity->save();
      }
    }

    if (PHP_SAPI === 'cli') {
      $this->accountSwitcher->switchBack();
    }
  }

  /**
   * Prepare file to import.
   *
   * @param $file
   *
   * @return $this
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  protected function decodeFile($file) {
    // Get parsed data.
    $parsed_data = $this->parseFile($file);
    $this->preDecode($parsed_data);

    // Decode.
    $decode = $this->serializer->decode($parsed_data, 'hal_json');
    $references = $this->getReferences($decode);

    if ($references) {
      foreach ($references as $reference) {
        $this->decodeFile($reference);
      }
    }

    // Prepare data for import.
    $link = $decode['_links']['type']['href'];
    $data_to_import = [
      'data' => $decode,
      'entity_type_id' => $this->getEntityTypeByLink($link),
    ];

    $this->preAddToImport($data_to_import);
    $this->addToImport($data_to_import);

    return $this;
  }

  /**
   * Here you can rewrite the file data before decoding it.
   *
   * @param $data
   */
  protected function preDecode(&$data) {
    // Init variables.
    $pattern_first = '/"http:\\\\\/\\\\\/(.*?)": \[/';
    $pattern_second = '/ "href": "(.*?)"/';
    $pattern_link_first = '/:\\\\\/\\\\\/(.*?)\\\\\//';
    $pattern_link_second = '/ "href": "http:\\\\\/\\\\\/(.*?)\\\\\//';

    // Replace host in the all links.
    $this->replaceHost($pattern_first, $pattern_link_first, $data);
    $this->replaceHost($pattern_second, $pattern_link_second, $data);
  }

  /**
   * Here we can edit data`s value before importing.
   *
   * @param $data
   *
   * @return $this
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function preAddToImport(&$data) {
    $decode = $data['data'];
    $uuid = $decode['uuid'][0]['value'];
    $entity_type_id = $data['entity_type_id'];
    $entity = $this->entityRepository->loadEntityByUuid($entity_type_id, $uuid);
    $entity_type_object = $this->entityTypeManager->getDefinition($entity_type_id);

    // Keys of entity.
    $key_id = $entity_type_object->getKey('id');
    $key_revision_id = $entity_type_object->getKey('revision');

    if ($entity) {
      $is_new = FALSE;
      $status = 'update';

      // Replace ID entity.
      $decode[$key_id][0]['value'] = $entity->id();

      // Skip if the Changed time the same or less in the file.
      if (method_exists($entity, 'getChangedTime')) {
        $changed_time_entity = $entity->getChangedTime();
        $changed_time_file = strtotime($decode['changed'][0]['value']);

        if (!$this->forceOverride && $changed_time_file <= $changed_time_entity) {
          $status = 'skip';
        }
      }
    }
    else {
      $status = 'create';
      $is_new = TRUE;

      // Ignore ID for creating a new entity.
      unset($decode[$key_id]);
    }

    // Ignore revision and id of entity.
    unset($decode[$key_revision_id]);

    $data['is_new'] = $is_new;
    $data['status'] = $status;
    $data['data'] = $decode;

    return $this;
  }

  /**
   * This event is triggered before decoding to an entity.
   *
   * @param $file
   *
   * @return $this
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function preDenormalize(&$file) {
    $this->updateTargetRevisionId($file['data']);

    return $this;
  }

  /**
   * Adding prepared data for import.
   *
   * @param $data
   *
   * @return $this
   */
  protected function addToImport($data) {
    $uuid = $data['data']['uuid'][0]['value'];
    $this->dataToImport[$uuid] = $data;

    return $this;
  }

  /**
   * Get all reference by entity array content.
   *
   * @param array $content
   *
   * @return array
   */
  private function getReferences(array $content) {
    $references = [];

    if (isset($content['_embedded'])) {
      foreach ($content['_embedded'] as $link) {
        foreach ($link as $reference) {
          $uuid = $reference['uuid'][0]['value'];
          $path = $this->getPathToFileByName($uuid);

          if ($path) {
            $references[] = $this->files[$path];
          }
        }
      }
    }

    return $references;
  }

  /**
   * Get path to file by Name.
   *
   * @param $name
   *
   * @return false|int|string
   */
  private function getPathToFileByName($name) {
    $array_column = array_column($this->files, 'name', 'uri');
    $path = array_search($name . '.json', $array_column);

    return $path;
  }

  /**
   * Get Entity type ID by link.
   *
   * @param $link
   *
   * @return string|string[]
   */
  private function getEntityTypeByLink($link) {
    $type = $this->linkManager->getTypeInternalIds($link);

    if ($type) {
      $entity_type_id = $type['entity_type'];
    }
    else {
      $components = array_reverse(explode('/', $link));
      $entity_type_id = $components[1];
      $this->cache->invalidate('hal:links:types');
    }

    return $entity_type_id;
  }

  /**
   * If this entity contains a reference field with target revision is value,
   * we should to update it.
   *
   * @param $decode
   *
   * @return $this
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function updateTargetRevisionId(&$decode) {
    if (isset($decode['_embedded'])) {
      foreach ($decode['_embedded'] as $link_key => $link) {
        if (array_column($link, 'target_revision_id')) {
          foreach ($link as $ref_key => $reference) {
            $url = $reference['_links']['type']['href'];
            $uuid = $reference['uuid'][0]['value'];
            $entity_type = $this->getEntityTypeByLink($url);
            $entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);

            // Update the Target revision id if child entity exist on this site.
            if ($entity) {
              $revision_id = $entity->getRevisionId();
              $decode['_embedded'][$link_key][$ref_key]['target_revision_id'] = $revision_id;
            }
          }
        }
      }
    }

    return $this;
  }

  /**
   * Change to current host in a config file.
   *
   * @param $pattern
   * @param $pattern_link
   * @param $data
   */
  private function replaceHost($pattern, $pattern_link, &$data) {
    $host = $this->request->getHttpHost();
    preg_match_all($pattern, $data, $match);

    foreach ($match[0] as $origin_str) {
      preg_match($pattern_link, $origin_str, $match_host);

      if (!empty($match_host)) {
        $valid_str = str_replace($match_host[1], $host, $origin_str);
        $data = str_replace($origin_str, $valid_str, $data);
      }
    }
  }

}
