<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the current routes entity, if it is an entity route.
 *
 * TODO: Move this to `InternalUrl` (breaking change).
 *
 * @GraphQLField(
 *   id = "route_entity",
 *   secure = true,
 *   name = "entity",
 *   parents = {"EntityCanonicalUrl"},
 *   type = "Entity"
 * )
 */
class RouteEntity extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
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
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    LanguageManagerInterface $languageManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
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
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      list($prefix, $entityType, $suffix) = explode('.', $value->getRouteName());
      $parameters = $value->getRouteParameters();

      if (!($prefix === 'entity' && $suffix === 'canonical') || !array_key_exists($entityType, $parameters)) {
        return;
      }

      $entity = $this->entityTypeManager
        ->getStorage($entityType)
        ->load($parameters[$entityType]);

      $lang = $this->languageManager->getCurrentLanguage(Language::TYPE_CONTENT)->getId();

      if ($entity instanceof TranslatableInterface && $entity->hasTranslation($lang)) {
        $translation = $entity->getTranslation($lang);
        if ($translation->access('view')) {
          yield $translation;
        }
      }
      else if ($entity->access('view')) {
        yield $entity;
      }

    }
  }

}
