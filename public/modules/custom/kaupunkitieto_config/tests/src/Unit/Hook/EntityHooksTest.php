<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_config\Unit\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\kaupunkitieto_config\Hook\EntityHooks;
use Drupal\kaupunkitieto_config\Plugin\Validation\Constraint\EmbedConstraints;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests entity hooks.
 */
#[Group('kaupunkitieto_config')]
class EntityHooksTest extends UnitTestCase {

  /**
   * Embed fields on embed paragraphs get the embed constraint.
   */
  public function testEmbedFieldsGetEmbedConstraint(): void {
    $fields = [];
    foreach (array_keys(EmbedConstraints::EMBED_FIELDS) as $name) {
      $field = $this->createMock(FieldConfigInterface::class);
      $field->expects($this->once())
        ->method('addConstraint')
        ->with('EmbedConstraints', []);
      $fields[$name] = $field;
    }

    $other = $this->createMock(FieldConfigInterface::class);
    $other->expects($this->never())->method('addConstraint');
    $fields['field_other'] = $other;

    (new EntityHooks())->entityBundleFieldInfoAlter($fields, $this->createEntityType('paragraph'), 'embed');
  }

  /**
   * Other bundles and entity types are left untouched.
   */
  #[DataProvider('untouchedBundleProvider')]
  public function testOtherBundlesAreUntouched(string $entity_type_id, string $bundle): void {
    $field = $this->createMock(FieldConfigInterface::class);
    $field->expects($this->never())->method('addConstraint');
    $fields = ['field_embed_code' => $field];

    (new EntityHooks())->entityBundleFieldInfoAlter($fields, $this->createEntityType($entity_type_id), $bundle);
  }

  /**
   * Provides entity types and bundles that skip the embed constraint.
   *
   * @return array<string, array{string, string}>
   *   The entity type ID and bundle pairs.
   */
  public static function untouchedBundleProvider(): array {
    return [
      'other paragraph bundle' => ['paragraph', 'text'],
      'other entity type' => ['node', 'embed'],
    ];
  }

  /**
   * Creates an entity type mock.
   *
   * @param string $id
   *   The entity type ID.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   Returns the entity type mock.
   */
  private function createEntityType(string $id): EntityTypeInterface {
    $entity_type = $this->createMock(EntityTypeInterface::class);
    $entity_type->method('id')->willReturn($id);
    return $entity_type;
  }

}
