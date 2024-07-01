<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_infograph\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\paragraphs\Entity\Paragraph;
use Drush\Commands\DrushCommands;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Infograph query command.
 */
final class Infograph extends DrushCommands implements LoggerAwareInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;
  use LoggerAwareTrait;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $connection,
  ) {
    parent::__construct();
  }

  /**
   * Execute infograph query.
   *
   *
   * @usage drush kaupunkitieto:infograph-query
   *   Updata infograph values.
   *
   * @command kaupunkitieto:infograph-query
   */
  public function infographQuery(): void {
    $graph_field_name = 'field_graph_id';
    $graph_type_field_name = 'field_type_infograph';

    foreach ($this->get_graphs() as $paragraph) {
      if (!$paragraph->hasField($graph_field_name)) {
        continue;
      }

      if (!$rows = $this->fetch_data($paragraph->get($graph_field_name)->value)) {
        $this->logger()
          ->warning("No rows found for Graph Id: {$paragraph->get($graph_field_name)->value}");
        continue;
      }

      $paragraph->set($graph_type_field_name, $rows->graphType);

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
   */
  function get_graphs(): array {
    $storage = $this->entityTypeManager->getStorage('paragraph');
    $paragraphIds = $storage->getQuery()->condition('type', 'infograph')->execute();

    return $storage->loadMultiple($paragraphIds);
  }

  /**
   * Query data from external interface.
   *
   * https://infographic-api.herokuapp.com/docs
   *
   */
  function fetch_data($id): mixed {
    $client = \Drupal::httpClient();

    $config = \Drupal::config('kaupunkitieto_infograph.settings');
    if(trim($config->get('url')) == '') {
      return [];
    }

    try {
      $request = $client->get($config->get('url').$id);
      return json_decode($request->getBody()->getContents());
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return [];
    }
  }
}
