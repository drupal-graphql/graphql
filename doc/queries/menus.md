# Querying menus

Another common use case you will run need is to query a menu, this is particularly useful for use cases where you will want to control or add items via the backend in the drupal administration interface.

## Add the schema declaration

The first step as seen in the introduction is to add the types and fields in the schema. We can do this directly in the schema string in your own schema implementation.

```
...
type Query {
    ...
    menu(name: String!): Menu
    ...
}

type Menu {
  name: String!
  items: [MenuItem]
}

type MenuItem {
  title: String!
  url: Url!
  children: [MenuItem]
}

type Url {
  path: String
}

...

```
In this schema we can see that we can query a menu by its name. This will return a "Menu" type which includes a name field as well as a list of "MenuItem".

## Adding resolvers

To add the resolvers we go to our schema implementation and call the appropriate data producers inside the `getResolverRegistry` method.

```php
/**
   * {@inheritdoc}
   */
  protected function getResolverRegistry() {
    ...

     // Menu query.
    $registry->addFieldResolver('Query', 'menu',
      $builder->produce('entity_load', [
        'mapping' => [
          'type' => $builder->fromValue('menu'),
          'id' => $builder->fromArgument('name'),
        ],
      ])
    );

    // Menu name.
    $registry->addFieldResolver('Menu', 'name',
      $builder->produce('property_path', [
        'mapping' => [
          'type' => $builder->fromValue('entity:menu'),
          'value' => $builder->fromParent(),
          'path' => $builder->fromValue('label'),
        ],
      ])
    );

    // Menu items.
    $registry->addFieldResolver('Menu', 'items',
      $builder->produce('menu_links', [
        'mapping' => [
          'menu' => $builder->fromParent(),
        ],
      ])
    );

    // Menu title.
    $registry->addFieldResolver('MenuItem', 'title',
      $builder->produce('menu_link_label', [
        'mapping' => [
          'link' => $builder->produce('menu_tree_link', [
            'mapping' => [
              'element' => $builder->fromParent(),
            ],
          ]),
        ],
      ])
    );

    // Menu children.
    $registry->addFieldResolver('MenuItem', 'children',
      $builder->produce('menu_tree_subtree', [
        'mapping' => [
          'element' => $builder->fromParent(),
        ],
      ])
    );

    // Menu url.
    $registry->addFieldResolver('MenuItem', 'url',
      $builder->produce('menu_link_url', [
        'mapping' => [
          'link' => $builder->produce('menu_tree_link', [
            'mapping' => [
              'element' => $builder->fromParent(),
            ],
          ]),
        ],
      ])
    );

    $registry->addFieldResolver('Url', 'path',
      $builder->produce('url_path', [
        'mapping' => [
          'url' => $builder->fromParent(),
        ],
      ])
    );

    ...

    return $registry;
  }
```

As we see here we need to really provide all the fields with a resolver in order to have the data available, this can seem a bit unnecessary at first but its a crucial step towards a cleaner API. In this case we resolve the `menu` inside the `Query` type and from there we start resolving each field from `Menu` and also `MenuItem`.
