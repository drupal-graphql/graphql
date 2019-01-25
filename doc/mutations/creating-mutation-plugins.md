# Creating mutations for Entities
The graphql module uses the [Drupal plugin system](https://www.drupal.org/docs/8/api/plugin-api/plugin-api-overview) for a lot of the extensibility features of plugins. So a lot of the times when you want to extend the graphql module (for examply when creating your own mutations) you will be using the plugin system and creating your own plugins.

## Why the automatic mutations where removed. 

In [this article](https://www.amazeelabs.com/en/blog/extending-graphql-part-3-mutations) its well explained why automatic mutations where removed. But this, as stated in the article, doesn't mean that creating mutations is complicated. In fact, its a simple task and one that might even provide with the extra flexibility you know and love from Drupal.

## Mutations to create Drupal Entities

So lets have a look at how you can create a mutation from scratch to generate a new entity of type node, and in this case a new article. You can refer to the [Examples](https://github.com/drupal-graphql/graphql-examples) repository to look at some other examples as well for how to create other kinds of mutations.

### CreateArticle Plugin

The first step to create a mutation is to make the plugin, the graphql module provides a base class for creating new entities called **CreateEntityBase**. You should use implement a plugin that extends this class when you want to create an entity directly without too much custom things in it.

lets look at what the code for this plugin looks like : 

```
<?php
namespace Drupal\graphql_examples\Plugin\GraphQL\Mutations;
use Drupal\graphql\Annotation\GraphQLMutation;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\CreateEntityBase;
use GraphQL\Type\Definition\ResolveInfo;
/**
 * Simple mutation for creating a new article node.
 *
 * @GraphQLMutation(
 *   id = "create_article",
 *   entity_type = "node",
 *   entity_bundle = "article",
 *   secure = true,
 *   name = "createArticle",
 *   type = "EntityCrudOutput!",
 *   arguments = {
 *     "input" = "ArticleInput"
 *   }
 * )
 */
class CreateArticle extends CreateEntityBase {
  /**
   * {@inheritdoc}
   */
  protected function extractEntityInput(
    $value,
    array $args,
    ResolveContext $context,
    ResolveInfo $info
  ) {
    return [
      'title' => $args['input']['title'],
      'body' => $args['input']['body'],
    ];
  }
}
```


We can see a couple things here in this code that are particular interesting : 

### GraphQLMutation anotations

The graphql module uses anotations for classes in order to have some information define the mutation in a simple way, things like : 

*  id - The id of the mutation.
*  entity_type - The type of entity that is going to be created from this mutation (only important for when extending CreateEntityBase mutations)
*  entity_bundle - The bundle of the entity that is going to be created
*  secure - Fields that are not marked secure are automatically blocked in untrusted environments. For example there is a field that allows to fetch content from a remote url, which would basically turn your website into a proxy for anybody. This field will only work with a certain user permission or in persisted queries, where we are in control of what they do. The other way around, a field that is marked as secure doesn't allow any operations drupal itself wouldn't.
*  name - The name for the mutation. This name is what you will use when calling the mutation.
*  type - the "type" is the returned type by the mutation.  In the example above the mutation returns a "EntityCrudOutput" type which is provided by the graphql module itself.
*  arguments - The arguments passed to the mutation. These are the fields for the entity you want to create, in the case above we are passing one argument called "Input" of type "ArticleInput". We will look at InputTypes afterwards. But essentially since graphql is strickly typed we want to provide information for types for each field we pass to the mutation we can do that using "InputTypes".

### extractEntityInput method

There is one method you should always implement when doing mutations, that is the extractEntityInput method which will be sort of a mapping between the arguments you pass to the mutation and the fields that drupal expects to receive for this mutation.

We can see we are assigning the "title" that we are passing in the input (we will look at the ArticleInput after) to the title property in the entity, same for the body.

## Mutations to update Drupal Entities

Lets continue with our article example. In this case we implement a mutation to update a given article. Because we are updating a particular entity and we need to know which entity it is, we will need to provide the plugin anotation with something extra, an Id for the entity.

```
<?php
namespace Drupal\graphql_examples\Plugin\GraphQL\Mutations;
use Drupal\graphql\Annotation\GraphQLMutation;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\UpdateEntityBase;
use GraphQL\Type\Definition\ResolveInfo;
/**
 * Simple mutation for updating an existing article node.
 *
 * @GraphQLMutation(
 *   id = "update_article",
 *   entity_type = "node",
 *   entity_bundle = "article",
 *   secure = true,
 *   name = "updateArticle",
 *   type = "EntityCrudOutput!",
 *   arguments = {
 *     "id" = "String",
 *     "input" = "ArticleInput"
 *   }
 * )
 */
class UpdateArticle extends UpdateEntityBase {
  /**
   * {@inheritdoc}
   */
  protected function extractEntityInput(
    $value,
    array $args,
    ResolveContext $context,
    ResolveInfo $info
  ) {
    return array_filter([
      'title' => $args['input']['title'],
      'body' => $args['input']['body'],
    ]);
  }
}
```

The first thing we noticed is we are now using "UpdateEntityBase" instead of "CreateEntityBase" as our parent class,
we can also see that we use the same argument "Input" as above but we also have another argument called "id". The Graphql Module will be smart enough to use that id to match to the right entity.

## Mutations to Delete Drupal Entities

The only thing left now is really to delete the entity right? This is the simples type of operation out of the 3, because we only need to give graphql the id, it will check if we can access that type of operation and if so delete the entity with the id we give to it. So lets look at how the plugin looks like 

```
<?php
namespace Drupal\graphql_examples\Plugin\GraphQL\Mutations;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\DeleteEntityBase;
/**
 * Simple mutation for deleting an article node.
 *
 * @GraphQLMutation(
 *   id = "delete_article",
 *   entity_type = "node",
 *   entity_bundle = "article",
 *   secure = true,
 *   name = "deleteArticle",
 *   type = "EntityCrudOutput!",
 *   arguments = {
 *     "id" = "String"
 *   }
 * )
 */
class DeleteArticle extends DeleteEntityBase {
}
```

We can see that we use extend a "DeleteEntityBase" class and we only pass one argument, the id of the entity we want to delete, together with some of the anotations as we did previously with other mutations.

## ArticleInput

So we know we need to define the arguments for mutations, much like a function it receives arguments and they are used to do whatever our mutation needs. In order for graphql to know information about the Arguments we create an "InputType". The ArticleInput that we used above looks like this : 

```
<?php
namespace Drupal\graphql_examples\Plugin\GraphQL\InputTypes;
use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;
/**
 * The input type for article mutations.
 *
 * @GraphQLInputType(
 *   id = "article_input",
 *   name = "ArticleInput",
 *   fields = {
 *     "title" = "String",
 *     "body" = {
 *        "type" = "String",
 *        "nullable" = "TRUE"
 *     }
 *   }
 * )
 */
class ArticleInput extends InputTypePluginBase {
}
```

We can see above that we only use anotations here to define the arguments inside the "Input" property. So we know that it receives a "title" and thats a string and we also receive a "body" which is also a "String".
