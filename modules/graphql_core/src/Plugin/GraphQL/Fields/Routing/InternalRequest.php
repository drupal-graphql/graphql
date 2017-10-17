<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\graphql\Annotation\GraphQLField;
use Drupal\graphql\Plugin\GraphQL\Fields\SubrequestFieldBase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Issue an internal request and retrieve the response object.
 *
 * @GraphQLField(
 *   id = "internal_request",
 *   secure = true,
 *   name = "request",
 *   type = "InternalResponse",
 *   parents = {"InternalUrl"}
 * )
 */
class InternalRequest extends SubrequestFieldBase {

  /**
   * {@inheritdoc}
   *
   * TODO: Consider implementing this by just executing the controller instead
   * of issuing another subrequest.
   */
  protected function resolveSubrequest($value, array $args, ResolveInfo $info) {
    $request = $this->requestStack->getCurrentRequest()->duplicate();
    $request->attributes->set('_controller', $request->get('_graphql_controller'));

    $request->attributes->remove('graphql_subrequest');
    $request->attributes->remove('_graphql_controller');

    $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);

    // TODO:
    // Remove the request stack manipulation once the core issue described at
    // https://www.drupal.org/node/2613044 is resolved.
    while ($this->requestStack->getCurrentRequest() === $request) {
      $this->requestStack->pop();
    }

    return $response;
  }

}