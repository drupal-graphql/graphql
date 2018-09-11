<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\language\LanguageNegotiator;
use Drupal\Core\Language\LanguageManagerInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Retrieve a route object based on a path.
 *
 * @GraphQLField(
 *   id = "url_route",
 *   secure = true,
 *   name = "route",
 *   description = @Translation("Loads a route by its path."),
 *   type = "Url",
 *   arguments = {
 *     "path" = "String!"
 *   }
 * )
 */
class Route extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The language negotiator service.
   *
   * @var \Drupal\language\LanguageNegotiator
   */
  protected $languageNegotiator;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.validator'),
      $container->has('language_negotiator') ? $container->get('language_negotiator') : NULL,
      $container->get('language_manager')
    );
  }

  /**
   * Route constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   * @param \Drupal\language\LanguageNegotiator|null $languageNegotiator
   *   The language negotiator.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    PathValidatorInterface $pathValidator,
    $languageNegotiator,
    $languageManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->pathValidator = $pathValidator;
    $this->languageNegotiator = $languageNegotiator;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   *
   * Execute routing in language context.
   *
   * Language context has to be inferred from the path prefix, but set before
   * `resolveValues` is invoked.
   */
  public function resolve($value, array $args, ResolveContext $context, ResolveInfo $info) {
    // For now we just take the "url" negotiator into account.
    if ($this->languageManager->isMultilingual() && $this->languageNegotiator) {
      if ($negotiator = $this->languageNegotiator->getNegotiationMethodInstance('language-url')) {
        $context->setContext('language', $negotiator->getLangcode(Request::create($args['path'])), $info);
      }
      else {
        $context->setContext('language', $this->languageManager->getDefaultLanguage()->getId(), $info);
      }
    }

    return parent::resolve($value, $args, $context, $info);
  }

  /**
   * {@inheritdoc}
   *
   * Route field is always language aware since it sets it's context from
   * the prefix.
   */
  protected function isLanguageAwareField() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if (($url = $this->pathValidator->getUrlIfValidWithoutAccessCheck($args['path'])) && $url->access()) {
      yield $url;
    }
    else {
      yield (new CacheableValue(NULL))->addCacheTags(['4xx-response']);
    }
  }

}
