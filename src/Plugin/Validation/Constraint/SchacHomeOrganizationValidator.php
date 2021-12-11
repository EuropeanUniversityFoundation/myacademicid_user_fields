<?php

namespace Drupal\myacademicid_user_fields\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the SchacHomeOrganization constraint.
 *
 * This is essentially a long winded way to validate a hostname.
 * It could possibly be accomplished with a single RegEx found on StackExchange.
 * However, a readable explanation of that RegEx might be longer than this code.
 */
class SchacHomeOrganizationValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $item) {
      // Assume the best case scenario.
      $fail = FALSE;

      // Begin by validating very basic assumptions.
      $fail = (
        !\is_string($item->value) ||
        // Domain names are limited to 253 characters length in total.
        \strlen($item->value) > 253 ||
        // There are at least two components to a valid domain name.
        \count(\explode('.', $item->value)) < 2
      );

      if (!$fail) {
        // Validate individual hostname labels.
        $fail = validateLabels($item->value);
      }

      if ($fail) {
        // The user does not need to know what part of the validation failed.
        // The SCHAC code is supposed to be determined programmatically.
        $this->context
          ->addViolation($constraint->message, ['%value' => $item->value]);
      }
    }
  }

  /**
   * Validates hostname labels.
   *
   * @param string $hostname
   *
   * @return bool $fail
   */
  private function validateLabels(string $hostname): bool {
    $fail = FALSE;

    $labels = \explode('.', $hostname);

    // The last label is the Top Level Domain (TLD).
    $tld = $labels[\count($labels)-1];
    // The TLD label must contain only letters.
    // The longest known TLD is 24 characters long.
    $fail = (!\ctype_alpha($tld) || \strlen($tld) > 24);

    if (!$fail) {
      // Validate all other labels.
      for ($i=0; $i < \count($labels)-1; $i++) {
        $label = $labels[$i];

        if (!$fail) {
          // No leading, trailing or consecutive periods allowed.
          // Each label has a maximum length of 63 characters.
          $fail = (empty($label) || \strlen($label) > 63);
        }

        if (!$fail) {
          // There are restrictions on the domain label in particular.
          $domain = ($i === \count($labels)-2);
          // Validate an individual label.
          $fail = validateSingleLabel($label, $domain);
        }
      }
    }

    return $fail;
  }

  /**
   * Validates a single label.
   *
   * @param string $label
   * @param bool $domain
   *
   * @return bool $fail
   */
  private function validateSingleLabel(string $label, bool $domain): bool {
    $fail = FALSE;

    // Hyphens are allowed, under certain conditions.
    $parts = \explode('-', $label);

    // No leading or trailing hyphens allowed.
    $fail = (
      empty($parts[0]) ||
      empty($parts[\count($parts)-1])
    );

    // In the particular case of a domain label, another rule applies.
    if (!$fail && $domain) {
      // A double hyphen cannot occur in the third and fourth positions.
      $parts = \explode('--', $label);
      $fail = (\count($parts) > 1 && \strlen($parts[0]) === 2);
    }

    return $fail;
  }
}
