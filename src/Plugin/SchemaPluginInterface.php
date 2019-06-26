<?php

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;
use GraphQL\Server\OperationParams;

interface SchemaPluginInterface extends PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * Retrieves the schema.
   *
   * @return \GraphQL\Type\Schema
   *   The schema.
   */
  public function getSchema();

  /**
   * Validates the schema.
   *
   * @return null|array
   */
  public function validateSchema();

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
   *   The root value for query execution or a callable factory.
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
   *   The context object for query execution or a callable factory.
   */
  public function getContext();

  /**
   * Returns the error formatter.
   *
   * Allows to replace the default error formatter with a custom one. It is
   * essential when there is a need to adjust error format, for instance
   * to add an additional fields or remove some of the default ones.
   *
   * @see \GraphQL\Error\FormattedError::prepareFormatter
   *
   * @return mixed|callable
   *   The error formatter.
   */
  public function getErrorFormatter();

  /**
   * Returns the error handler.
   *
   * Allows to replace the default error handler with a custom one. For example
   * when there is a need to handle specific errors differently.
   *
   * @see \GraphQL\Executor\ExecutionResult::toArray
   *
   * @return mixed|callable
   *   The error handler.
   */
  public function getErrorHandler();

  /**
   * Returns the default field resolver.
   *
   * Fields that don't explicitly declare a field resolver will use this one
   * as a fallback.
   *
   * @return null|callable
   *   The default field resolver.
   */
  public function getFieldResolver();

  /**
   * @param \GraphQL\Server\OperationParams $params
   *
   * @return string|null
   */
  public function getOperationLanguage(OperationParams $params);

}
