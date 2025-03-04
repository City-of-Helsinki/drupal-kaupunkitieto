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
  }

}
