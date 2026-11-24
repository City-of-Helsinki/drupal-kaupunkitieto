<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_search\EventSubscriber;

use Drupal\search_api\Event\QueryPreExecuteEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Falls back to OR search when an AND search would return no results.
 *
 * The site search view searches with the AND operator ("contains all of these
 * words"). When no item contains all of the words, the keywords are switched
 * to OR ("contains any of these words") so that the user still gets results.
 */
final class FulltextOrFallbackSubscriber implements EventSubscriberInterface {

  /**
   * The views query tag of the site search view.
   */
  private const QUERY_TAG = 'views_site_search';

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SearchApiEvents::QUERY_PRE_EXECUTE . '.' . self::QUERY_TAG => 'onQueryPreExecute',
    ];
  }

  /**
   * Switches the fulltext keys to OR if the AND search has no results.
   *
   * @param \Drupal\search_api\Event\QueryPreExecuteEvent $event
   *   The query pre-execute event.
   */
  public function onQueryPreExecute(QueryPreExecuteEvent $event): void {
    $query = $event->getQuery();
    $keys = $query->getKeys();

    // Only parsed keys with AND conjunction and multiple words are relevant.
    if (!is_array($keys) || ($keys['#conjunction'] ?? 'AND') !== 'AND' || !empty($keys['#negation'])) {
      return;
    }
    $words = array_filter(array_keys($keys), static fn ($key) => !str_starts_with((string) $key, '#'));
    if (count($words) < 2) {
      return;
    }

    // Count the AND results with a lightweight copy of the query. The query
    // has already been preprocessed at this point, so the copy is sent
    // directly to the backend instead of executing it, which would skip it as
    // already executed and would also pollute the static results cache.
    $count_query = clone $query;
    $count_query->range(0, 1);
    $count_query->setOption('search_api_facets', []);
    $count_query->setOption('skip result count', FALSE);
    $count_query->getIndex()->getServerInstance()->search($count_query);

    // The database backend may return the count as a string.
    if ((int) $count_query->getResults()->getResultCount() === 0) {
      // Take the reference only now: a reference held during cloning would be
      // shared between the original query and the copy.
      $query_keys = &$query->getKeys();
      if (is_array($query_keys)) {
        $query_keys['#conjunction'] = 'OR';
      }
    }
  }

}
