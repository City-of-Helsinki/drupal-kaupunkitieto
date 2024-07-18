<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_infograph\Commands;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\Entity\Paragraph;
use Drush\Commands\DrushCommands;
use GuzzleHttp\ClientInterface;

/**
 * Infograph query command.
 */
final class Infograph extends DrushCommands {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The http client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $connection,
    protected ClientInterface $httpClient,
    private readonly ConfigFactoryInterface $configFactory,
  ) {
    parent::__construct();
  }

  /**
   * Execute infograph query.
   *
   * @usage drush kaupunkitieto:infograph-query
   *   Update infograph values.
   *
   * @command kaupunkitieto:infograph-query
   */
  public function infographQuery(): void {
    $graphIdFieldName = 'field_graph_id';
    $graphTypeFieldName = 'field_type_infograph';

    foreach ($this->getGraphs() as $paragraph) {
      if (!$paragraph->hasField($graphIdFieldName)) {
        continue;
      }

      if (!$rows = $this->fetchData($paragraph->get($graphIdFieldName)->value)) {
        $this->logger()
          ->warning("No rows found for Graph Id: {$paragraph->get($graphIdFieldName)->value}");
        continue;
      }

      $paragraph->set($graphTypeFieldName, $rows->graphType);

      $new_set = [];
      foreach ($rows->graphParts as $value) {
        $subParagraph = Paragraph::create(['type' => 'infograph_row'])
          ->set('field_data', $value->data[1])
          ->set('field_label', $value->data[0])
          ->set('field_url', $value->url);
        $subParagraph->save();

        $new_set[] = [
          'target_id' => $subParagraph->id(),
          'target_revision_id' => $subParagraph->getRevisionId(),
        ];
      }

      foreach ($paragraph->get('field_rows')->referencedEntities() as $paragraphEntity) {
        $paragraphEntity->delete();
      }

      $paragraph->set('field_rows', $new_set);
      $paragraph->save();
    }
  }

  /**
   * Get the paragraphs.
   *
   * @return array
   *   Returns array of paragraphs.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getGraphs(): array {
    $storage = $this->entityTypeManager->getStorage('paragraph');
    $paragraphIds = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'infograph')
      ->execute();

    return $storage->loadMultiple($paragraphIds);
  }

  /**
   * Query data from external interface.
   *
   * See: https://infographic-api.herokuapp.com/docs.
   *
   * @param string $id
   *   Infograph id.
   *
   * @return mixed
   *   Returns the decoded request contents or empty array.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function fetchData(string $id): mixed {
    $config = $this->configFactory->get('kaupunkitieto_infograph.settings');
    if (trim($config->get('url')) == '') {
      return [];
    }

    try {
      $request = $this->httpClient->request('GET', $config->get('url') . $id);
      return json_decode($request->getBody()->getContents());
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return [];
    }
  }

}
