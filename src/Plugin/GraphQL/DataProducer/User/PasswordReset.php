<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\User;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Response\Response;
use Drupal\graphql\GraphQL\Response\ResponseInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\user\UserFloodControlInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Resets the user's password (mutation).
 */
#[DataProducer(
  id: "password_reset",
  name: new TranslatableMarkup("Password reset"),
  description: new TranslatableMarkup("Allows to reset the password."),
  consumes: [
    "email" => new ContextDefinition(
      data_type: "email",
      label: new TranslatableMarkup("Email"),
    ),
  ],
)]
class PasswordReset extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
    $request_stack = $container->get('request_stack');
    /** @var \Drupal\Core\Logger\LoggerChannelInterface $logger */
    $logger = $container->get('logger.channel.graphql');
    /** @var \Drupal\user\UserFloodControlInterface $user_flood_control */
    $user_flood_control = $container->get('user.flood_control');
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_kernel'),
      $request_stack,
      $logger,
      $user_flood_control,
      $config_factory,
    );
  }

  /**
   * PasswordReset constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The http kernel, necessary for password reset subrequest.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger service.
   * @param \Drupal\user\UserFloodControlInterface $userFloodControl
   *   The user flood control service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected HttpKernelInterface $httpKernel,
    protected RequestStack $requestStack,
    protected LoggerChannelInterface $logger,
    protected UserFloodControlInterface $userFloodControl,
    protected ConfigFactoryInterface $configFactory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Creates an user.
   *
   * @param string $email
   *   The email address to reset the password for.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The field's caching metadata.
   *
   * @return \Drupal\graphql\GraphQL\Response\ResponseInterface
   *   Response for password reset mutation with violations in case of failure.
   */
  public function resolve(string $email, RefinableCacheableDependencyInterface $metadata): ResponseInterface {
    $response = new Response();

    // Flood control: UserAuthenticationController::resetPassword() does not
    // enforce flood control (unlike login()), so we must check it here. We
    // rate limit per email address rather than per IP, otherwise legitimate
    // users sharing an IP (eg. behind a conference NAT) would be penalized.
    // The core "user.flood" user_limit/user_window settings are reused; a
    // graphql-specific config could be introduced later if needed.
    // @todo Revisit once #3587709 lands and refactors password reset.
    $flood_config = $this->configFactory->get('user.flood');
    $flood_event = 'graphql.password_reset_user';
    $flood_identifier = mb_strtolower($email);
    if (!$this->userFloodControl->isAllowed($flood_event, $flood_config->get('user_limit'), $flood_config->get('user_window'), $flood_identifier)) {
      $response->addViolation($this->t('Too many password reset attempts for this account. Please try again later.'));
      return $response;
    }
    $this->userFloodControl->register($flood_event, $flood_config->get('user_window'), $flood_identifier);

    $content = [
      'mail' => $email,
    ];

    // Build up an authentication request for controller out of current request
    // but replace the request body with proper content. This way most of the
    // data are reused including the client's IP which is needed for flood
    // control. The request body is the only thing (besides client's IP) which
    // is pulled from the request within controller.
    $current_request = $this->requestStack->getCurrentRequest();
    $url = Url::fromRoute('user.pass.http')->toString(TRUE);
    $metadata->addCacheableDependency($url);
    $auth_request = Request::create(
      $url->getGeneratedUrl(),
      'POST',
      [],
      $current_request->cookies->all(),
      [],
      $current_request->server->all(),
      json_encode($content),
    );
    $auth_request->setRequestFormat('json');

    try {
      $controller_response = $this->httpKernel->handle(
        $auth_request,
        HttpKernelInterface::SUB_REQUEST
      );
    }
    catch (\Exception $e) {
      // Show general error message so potential attacker cannot abuse endpoint
      // to eg check if some email exist or not. Log to watchdog for potential
      // further investigation.
      $this->logger->warning($e->getMessage());
      $response->addViolation($this->t('Unable to reset password, please try again later.'));
      return $response;
    }
    // Show general error message also in case of unexpected response. Log to
    // watchdog for potential further investigation.
    if ($controller_response->getStatusCode() !== 200) {
      $this->logger->warning("Unexpected response code @code during password reset.", ['@code' => $controller_response->getStatusCode()]);
      $response->addViolation($this->t('Unable to reset password, please try again later.'));
    }

    return $response;
  }

}
