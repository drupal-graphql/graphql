<?php

namespace Drupal\graphql_metatag\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\graphql_metatag\MetatagResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * The metatags field.
 *
 * @GraphQLField(
 *   id = "metatags",
 *   name = "metatags",
 *   types = {"Url"},
 *   type = "MetaTag",
 *   multi = true
 * )
 */
class Metatags extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * A http kernel to issue sub-requests to.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * A language manager to reset language negotiation before resolving metatags.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, HttpKernelInterface $httpKernel, RequestStack $requestStack, LanguageManagerInterface $languageManager) {
    $this->httpKernel = $httpKernel;
    $this->languageManager = $languageManager;
    $this->requestStack = $requestStack;

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
      $container->get('http_kernel'),
      $container->get('request_stack'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      $currentRequest = $this->requestStack->getCurrentRequest();

      $request = Request::create(
        $value->getOption('routed_path') ?: $value->toString(),
        'GET',
        $currentRequest->query->all(),
        $currentRequest->cookies->all(),
        $currentRequest->files->all(),
        $currentRequest->server->all()
      );

      $request->attributes->set('graphql_metatag', TRUE);

      if ($session = $currentRequest->getSession()) {
        $request->setSession($session);
      }

      // TODO:
      // Language manager stores negotiation state between requests.
      //
      // This is a hack to ensure language get's re-negotiated.
      // This will issue affect other sub-requests and potentially other
      // services that keep a static cache.
      // $this->languageManager->reset();

      $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);

      // TODO:
      // Remove the request stack manipulation once the core issue described at
      // https://www.drupal.org/node/2613044 is resolved.
      while ($this->requestStack->getCurrentRequest() === $request) {
        $this->requestStack->pop();
      }

      if ($response instanceof MetatagResponse) {
        foreach ($response->getMetatags() as $tag) {
          yield $tag;
        }
      }
    }
  }

}
