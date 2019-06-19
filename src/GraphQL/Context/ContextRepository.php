<?php

namespace Drupal\graphql\GraphQL\Context;

use Drupal\Core\Plugin\Context\LazyContextRepository;

// TODO: When inside a graphql query, do not cache contexts.
class ContextRepository extends LazyContextRepository {

}
