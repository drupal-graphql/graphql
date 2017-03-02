<?php

namespace Drupal\graphql_example\GraphQL\Field\Root;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\graphql_example\GraphQL\Type\CreatePageResponseType;
use Drupal\graphql_example\GraphQL\Type\CreatePageInputType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\InputField;

class CreatePageField extends SelfAwareField implements ContainerAwareInterface {
  use ContainerAwareTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build(FieldConfig $config) {
    $config->addArgument(new InputField([
      'name' => 'input',
      'type' => new CreatePageInputType(),
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $this->container->get('entity_type.manager');
    /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
    $nodeStorage = $entityTypeManager->getStorage('node');

    /** @var \Drupal\node\NodeInterface $page */
    $page = $nodeStorage->create([
      'type' => 'page',
      'title' => isset($args['input']['title']) ? $args['input']['title'] : NULL,
      'body' => isset($args['input']['body']) ? $args['input']['body'] : NULL,
    ]);

    // Check if the current user has access to create the page.
    if (!$page->access('create')) {
      $message = $this->t('You do not have access to create pages.');

      return [
        'errors' => [$message],
        'page' => NULL,
      ];
    }

    // Check if there are any validation errors.
    if (($errors = $page->validate()) && $errors->count() > 0) {
      $messages = array_map(function (ConstraintViolationInterface $violation) {
        return "{$violation->getPropertyPath()}: {$violation->getMessage()}";
      }, iterator_to_array($errors));

      return [
        'errors' => $messages,
        'page' => NULL,
      ];
    }

    // Save the page and return it in the response.
    $page->save();

    return [
      'errors' => [],
      'page' => $page,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'createPage';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return new CreatePageResponseType();
  }
}
