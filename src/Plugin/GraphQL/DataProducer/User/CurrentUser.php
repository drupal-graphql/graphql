<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\User;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gets the current user.
 */
#[DataProducer(
  id: "current_user",
  name: new TranslatableMarkup("Current user"),
  description: new TranslatableMarkup("Current logged in user."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Current user")
  )
)]
class CurrentUser extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * CurrentUser constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected AccountInterface $currentUser,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Returns current user.
   *
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field_context
   *   Field context.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The current user.
   */
  public function resolve(FieldContext $field_context): AccountInterface {
    // Response must be cached per user so that information from previously
    // logged in users will not leak to newly logged in users. Note that we need
    // to add the cache metadata manually because the AccountProxy object does
    // not implement CacheableDependencyInterface.
    $field_context->addCacheContexts(['user']);

    // Also add a cache tag for the user entity so that changes to the user
    // will invalidate the cache.
    $field_context->addCacheTags(['user:' . $this->currentUser->id()]);

    return $this->currentUser;
  }

}
