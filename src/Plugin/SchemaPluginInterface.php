<?php

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;

interface SchemaPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Retrieves the schema.
   *
   * @return \GraphQL\Type\Schema
   *   The schema.
   */
  public function getSchema();

  /**
   * Returns whether the schema allows query batching.
   *
   * @return boolean
   */
  public function allowsQueryBatching();

  /**
   * Returns whether the schema should output debugging information.
   *
   * Returning TRUE will add detailed error information to any error messages
   * returned during query execution.
   *
   * @return boolean
   */
  public function inDebug();

  /**
   * Returns to root value to use when resolving queries against the schema.
   *
   * May return a callable to resolve the root value at run-time based on the
   * provided query parameters / operation.
   *
   * @code
   *
   * public function getRootValue() {
   *   return function (OperationParams $params, DocumentNode $document, $operation) {
   *     // Dynamically return a root value based on the current query.
   *   };
   * }
   *
   * @endcode
   *
   * @return mixed|callable
   */
  public function getRootValue();

  /**
   * Returns the context object to use during query execution.
   *
   * May return a callable to instantiate a context object for each individual
   * query instead of a shared context. This may be useful e.g. when running
   * batched queries where each query operation within the same request should
   * use a separate context object.
   *
   * The returned value will be passed as an argument to every type and field
   * resolver during execution.
   *
   * @code
   *
   * public function getContext() {
   *   $shared = ['foo' => 'bar'];
   *
   *   return function (OperationParams $params, DocumentNode $document, $operation) use ($shared) {
   *     $private = ['bar' => 'baz'];
   *
   *     return new MyContext($shared, $private);
   *   };
   * }
   *
   * @endcode
   *
   * @return mixed|callable
   */
  public function getContext();

  /**
   * Returns the default field resolver.
   *
   * Fields that don't explicitly declare a field resolver will use this one
   * as a fallback.
   *
   * @return null|callable
   */
  public function getFieldResolver();

  /**
   * Returns the validation rules to use for the query.
   *
   * May return a callable to allow the schema to decide the validation rules
   * independently for each query operation.
   *
   * @code
   *
   * public function getValidationRules() {
   *   return function (OperationParams $params, DocumentNode $document, $operation) {
   *     if (isset($params->queryId)) {
   *       // Assume that pre-parsed documents are already validated. This allows
   *       // us to store pre-validated query documents e.g. for persisted queries
   *       // effectively improving performance by skipping run-time validation.
   *       return [];
   *     }
   *
   *     return array_values(DocumentValidator::defaultRules());
   *   };
   * }
   *
   * @endcode
   *
   * @return array|callable
   */
  public function getValidationRules();

  /**
   * Returns a callable for loading persisted queries.
   *
   * @return callable
   */
  public function getPersistedQueryLoader();

}
