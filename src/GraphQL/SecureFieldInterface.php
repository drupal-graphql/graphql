<?php

namespace Drupal\graphql\GraphQL;

/**
 * Interface for marking fields as "secure".
 *
 * A field marked as "secure" is safe to be invoked used by untrusted consumers.
 *
 * The consumer is considered trusted if:
 *
 * - The user session has the "bypass graphql field security" permission.
 * - The query is persisted and therefore controlled by the host.
 * - GraphQL development mode is enabled.
 * - The processor is instanced manually with the $secure argument set to TRUE.
 */
interface SecureFieldInterface {

  /**
   * Check if the field is considered secure.
   *
   * @return boolean
   *   Boolean value if the field is considered secure or not.
   */
  public function isSecure();
}