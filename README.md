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

## Login to Drupal container

This will log you inside the app container:

```
$ make shell
```

## Instance specific features

### Custom content types

#### Article (article)

The Article content type is a remnant of the original Helfi platform article content type. Article content type has been removed from the Helfi platform config repository and from other instances, but as this instance and Helfi platform got differentiated, it was never removed from this instance. The Article content type is a blend of News item and Standard page content types. It has its own theme and few fields what are used to give metadata to the article what in article teaser display mode.

### Custom paragraphs

#### Infograph (infograph_group, infograph)

The _infograph_ paragraph is a list of links and statistical values of various statistics. The list is presented in a carousel and its visually rendered as tiled fact slideshow. The statistics are retrieved via [API once a day](https://github.com/City-of-Helsinki/drupal-kaupunkitieto/blob/f30eec98e4cd7ce1436ea0205dad9bb8cfcf5c6b/public/modules/custom/kaupunkitieto_infograph/src/Commands/Infograph.php).

#### Hero with search (hero)

_Hero with search_ is a custom design for the Hero paragraph. It is used on the front page of the instance, and it is visually designed to differ from the hdbt theme hero search. See: [markup](https://github.com/City-of-Helsinki/drupal-kaupunkitieto/blob/050fa230cb3a91675a438211c1f3d9d960e97a14/public/themes/custom/hdbt_subtheme/templates/paragraph/paragraph--hero--with-search.html.twig) and [styles](https://github.com/City-of-Helsinki/drupal-kaupunkitieto/blob/77b5477ff1c8b6705d4eb118d7c6c1aed5a0423a/public/themes/custom/hdbt_subtheme/src/scss/06_components/paragraphs/_hero.scss).

#### Content menu (content_menu)

_Content menu_ paragraph creates a sidebar menu out of text paragraphs selected by the content producer. One text paragraphs can be visible at a time and the logic is being handled by a [custom javascript](https://github.com/City-of-Helsinki/drupal-kaupunkitieto/blob/dcb98476f49cd7ebf0578fd5529a4976fa5da283/public/themes/custom/hdbt_subtheme/src/js/contentMenu.js).

#### Quick links (quick_links)

The _quick links_ paragraph lists curated and visually prominent list of links on a page.

## Customizations

### Site search

The _Site search_ is a customized view page which uses DB as a backend.The content is indexed via Search API. The search page uses facets and a text field to filter the search.
