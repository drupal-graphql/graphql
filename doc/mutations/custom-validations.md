# Writing custom validations

One aspect that is important to consider when creating mutations is providing good error messages and validations. Often you will be connecting these mutations to forms or other types of UI that should give the user clear indication of what went wrong. Access checks and permissions are also important to consider when creating mutations for your entities.

## CreateEntitybase plugin access

The `CreateEntityBase` plugin does a entity access check in it's resolveOutput method, so it will validate if a user is trying to create an entity it does not have access to and fail if that happens with a message : **"You do not have the necessary permissions to create entities of this type."**

However you might have some other logic you want to perform, for example check that a user has done something else before he can perform this action, some kind of custom validation or a simple field access check, so that maybe a user that has no access to a particular field give his role fails accordingly.

You can make custom validations by implementing your own `resolveOutput` method inside your mutation.

## Custom validations - errors and violations

Graphql mutations by default return 3 things :

* data - The data that was returned by the mutation. what the consumer of the mutation asked for when running it \(if successful\)
* errors - If an error occurred in Drupal \(an exception\) it will be added to the errors array.
* violations - Violations are a useful way to provide error messages to users, nothing "crashed" but something went wrong and the user can't do the operation. Maybe he has no access or something else.

### Adding custom information to errors

To add things to the errors for example when creating an entity you can return a new `EntityCrudOutputWrapper`, e.g. :

```php
if (!$entity->access('create')) {
     return new EntityCrudOutputWrapper(NULL, NULL, [
       $this->t('You do not have the necessary permissions to create entities of this type.'),
     ]);
}
```

In this case if the user has no access to create on this entity its going to fail. You can make your own logic inside resolve or resolveOutput to output your own information and logic to users.

### Adding custom violations

To add violations the process is very similar, you need to return a new `EntityCrudOutputWrapper`, you can decide based on your own situation if the entity should or not be returned \(or if even should or not be processed and created\) but the second argument to this `EntityCrudOutputWrapper` where we passed NULL previously is a `Violations` array of type `ConstraintViolationList` from Symphony. Check the [Drupal information on ConstraintViolationList](https://api.drupal.org/api/drupal/vendor!symfony!validator!ConstraintViolationList.php/8.2.x) as well as [ConstraintViolation](https://api.drupal.org/api/drupal/vendor!symfony!validator!ConstraintViolation.php/class/ConstraintViolation/8.2.x)

There are a couple imporant pieces in ContrainstViolations you can use that are output by the graphql module to the user in the `violations` array :

* code - Can indicate the type of violation
* message - a clear message for the user of what went wrong
* path - The path \(field or other part\) where the violation occurred

See the following error for an example of a situation where a user tries updating an entity which he has access to but not a particular field :

```json
{
 "data": {
   "addCredit": {
     "entity": null,
     "violations": [
       {
         "code": "403",
         "message": "Access denied",
         "path": "field_credit_status"
       }
     ],
     "errors": [
       "You do not have the necessary permissions to create some fields for this entity."
     ]
   }
 }
}
```

In this case it was decided to fail creating the entity `Credit` because the user does not have access to fields he is trying to create, but instead of only providing the generic message : _"You do not have the necessary permissions to create some fields for this entity."_ some extra information is added specifying which exact fields failed and why.
