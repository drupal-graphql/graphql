# Writing custom mutations

Not all mutations are directly related to an entity, and often you might need to perform operations on a mutation that are not necessarily creating, updating or deleting an entity in Drupal. For these cases you can use the `MutationPluginBase` plugin and extend that instead of extending the `CreateEntityBase` as we saw on _Creating mutations for Entities_.

The mutation itself wouldn't be too different from what we did previously, you can see an example in the [Examples repo](https://github.com/drupal-graphql/graphql-examples/blob/master/src/Plugin/GraphQL/Mutations/FileUpload.php) of a file upload mutation.

## Resolve method

One important method of the MutationPluginBase is the resolve method where we, similar to our "extractEntityInput" above, get access to the arguments passed to the mutation and we can then perform the operation we want on Drupal.

Let's look at an example that will perform an operation of buying a car. The operation itself exists on a service so it's not really important to look at the details of that operation, but what is important is that in the resolve method we take the `car` from our arguments \(defined in the annotation as seen above\) and we call our `garage` service and pass it the car :

```php
<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Mutations;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Mutations\MutationPluginBase;
use Drupal\graphql_plugin_test\GarageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
* A test mutation.
*
* @GraphQLMutation(
*   id = "buy_car",
*   secure = true,
*   name = "buyCar",
*   type = "Car",
*   arguments = {
*     "car" = "CarInput!"
*   }
* )
*/
class BuyCar extends MutationPluginBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
    * The garage.
    *
    * @var \Drupal\graphql_plugin_test\GarageInterface
    */
  protected $garage;

  /**
    * {@inheritdoc}
    */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static($configuration, $pluginId, $pluginDefinition, $container->get('graphql_test.garage'));
  }

  /**
    * BuyCar constructor.
    *
    * @param array $configuration
    *   The plugin configuration array.
    * @param string $pluginId
    *   The plugin id.
    * @param mixed $pluginDefinition
    *   The plugin definition array.
    * @param \Drupal\graphql_plugin_test\GarageInterface $garage
    *   The garage service.
    */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, GarageInterface $garage) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->garage = $garage;
  }
  /**
    * {@inheritdoc}
    */
  public function resolve($value, array $args, ResolveContext $context, ResolveInfo $info) {
    return $this->garage->insertVehicle($args['car']);
  }
}
```

This example was taken from the [a test](https://github.com/drupal-graphql/graphql/blob/188be525a007f385a3d3c4f8d2900b62a0150a5f/tests/modules/graphql_plugin_test/src/Plugin/GraphQL/Mutations/BuyCar.php) inside the graphql repository. Inside the resolve method it could be doing other things.
