# Composing data producers

Often you will find that you need to call multiple producers in a sequence in order to get the data you actually want. Maybe because you call a producer that only returns an `id` and then you need an `entity_load` producer to use that `id` to return the actual entity, or maybe a route that returns a URL Object and then you want to take that URL can get the entity out of it using the `route_entity` data producer.

This can be accomplished using some of the built-in helpers inside the `$builder` object called `compose`. given our example for the previous example `current_user`, this is how it works :

```php
$registry->addFieldResolver('Query', 'currentUser', $builder->compose(
  $builder->produce('current_user'),
  $builder->produce('entity_load')
    ->map('type', $builder->fromValue('user'))
    ->map('id', $builder->fromParent())
));
```

We are chaining the two data producers together here, one after the other and calling `fromParent` will give us the result that was returned in the previous step.

## Debugging composed chains

A `compose()` chain is executed by [`Composite`](../../src/GraphQL/Resolver/Composite.php). Each step receives the value returned by the previous step; the loop walks that list until the final result is produced.

### Where to break in `Composite`

Set an Xdebug breakpoint in `Composite::resolve()` on the line **inside the `while` loop** where the next resolver runs—[`$value = $resolver->resolve(...)`](../../src/GraphQL/Resolver/Composite.php) (around line 46 in the graphql module sources).

- **Before a step runs:** when execution stops on that line, `$value` is the output of the previous composed resolver (or the initial parent value for the first step), and `$resolver` is the resolver about to run (often a `DataProducerProxy` for the next producer).
- **After a step runs:** step once (or break on the next line) and inspect the new `$value`, which is that step’s return value before the loop continues.

If a step returns a `SyncPromise`, execution may continue asynchronously via `DeferredUtility::returnFinally`; in that case you may need to break again when the deferred continuation re-enters `Composite::resolve()` for the remainder of the chain.

### Narrowing to one field and parent type

The same `Composite` instance can be shared only indirectly, but every invocation receives GraphQL resolution metadata. Use a **conditional breakpoint** so you only stop for the field you care about:

- **Field name** (API name of the field being resolved): `$field->getFieldName() === 'heroImage'`
  `FieldContext::getFieldName()` delegates to [`ResolveInfo::$fieldName`](https://webonyx.github.io/graphql-php/class-reference/#graphqltypedefinitionresolveinfo).
- **Parent GraphQL type** (the type of the object that owns the field): `$info->parentType->name === 'Article'`
  For the root `Query` / `Mutation` fields, the parent type is typically that operation type’s name (`Query`, `Mutation`, etc.).

Combine them when both matter, for example:

```text
$field->getFieldName() === 'heroImage' && $info->parentType->name === 'Article'
```

You can add further conditions on `$value` (entity id, bundle, etc.) once you know what the parent passes into the chain—useful when the same composed chain is registered for several fields but you only want to stop for one case.
