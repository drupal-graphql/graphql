<?php

namespace Drupal\graphql\GraphQL\Relay\Field;

use Drupal\graphql\GraphQL\Relay\Type\NodeInterfaceType;
use Drupal\graphql\TypeResolver\TypeResolverWithRelaySupportInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Field\InputField;
use Youshido\GraphQL\Relay\Node;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\IdType;

class NodeField extends AbstractField implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * The node interface type.
   *
   * @var \Drupal\graphql\GraphQL\Relay\Type\NodeInterfaceType
   */
  protected $type;

  /**
   * Constructs a NodeField object.
   */
  public function __construct() {
    $this->type = new NodeInterfaceType();

    // By passing an empty config array to the parent we get some automatically
    // derived values set.
    parent::__construct([]);
  }

  /**
   * {@inheritdoc}
   */
  public function build(FieldConfig $config) {
    $config->addArgument(new InputField([
      'name' => 'id',
      'type' => new NonNullType(new IdType()),
      'description' => 'The ID of an object.',
    ]));
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return 'Fetches an object given its ID.';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    list($type, $id) = Node::fromGlobalId($args['id']);

    /** @var \Drupal\graphql\TypeResolver\TypeResolverInterface $typeResolver */
    $typeResolver = $this->container->get('graphql.type_resolver');
    if ($typeResolver instanceof TypeResolverWithRelaySupportInterface) {
      if ($typeResolver->canResolveRelayNode($type, $id)) {
        return $typeResolver->resolveRelayNode($type, $id);
      }
    }

    return NULL;
  }
}
