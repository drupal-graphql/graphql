The following examples should help accessing images and their derivatives based on image styles:

## Add the schema declaration

```gql
type Article implements NodeInterface {
...
  image_url: String
  image_alt: String
  image_thumbnail: Image
  image_large: Image
...
}

type Image {
  url: String
  width: Int
  height: Int
}
```

## Adding resolvers

```php
    $registry->addFieldResolver('Article', 'image_url',
      $builder->compose(
        $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:file'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_image.entity')),
        $builder->produce('image_url')
          ->map('entity', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('Article', 'image_alt',
      $builder->produce('property_path')
        ->map('type', $builder->fromValue('entity:node'))
        ->map('value', $builder->fromParent())
        ->map('path', $builder->fromValue('field_image.alt'))
    );

    $registry->addFieldResolver('Article', 'image_thumbnail',
      $builder->compose(
        $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:file'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_image.entity')),
        $builder->produce('image_derivative')
          ->map('entity', $builder->fromParent())
          ->map('style', $builder->fromValue('thumbnail'))
      )
    );

    $registry->addFieldResolver('ProductDetailPage', 'image_large',
      $builder->compose(
        $builder->produce('property_path')
          ->map('type', $builder->fromValue('entity:file'))
          ->map('value', $builder->fromParent())
          ->map('path', $builder->fromValue('field_image.entity')),
        $builder->produce('image_derivative')
          ->map('entity', $builder->fromParent())
          ->map('style', $builder->fromValue('large'))
      )
    );
```
