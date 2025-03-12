<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_infograph\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\media\Entity\Media;
use Drupal\paragraphs\Entity\Paragraph;
use Drush\Attributes\Command;
use Drush\Attributes\Usage;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Drush commandfile.
 */
final class MigrateEmbedParagraphs extends DrushCommands {

  use AutowireTrait;
  use DependencySerializationTrait;

  /**
   * The paragraph storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $paragraphStorage;

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $nodeStorage;

  /**
   * Constructs a KaupunkitietoMigrateParagraphs object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Connection $connection,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
  ) {
    parent::__construct();
    $this->paragraphStorage = $this->entityTypeManager->getStorage('paragraph');
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
      $container->get('logger.factory'),
    );
  }

  /**
   * Converts Embed paragraphs to Chart paragraphs.
   */
  #[Command(name: 'kaupunkitieto:migrate-embed-paragraphs')]
  #[Usage(name: 'kaupunkitieto:migrate-embed-paragraphs', description: 'Running the command will migrate Embed paragraphs with app.powerbi.com URLs to chart paragraphs.')]
  public function commandName() {
    $query = $this->connection->select('paragraph__field_embed_link', 'p')
      ->fields('p', ['entity_id'])
      ->condition('p.field_embed_link_uri', '%app.powerbi.com%', 'LIKE');

    // Embed paragraphs with app.powerbi.com URLs.
    $paragraph_ids = $query->execute()->fetchCol();

    if (!$paragraph_ids) {
      $this->loggerFactory->get('kaupunkitieto_config')->notice('No Embed paragraphs with app.powerbi.com found.');
      return;
    }

    // If Embed paragraphs with app.powerbi.com URLs are found, migrate them.
    foreach ($paragraph_ids as $paragraph_id) {
      // Get parent references.
      $query = $this->connection->select('paragraphs_item_field_data', 'pi')
        ->fields('pi', ['parent_id', 'parent_type', 'parent_field_name'])
        ->condition('pi.id', $paragraph_id)
        ->condition('pi.type', 'embed');
      $parent = $query->execute()->fetchAssoc();

      if ($parent) {
        $this->processMigration($parent, $paragraph_id);
      }
    }
  }

  /**
   * Process the migration.
   *
   * @param array $parent_entity
   *   Parent entity, either a node or another paragraph.
   * @param string $paragraph_id
   *   Embed paragraph to be converted.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function processMigration(array $parent_entity, string $paragraph_id): void {
    $embed_paragraph = Paragraph::load($paragraph_id);

    $parent_id = $parent_entity['parent_id'];
    $parent_type = $parent_entity['parent_type'];
    $parent_field = $parent_entity['parent_field_name'];

    if ($parent_type === 'node') {
      /** @var \Drupal\node\NodeInterface $parent_entity */
      $parent_entity = $this->nodeStorage->load($parent_id);
    }
    else {
      /** @var \Drupal\paragraphs\ParagraphInterface $parent_entity */
      $parent_entity = $this->paragraphStorage->load($parent_id);
    }

    if ($parent_entity) {
      $paragraphs = $parent_entity->get($parent_field)->getValue();
      $save = FALSE;

      // Replace the reference to the Embed paragraph with
      // the new Video paragraph.
      foreach ($paragraphs as &$paragraph) {
        if ($paragraph['target_id'] == $embed_paragraph->id()) {
          // Construct a title for the media entity.
          // @phpstan-ignore-next-line
          $title = $embed_paragraph->getParentEntity()->getTitle()
            ?? $embed_paragraph->getParentEntity()->id();

          // Create the chart media entity.
          $chart_media = Media::create([
            'bundle' => 'helfi_chart',
            'name' => $title,
            'langcode' => $embed_paragraph->language()->getId(),
            'field_helfi_chart_url' => [
              [
                'uri' => $embed_paragraph
                  ->get('field_embed_link')
                  ->first()
                  ->getString(),
              ],
            ],
            'field_helfi_chart_title' => [
              'value' => $title,
            ],
          ]);
          $chart_media->save();

          // Create a new Chart paragraph.
          $chart_paragraph = Paragraph::create([
            'type' => 'chart',
            'field_chart_chart' => [
              'target_id' => $chart_media->id(),
            ],
            'field_iframe_title' => [
              'value' => $title,
            ],
            'langcode' => $embed_paragraph->language()->getId(),
          ]);
          $chart_paragraph->save();

          $paragraph['target_id'] = $chart_paragraph->id();
          $paragraph['target_revision_id'] = $chart_paragraph->getRevisionId();
          $save = TRUE;
        }
      }

      // Set the new chart paragraphs to the parent entity and save.
      if ($save) {
        $parent_entity->set($parent_field, $paragraphs);
        $parent_entity->save();

        if ($parent_type === 'node') {
          global $base_url;
          $this->loggerFactory->get('kaupunkitieto_config')->notice("Converted embed to chart in: $base_url/node/$parent_id");
        }

        // Delete the old Embed paragraph.
        $embed_paragraph->delete();
      }
    }
  }

}
