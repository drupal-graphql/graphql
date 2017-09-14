<?php

namespace Drupal\graphql\Plugin\views\row;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\views\Entity\Render\EntityTranslationRenderTrait;
use Drupal\views\Plugin\views\row\RowPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin which displays entities as raw data.
 *
 * @ViewsRow(
 *   id = "graphql_entity",
 *   title = @Translation("Entity"),
 *   help = @Translation("Use entities as row data."),
 *   display_types = {"graphql"}
 * )
 */
class GraphQLEntityRow extends RowPluginBase {

  use EntityTranslationRenderTrait {
    getEntityTranslationRenderer as getEntityTranslationRendererBase;
  }

  /**
   * {@inheritdoc}
   */
  protected $usesOptions = FALSE;

  /**
   * Contains the entity type of this row plugin instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render($row) {
    if ($entity = $this->getEntityFromRow($row)) {
      return $this->view->getBaseEntityType() ? $this->getEntityTranslation($entity, $row) : $entity;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityTranslationRenderer() {
    if ($this->view->getBaseEntityType()) {
      return $this->getEntityTranslationRendererBase();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeId() {
    if ($entityType = $this->view->getBaseEntityType()) {
      return $entityType->id();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityManager() {
    return $this->entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function getLanguageManager() {
    return $this->languageManager;
  }

  /**
   * Retrieves the entity object from a result row.
   *
   * @param \Drupal\Views\ResultRow $row
   *   The views result row object.
   *
   * @return null|\Drupal\Core\Entity\EntityInterface
   *   The extracted entity object or NULL if it could not be retrieved.
   */
  protected function getEntityFromRow(ResultRow $row) {
    if (isset($row->_entity) && $row->_entity instanceof EntityInterface) {
      return $row->_entity;
    }

    if (isset($row->_object) && $row->_object instanceof EntityAdapter) {
      return $row->_object->getValue();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getView() {
    return $this->view;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    parent::query();

    if ($this->view->getBaseEntityType()) {
      $this->getEntityTranslationRenderer()->query($this->view->getQuery());
    }
  }

}
