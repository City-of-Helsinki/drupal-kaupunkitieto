# Kaupunkitieto

Kaupunkitieto is a website project for the City of Helsinki's statistics and research unit.
Technical implementation is based on the main City of Helsinki platform.
Custom functionalities are added on top of it to match requirements for the kaupunkitieto instance.

Designs are guided by the Helsinki Design System which offers a variety of ready-made components.

Testing environment is hosted on `platform.sh`. Master branch should be used as production environment on release.

`Bitbucket` repository is used primarily for version control.
`Platform.sh` utilises `Git` as well which should be added a secondary remote for version control.
Bitbucket pipeline can be set in later phases if see necessary.

Project uses `HDBT` as base for the custom kaupunkitieto subtheme.
`HDBT-admin` is set as the admin theme and can be extended similarly if seen necessary.
HDBT themes are included in the project as composer packages.
Regression testing is advisable when upgrading base theme as implementation between platforms may vary.
See `kaupunkitieto` theme's README for additional information about theme layer.

## Environments

Env | Branch | Drush alias | URL
--- | ------ | ----------- | ---
development | * | - | http://kaupunkitieto.docker.sh/
production | master | @site-aliases.eg4znbqjkciow.master | https://master-7rqtwti-eg4znbqjkciow.eu-5.platformsh.site/

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
| Name | URL | Description |
| --- | --- | --- |
HELfi drupal -platform | https://github.com/City-of-Helsinki/drupal-helfi | Base installation
HDBT-theme | https://github.com/City-of-Helsinki/drupal-hdbt | Base theme
HDBT Admin theme | https://github.com/City-of-Helsinki/drupal-hdbt-admin | Admin theme
Helsinki Design System | https://hds.hel.fi/ |
`platform.sh` | https://docs.platform.sh/ | Hosting documentation
Bitbucket | https://bitbucket.org/mirum-europe/kaupunkitieto/ | Version control