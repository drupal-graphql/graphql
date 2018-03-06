## GraphQL Queries

Once you enable the module, if you want to see a simple query execute your browser, enable the `execute arbitrary queries` permission for the anonymous user and copy/paste the following into your browser.

```
[YOUR DOMAIN]/graphql?query=query{%20user:%20currentUserContext{%20uid,%20uuid%20}%20}
``` 

This would return a result similar to: 

```
{"data":{"user":{"uid":1,"uuid":"4e9af657-689b-4c06-8721-e267914f2255"}}}
```