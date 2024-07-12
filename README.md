# Kaupunkitieto

Kaupunkitieto is a website project for the City of Helsinki's statistics and research unit. Technical implementation is based on the main City of Helsinki platform. Custom functionalities are added on top of it to match requirements for the kaupunkitieto instance.

## Environments

Env | Branch   | URL
--- |----------| ----------- 
dev | *        |  https://kaupunkitieto.docker.sh/
test | dev      |  https://drupal-kaupunkitieto.test.hel.ninja/
staging | dev      |  https://drupal-kaupunkitieto.stage.hel.ninja/
production | main | https://kaupunkitieto.hel.fi/

## Requirements

You need to have these applications installed to operate on all environments:

- [Docker](https://github.com/druidfi/guidelines/blob/master/docs/docker.md)
- [Stonehenge](https://github.com/druidfi/stonehenge)

## Create and start the environment

For the first time (new project):

``
$ make new
``

And following times to create and start the environment:

``
$ make fresh
``

NOTE: Change these according of the state of your project.

## Login to Drupal container

This will log you inside the app container:

```
$ make shell
```

## Additional resources
| Name | URL                                                       | Description |
| --- |-----------------------------------------------------------| --- |
HELfi drupal -platform | https://github.com/City-of-Helsinki/drupal-helfi-platform | Base installation
HDBT-theme | https://github.com/City-of-Helsinki/drupal-hdbt           | Base theme
HDBT Admin theme | https://github.com/City-of-Helsinki/drupal-hdbt-admin     | Admin theme
Helsinki Design System | https://hds.hel.fi/                                       |
