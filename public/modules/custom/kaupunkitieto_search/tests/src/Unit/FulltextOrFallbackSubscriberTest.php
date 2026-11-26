<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_search\Unit;

use Drupal\kaupunkitieto_search\EventSubscriber\FulltextOrFallbackSubscriber;
use Drupal\search_api\Event\QueryPreExecuteEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\ParseMode\ParseModeInterface;
use Drupal\search_api\Query\Query;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\ServerInterface;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the site search AND operator's OR fallback.
 *
 * Uses a real search query with a mocked search server, so no site, database
 * or search index is needed.
 */
#[CoversClass(FulltextOrFallbackSubscriber::class)]
#[Group('kaupunkitieto_search')]
final class FulltextOrFallbackSubscriberTest extends UnitTestCase {

  /**
   * The queries the mocked search server received.
   *
   * @var \Drupal\search_api\Query\QueryInterface[]
   */
  private array $searchedQueries = [];

  /**
   * Tests that the subscriber listens to the site search view's queries only.
   */
  public function testSubscribedEvents(): void {
    $this->assertSame(
      [SearchApiEvents::QUERY_PRE_EXECUTE . '.views_site_search' => 'onQueryPreExecute'],
      FulltextOrFallbackSubscriber::getSubscribedEvents(),
    );
  }

  /**
   * Provides result counts that mean "no results".
   *
   * @return array<string, array{int|string}>
   *   The result counts.
   */
  public static function noResultsProvider(): array {
    return [
      'integer' => [0],
      // The database backend returns the count as a string.
      'string' => ['0'],
    ];
  }

  /**
   * Tests the switch to OR when no item contains all the words.
   */
  #[DataProvider('noResultsProvider')]
  public function testFallsBackToOrWithoutResults(int|string $result_count): void {
    $query = $this->createQuery(['#conjunction' => 'AND', 'asuminen', 'helsinki'], $result_count);

    $this->dispatch($query);

    $this->assertSame(['#conjunction' => 'OR', 'asuminen', 'helsinki'], $query->getKeys());
  }

  /**
   * Tests that the AND search is kept when it has results.
   */
  public function testKeepsAndWithResults(): void {
    $query = $this->createQuery(['#conjunction' => 'AND', 'asuminen', 'helsinki'], '3');

    $this->dispatch($query);

    $this->assertSame(['#conjunction' => 'AND', 'asuminen', 'helsinki'], $query->getKeys());
    $this->assertCount(1, $this->searchedQueries);
  }

  /**
   * Tests that the count is made with a cheap copy of the query.
   */
  public function testCountQueryIsSeparateLightweightCopy(): void {
    $query = $this->createQuery(['#conjunction' => 'AND', 'asuminen', 'helsinki'], 0);
    $query->setOption('search_api_facets', ['topic' => ['field' => 'topic']]);
    $query->range(20, 10);

    $this->dispatch($query);

    $this->assertCount(1, $this->searchedQueries);
    $count_query = $this->searchedQueries[0];
    $this->assertNotSame($query, $count_query);
    $this->assertSame(0, $count_query->getOption('offset'));
    $this->assertSame(1, $count_query->getOption('limit'));
    $this->assertSame([], $count_query->getOption('search_api_facets'));
    $this->assertFalse($count_query->getOption('skip result count'));

    // The real query keeps its paging and facets.
    $this->assertSame(20, $query->getOption('offset'));
    $this->assertSame(10, $query->getOption('limit'));
    $this->assertSame(['topic' => ['field' => 'topic']], $query->getOption('search_api_facets'));

    // Switching the real query to OR must not change the copy, which would
    // happen if the keys were shared by reference.
    $this->assertSame(['#conjunction' => 'OR', 'asuminen', 'helsinki'], $query->getKeys());
    $this->assertSame(['#conjunction' => 'AND', 'asuminen', 'helsinki'], $count_query->getKeys());
  }

  /**
   * Provides keys that the fallback must leave alone.
   *
   * @return array<string, array{mixed}>
   *   The keys.
   */
  public static function ignoredKeysProvider(): array {
    return [
      'single word' => [['#conjunction' => 'AND', 'asuminen']],
      'already OR' => [['#conjunction' => 'OR', 'asuminen', 'helsinki']],
      'negated' => [['#conjunction' => 'AND', '#negation' => TRUE, 'asuminen', 'helsinki']],
      'unparsed string' => ['asuminen helsinki'],
      'no keys' => [NULL],
    ];
  }

  /**
   * Tests that other searches are not changed or counted.
   */
  #[DataProvider('ignoredKeysProvider')]
  public function testIgnoresOtherKeys(mixed $keys): void {
    $query = $this->createQuery($keys, 0);

    $this->dispatch($query);

    $this->assertSame($keys, $query->getKeys());
    $this->assertSame([], $this->searchedQueries, 'No count query is run.');
  }

  /**
   * Creates a query whose search server returns the given result count.
   *
   * @param mixed $keys
   *   The parsed search keys.
   * @param int|string $result_count
   *   The result count the search server returns.
   */
  private function createQuery(mixed $keys, int|string $result_count): QueryInterface {
    $server = $this->createMock(ServerInterface::class);
    $server->method('search')
      ->willReturnCallback(function (QueryInterface $query) use ($result_count): void {
        $this->searchedQueries[] = $query;
        // The database backend may set the count as a string.
        // @phpstan-ignore argument.type
        $query->getResults()->setResultCount($result_count);
      });

    $index = $this->createMock(IndexInterface::class);
    $index->method('status')->willReturn(TRUE);
    $index->method('getServerInstance')->willReturn($server);

    $query = new Query($index);
    // Arrays are set as already parsed keys. Strings go through the parse
    // mode, so use one that keeps them unparsed, like the "direct" mode.
    $parse_mode = $this->createMock(ParseModeInterface::class);
    $parse_mode->method('parseInput')->willReturnArgument(0);
    $query->setParseMode($parse_mode);
    $query->keys($keys);
    return $query;
  }

  /**
   * Runs the subscriber for the query.
   */
  private function dispatch(QueryInterface $query): void {
    (new FulltextOrFallbackSubscriber())->onQueryPreExecute(new QueryPreExecuteEvent($query));
  }

}
