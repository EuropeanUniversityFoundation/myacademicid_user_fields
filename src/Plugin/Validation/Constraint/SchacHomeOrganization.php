<?php

namespace Drupal\myacademicid_user_fields\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a valid SCHAC code.
 *
 * @Constraint(
 *   id = "SchacHomeOrganization",
 *   label = @Translation("SCHAC Home Organization", context = "Validation"),
 *   type = "string"
 * )
 */
class SchacHomeOrganization extends Constraint {

  // The message that will be shown if the SCHAC code is not valid.
  public $message = '%value is not a valid SCHAC code.';

}
