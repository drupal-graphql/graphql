<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\User;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Response\Response;
use Drupal\graphql\GraphQL\Response\ResponseInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\user\Controller\UserAuthenticationController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container,
      $request_stack,
      $logger
    );
  }

  /**
   * UserRegister constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container, necessary for creating a UserAuthenticationController.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected ContainerInterface $container,
    protected RequestStack $requestStack,
    protected LoggerChannelInterface $logger,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Creates an user.
   *
   * @param string $email
   *   The email address to reset the password for.
   *
   * @return \Drupal\graphql\GraphQL\Response\ResponseInterface
   *   Response for password reset mutation with violations in case of failure.
   */
  public function resolve(string $email): ResponseInterface {
    $content = [
      'mail' => $email,
    ];

    // Drupal does not have a user authentication service so we need to use the
    // authentication controller instead.
    $controller = UserAuthenticationController::create($this->container);
    // Build up an authentication request for controller out of current request
    // but replace the request body with proper content. This way most of the
    // data are reused including the client's IP which is needed for flood
    // control. The request body is the only thing (besides client's IP) which
    // is pulled from the request within controller.
    $current_request = $this->requestStack->getCurrentRequest();
    $auth_request = new Request(
      $current_request->query->all(),
      $current_request->request->all(),
      $current_request->attributes->all(),
      $current_request->cookies->all(),
      $current_request->files->all(),
      $current_request->server->all(),
      json_encode($content)
    );
    $auth_request->setRequestFormat('json');

    $response = new Response();
    try {
      $controller_response = $controller->resetPassword($auth_request);
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
