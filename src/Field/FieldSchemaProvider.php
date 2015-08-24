<?php

/**
 * @file
 * Contains \Drupal\graphql\Field\FieldSchemaProvider.
 */

namespace Drupal\graphql\Field;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\EntitySchemaProviderInterface;
use Fubhy\GraphQL\Language\Node;

/**
 * Generates a GraphQL Schema for content entity fields.
 */
class FieldSchemaProvider extends FieldSchemaProviderBase {
  /**
   * Unsorted list of schema providers nested and keyed by priority.
   *
   * @var array
   */
  protected $providers;

  /**
   * Sorted list of schema providers.
   *
   * @var array
   */
  protected $sortedProviders;

  /**
   * List of resolved field schema providers keyed by field definition.
   *
   * @var \SplObjectStorage
   */
  protected $resolvedProviders;

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema(EntitySchemaProviderInterface $entity_schema_provider, FieldDefinitionInterface $field_definition) {
    return $this
      ->getFirstApplicableProvider($field_definition)
      ->getQuerySchema($entity_schema_provider, $field_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getMutationSchema(EntitySchemaProviderInterface $entity_schema_provider, FieldDefinitionInterface $field_definition) {
    return $this
      ->getFirstApplicableProvider($field_definition)
      ->getMutationSchema($entity_schema_provider, $field_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function applies(FieldDefinitionInterface $field_definition) {
    return FALSE;
  }

  /**
   * Adds a active theme negotiation service.
   *
   * @param \Drupal\graphql\Field\FieldSchemaProviderInterface $provider
   *   The schema provider to add.
   * @param int $priority
   *   Priority of the schema provider.
   */
  public function addFieldSchemaProvider(FieldSchemaProviderInterface $provider, $priority = 0) {
    $this->providers[$priority][] = $provider;
    $this->sortedProviders = NULL;
    $this->resolvedProviders = NULL;
  }

  /**
   * @param FieldDefinitionInterface $field_definition
   * @return \Drupal\graphql\Field\FieldSchemaProviderInterface|null
   */
  protected function getFirstApplicableProvider(FieldDefinitionInterface $field_definition) {
    if (!isset($this->resolvedProviders)) {
      $this->resolvedProviders = new \SplObjectStorage();
    }

    if (!$this->resolvedProviders->offsetExists($field_definition)) {
      foreach ($this->getSortedProviders() as $provider) {
        if ($provider->applies($field_definition)) {
          $this->resolvedProviders->offsetSet($field_definition, $provider);
          return $provider;
        }
      }

      // No provider applies.
      $this->resolvedProviders->offsetSet($field_definition, NULL);
    }

    return $this->resolvedProviders->offsetGet($field_definition);
  }

  /**
   * Returns the sorted array of field schema providers.
   *
   * @return \Drupal\graphql\Field\FieldSchemaProviderInterface[]
   *   An array of field schema provider objects.
   */
  protected function getSortedProviders() {
    if (!isset($this->sortedProviders)) {
      krsort($this->providers);

      $this->sortedProviders = [];
      foreach ($this->providers as $providers) {
        $this->sortedProviders = array_merge($this->sortedProviders, $providers);
      }
    }

    return $this->sortedProviders;
  }
}
