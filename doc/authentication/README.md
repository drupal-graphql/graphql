# Authentication

When it comes to authentication the Drupal GraphQL module is very much independent of what kind of authentication system or technique you use, as long as in the end you are able to send a token via the `Authorization` header.

Drupal has some modules for doing decoupled authentiation using tokens :

* [Simple oauth](https://www.drupal.org/project/simple_oauth)
* [JWT](https://www.drupal.org/project/jwt)

## Bearer token

To authenticated a graphql request with Drupal you need to attach the token you get with those modules as a `Bearer` in the Authorization header. Here is how that can look like when doing a fetch call :

```javascript
...
fetch(url, {
  method: 'post',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: '...'
})
  .then(json)
  ...
```

Once a request goes with that token the user will be authenticated and Drupal's permission system and access checking will work using that user.

## Basic auth

Most recently Drupal's own Basic auth module support was also added so you can aslo use that to authenticated queries to drupal.
