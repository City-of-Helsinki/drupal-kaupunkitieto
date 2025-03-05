<?php

declare(strict_types=1);

namespace Drupal\kaupunkitieto_config\Plugin\Validation\Constraint;

use Drupal\Core\Field\FieldItemListInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Embed constraints.
 */
class EmbedConstraintsValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    assert($constraint instanceof EmbedConstraints);

    // Only handle embed fields.
    if (
      !$value instanceof FieldItemListInterface ||
      !in_array($value->getName(), array_keys(EmbedConstraints::EMBED_FIELDS)) ||
      $value->isEmpty()
    ) {
      return;
    }

    // Add violation if the value doesn't match the pattern.
    if (
      isset(EmbedConstraints::VALIDATION_PATTERNS[$value->getName()]) &&
      !preg_match(EmbedConstraints::VALIDATION_PATTERNS[$value->getName()], $value->getString())
    ) {
      $this->context->addViolation($constraint->{EmbedConstraints::EMBED_FIELDS[$value->getName()]}, [
        '%value' => EmbedConstraints::VALID_VALUES[$value->getName()],
      ]);
    }

    $parent = $value->getParent();

    // Handle only field_embed_link field.
    if (!$parent || $value->getName() !== 'field_embed_link' || $value->isEmpty()) {
      return;
    }

    // Add violation if the embed link has value when the embed code and
    // embed code ID are filled.
    if (
      !$parent->get('field_embed_code')->isEmpty() ||
      !$parent->get('field_embed_code_id')->isEmpty()
    ) {
      $this->context->addViolation($constraint->embedShouldNotBeFilled);
    }
  }

}
