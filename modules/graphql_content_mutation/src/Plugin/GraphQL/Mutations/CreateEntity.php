<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\Mutations;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql_content_mutation\Plugin\GraphQL\InputTypes\EntityInput;
use Drupal\graphql_content_mutation\Plugin\GraphQL\InputTypes\EntityInputField;
use Drupal\graphql_core\GraphQL\MutationPluginBase;
use Drupal\graphql_content_mutation\Plugin\GraphQL\CreateEntityOutputWrapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

/**
 * Create an entity.
 *
 * @GraphQLMutation(
 *   id = "create_entity",
 *   secure = true,
 *   name = "createEntity",
 *   type = "CreateEntityOutput",
 *   nullable = false,
 *   cache_tags = {"entity_types", "entity_bundles"},
 *   deriver = "\Drupal\graphql_content_mutation\Plugin\Deriver\CreateEntityDeriver"
 * )
 */
class CreateEntity extends MutationPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    $entityTypeId = $this->pluginDefinition['entity_type'];
    $bundleName = $this->pluginDefinition['entity_bundle'];
    $bundleKey = $this->entityTypeManager->getDefinition($entityTypeId)->getKey('bundle');
    $storage = $this->entityTypeManager->getStorage($entityTypeId);

    // The raw input needs to be converted to use the proper field and property
    // keys because we usually convert them to camel case when adding them to
    // the schema.
    $inputArgs = $args['input'];
    /** @var \Drupal\graphql_content_mutation\Plugin\GraphQL\InputTypes\EntityInput $inputType */
    $inputType = $this->config->getArgument('input')->getType()->getNamedType();
    $input = $this->extractEntityInput($inputArgs, $inputType);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $storage->create($input + [
      $bundleKey => $bundleName,
    ]);

    if (!$entity->access('create')) {
      return new CreateEntityOutputWrapper(NULL, NULL, [
        $this->t('You do not have the necessary permissions to create entities of this type.'),
      ]);
    }

    if (($violations = $entity->validate()) && $violations->count()) {
      return new CreateEntityOutputWrapper(NULL, $violations);
    }

    if (($status = $entity->save()) && $status === SAVED_NEW) {
      return new CreateEntityOutputWrapper($entity);
    }

    return NULL;
  }

  /**
   * Extract entity values from the resolver args.
   *
   * Loops over all input values and assigns them to their original field names.
   *
   * @param array $inputValue
   *   The entity values provided through the resolver args.
   * @param \Drupal\graphql_content_mutation\Plugin\GraphQL\InputTypes\EntityInput $inputType
   *   The input type.
   * @return array
   *   The extracted entity values with their proper, internal field names.
   */
  protected function extractEntityInput(array $inputValue, EntityInput $inputType) {
    $fields = $inputType->getPluginDefinition()['fields'];
    return array_reduce(array_keys($inputValue), function($carry, $current) use ($fields, $inputValue, $inputType) {
      $isMulti = $fields[$current]['multi'];
      $fieldName = $fields[$current]['field_name'];
      $fieldValue = $inputValue[$current];
      $fieldType = $inputType->getField($current)->getType()->getNamedType();

      if ($fieldType instanceof AbstractScalarType) {
        return $carry + [$fieldName => $fieldValue];
      }

      if ($fieldType instanceof EntityInputField) {
        $fieldValue = $isMulti ? array_map(function ($value) use ($fieldType) {
          return $this->extractEntityFieldInput($value, $fieldType);
        }, $fieldValue) : $this->extractEntityFieldInput($fieldValue, $fieldType);

        return $carry + [$fieldName => $fieldValue];
      }

      return $carry;
    }, []);
  }

  /**
   * Extract property values from field values from the resolver args.
   *
   * Loops over all field properties and assigns them to their original property
   * names.
   *
   * @param array $fieldValue
   *   The field values keyed by property name.
   * @param \Drupal\graphql_content_mutation\Plugin\GraphQL\InputTypes\EntityInputField $fieldType
   *   The field type.
   * @return array
   *   The extracted field values with their proper, internal property names.
   */
  protected function extractEntityFieldInput(array $fieldValue, EntityInputField $fieldType) {
    $properties = $fieldType->getPluginDefinition()['fields'];
    return array_reduce(array_keys($fieldValue), function ($carry, $current) use ($properties, $fieldValue) {
      $key = $properties[$current]['property_name'];
      $value = $fieldValue[$current];

      return $carry + [$key => $value];
    }, []);
  }

}
