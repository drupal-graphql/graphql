<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns the URL of an entity.
 *
 * @DataProducer(
 *   id = "entity_url",
 *   name = @Translation("Entity url"),
 *   description = @Translation("Returns the entity's url."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Url")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "rel" = @ContextDefinition("string",
 *       label = @Translation("Relationship type"),
 *       description = @Translation("The relationship type, e.g. canonical"),
 *       required = FALSE
 *     ),
 *     "options" = @ContextDefinition("any",
 *       label = @Translation("URL Options"),
 *       description = @Translation("Options to pass to the toUrl call"),
 *       required = FALSE
 *     ),
 *     "access_user" = @ContextDefinition("entity:user",
 *         label = @Translation("User"),
 *         required = FALSE,
 *         default_value = NULL
 *     ),
 *   }
 * )
 */
class EntityUrl extends DataProducerPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('access_manager')
    );
  }

  /**
   * EntityTranslation constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Access\AccessManagerInterface $accessManager
   *   The access manager service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, AccessManagerInterface $accessManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->accessManager = $accessManager;
  }

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to create a canonical URL for.
   * @param string|null $rel
   *   The link relationship type, for example: canonical or edit-form.
   * @param array|null $options
   *   The options to provided to the URL generator.
   * @param \Drupal\Core\Session\AccountInterface|null $accessUser
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *
   * @return \Drupal\Core\Url|null
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function resolve(EntityInterface $entity, ?string $rel, ?array $options, ?AccountInterface $accessUser, FieldContext $context) {
    $url = $entity->toUrl($rel ?? 'canonical', $options ?? []);

    // @see https://www.drupal.org/project/drupal/issues/2677902
    $access = $this->accessManager->checkNamedRoute($url->getRouteName(), $url->getRouteParameters(), $accessUser, TRUE);
    $context->addCacheableDependency($access);
    if ($access->isAllowed()) {
      return $url;
    }

    return NULL;
  }

}
