<?php

declare(strict_types=1);

namespace Drupal\Tests\kaupunkitieto_config\Unit;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\kaupunkitieto_config\Plugin\Validation\Constraint\EmbedConstraints;
use Drupal\kaupunkitieto_config\Plugin\Validation\Constraint\EmbedConstraintsValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * Unit test for EmbedConstraintsValidator.
 *
 * @group kaupunkitieto_config
 */
class EmbedConstraintsValidatorTest extends ConstraintValidatorTestCase {

  /**
   * {@inheritdoc}
   */
  protected function createValidator(): EmbedConstraintsValidator {
    return new EmbedConstraintsValidator();
  }

  /**
   * Tests valid values.
   */
  public function testValidValues(): void {
    $valid_values = [
      'field_embed_code' => 'https://e.infogram.com/js/dist/embed.js?valid123',
      'field_embed_code_id' => 'infogram_0_abc1234-5678-9101-1121-314151617181',
      'field_embed_link' => 'https://infogram.com/valid-link',
    ];

    foreach ($valid_values as $field_name => $valid_value) {
      $field = $this->createFieldMock($field_name, $valid_value);
      $this->validator->validate($field, new EmbedConstraints());

      // No violations should be triggered.
      $this->assertNoViolation();
    }
  }

  /**
   * Tests invalid values.
   */
  public function testInvalidValues(): void {
    $invalid_values = [
      'field_embed_code' => ['invalid_value', 'embedCodeNotValid'],
      'field_embed_code_id' => ['wrong_prefix_abc123', 'embedCodeIdNotValid'],
      'field_embed_link' => ['http://wrong-url.com/abc123', 'embedLinkNotValid'],
    ];

    foreach ($invalid_values as $field_name => [$invalid_value, $error_message_key]) {
      $constraint = new EmbedConstraints();
      $expected_message = $constraint->$error_message_key;

      $field = $this->createFieldMock($field_name, $invalid_value);
      $this->setUp();
      $this->validator->validate($field, $constraint);
      $this->assertCount(1, $this->context->getViolations());
      $this->assertEquals($expected_message, $this->context->getViolations()[0]->getMessage());
      $this->context->getViolations()->remove(1);
    }
  }

  /**
   * Tests empty field values.
   */
  public function testEmptyField(): void {
    $field = $this->createMock(FieldItemListInterface::class);
    $field->method('getName')->willReturn('field_embed_code');
    $field->method('isEmpty')->willReturn(TRUE);

    $this->validator->validate($field, new EmbedConstraints());

    $this->assertNoViolation();
  }

  /**
   * Creates a mock FieldItemListInterface object.
   */
  private function createFieldMock(string $name, string $value): MockObject {
    $field = $this->createMock(FieldItemListInterface::class);
    $field->method('getName')->willReturn($name);
    $field->method('getString')->willReturn($value);
    $field->method('isEmpty')->willReturn(FALSE);
    return $field;
  }

}
