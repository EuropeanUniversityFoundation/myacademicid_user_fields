<?php

namespace Drupal\myacademicid_user_fields\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the SchacHomeOrganization constraint.
 *
 * This is essentially a long winded way to validate a domain name.
 * It could be done with a single RegEx found on StackExchange.
 * However, a readable explanation of that RegEx would be longer than this code.
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
        // Validate individual domain name components.
        $components = \explode('.', $item->value);

        // The last component is the Top Level Domain (TLD).
        $tld = $components[\count($components)-1];
        // The TLD component must contain only letters.
        // The longest known TLD is 24 characters long.
        $fail = (!\ctype_alpha($tld) || \strlen($tld) > 24);

        if (!$fail) {
          // Validate all other components.
          for ($i=0; $i < \count($components)-1; $i++) {
            $component = $components[$i];

            $fail = (
              // No leading, trailing or consecutive dots allowed.
              empty($component) ||
              // Each component has a maximum length of 63 characters.
              \strlen($component) > 63 ||
              (
                // A component can be only a single character long.
                \strlen($component) === 1 &&
                // A single character component must be alphanumerical.
                \preg_match('/^[a-zA-Z0-9]$/', $component) !== 1
              ) ||
              // A component can contain alphanumerical characters and dashes.
              // A component cannot start or end with a dash.
              \preg_match(
                '/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/',
                $component
              ) !== 1
            );
          }
        }
      }

      if ($fail) {
        // The user does not need to know what part of the validation failed.
        // The SCHAC code is supposed to be determined programmatically.
        $this->context
          ->addViolation($constraint->message, ['%value' => $item->value]);
      }
    }
  }

}
