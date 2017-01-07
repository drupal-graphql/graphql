# Contributing

## Contributing code

For some time, development will happen on GitHub using the pull request model:
in case you are not familiar with that, please take a few minutes to read the
[GitHub article](https://help.github.com/articles/using-pull-requests) on using
pull requests.

There are a few conventions that should be followed when contributing:

* Always create an issue in the [drupal.org GraphQL issue queue](https://www.drupal.org/project/issues/graphql)
  for every pull request you are working on.
* Always cross-reference the Issue in the Pull Request and the Pull Request in
  the issue.
* Always create a new branch for every pull request: its name should contain a
  brief summary of the ticket and its issue id, e.g **readme-2276369**.
* Try to keep the history of your pull request as clean as possible by squashing
  your commits: you can look at the [Symfony documentation](http://symfony.com/doc/current/cmf/contributing/commits.html)
  or at the [Git book](http://git-scm.com/book/en/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages)
  for more information on how to do that.

## Contributing documentation

Documentation is maintained in the `doc` directory in [GitBook] format, so it 
can be edited just like code issues with the pull request process.

[GitBook]: https://www.gitbook.com/

To check a local copy of documentation while working on it, install Gitbook locally, and type:

    $ cd (your_drupal_path)/modules/contrib/graphql
    $ gitbook serve

You then have documentation available on `(your_drupal_url):4000` with 
live-reload when you edit it.
