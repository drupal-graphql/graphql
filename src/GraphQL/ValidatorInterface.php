<?php

namespace Drupal\graphql\GraphQL;

use Drupal\graphql\Entity\ServerInterface;

/**
 * Validation service interface for Drupal GraphQL servers.
 */
interface ValidatorInterface {

  /**
   * Validates the schema of the server.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $server
   *   The server to validate.
   *
   * @return \GraphQL\Error\Error[]
   *   An array of validation errors.
   */
  public function validateSchema(ServerInterface $server) : array;

  /**
   * Get a list of missing resolvers.
   *
   * A resolver is considered missing if a field definition exists in the SDL
   * (.graphqls) files for this server but the type or any of its implemented
   * interfaces do not have a registered resolver in the server's resolver
   * registry for the field.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $server
   *   The server to validate.
   * @param array $ignore_types
   *   Any types to ignore during validation.
   *
   * @return array
   *   An array keyed by type containing arrays of field names.
   */
  public function getMissingResolvers(ServerInterface $server, array $ignore_types = []) : array;

  /**
   * Get a list of orphaned resolvers.
   *
   * A resolver is considered orphaned if it's defined in the resolver registry
   * for the server but the field does not occur in any SDL (.graphqls) files.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $server
   *   The server to validate.
   * @param array $ignore_types
   *   Any types to ignore during validation.
   *
   * @return array
   *   An array keyed by type containing arrays of field names.
   */
  public function getOrphanedResolvers(ServerInterface $server, array $ignore_types = []) : array;

}
