<?php

namespace Drupal\Tests\myacademicid_user_fields\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Affiliation types form.
 *
 * @group myacademicid_user_fields
 */
class AffiliationTypesFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user', 'myacademicid_user_fields'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
  }

  /**
   * Tests access to the Affiliation types form by a non-privileged user.
   */
  public function testAffiliationTypesFormWithoutPermission() {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/services/myacademicid/affiliation-types');
    $this->assertSession()
      ->statusCodeEquals(403);
  }

  /**
   * Tests the Affiliation types form as a privileged user.
   */
  public function testAffiliationTypesForm() {
    $account = $this->drupalCreateUser(['administer myacademicid user fields']);
    $this->drupalLogin($account);

    // Test the default configuration.
    $default_config = $this->config('myacademicid_user_fields.types')
      ->get('additional');
    $this->assertEmpty($default_config);

    // Test access to the settings form.
    $this->drupalGet('admin/config/services/myacademicid/affiliation-types');
    $this->assertSession()
      ->statusCodeEquals(200);

    // Test default values on the page.
    $this->assertSession()
      ->pageTextMatchesCount(8, '/Default/');
    $this->assertSession()
      ->pageTextMatchesCount(8, '/domain.tld/');
    $this->assertSession()
      ->pageTextMatchesCount(0, '/Config/');
    $this->assertSession()
      ->pageTextMatchesCount(0, '/Override/');

    // Test form submission.
    $additional_values = [
      'ewp-admin',
      'honor|Honoris Causa',
      'faculty|Academic staff',
    ];
    $submission_data = ['additional' => implode("\n", $additional_values)];
    $this->submitForm($submission_data, 'Save configuration');
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test the configuration has been updated.
    $new_config = $this->config('myacademicid_user_fields.types')
      ->get('additional');
    $this->assertNotEquals($default_config, $new_config);

    // Test the form loads normally.
    $this->drupalGet('admin/config/services/myacademicid/affiliation-types');
    $this->assertSession()
      ->statusCodeEquals(200);

    // Look for exactly two additional values stored in Config.
    $this->assertSession()
      ->pageTextMatchesCount(2, '/Config/');
    // Look for exactly one label override.
    $this->assertSession()
      ->pageTextMatchesCount(1, '/Override/');
    // Test default values on the page, now that one is overridden.
    $this->assertSession()
      ->pageTextMatchesCount(7, '/Default/');

    // First additional value has no label, so the key appears four times.
    $this->assertSession()
      ->pageTextMatchesCount(4, '/ewp-admin/');

    // Second additional value has label, so the key appears three times.
    $this->assertSession()
      ->pageTextMatchesCount(3, '/honor/');
    // Second additional value has label, so the label appears twice.
    $this->assertSession()
      ->pageTextMatchesCount(2, '/Causa/');

    // Override replaces the label, so the key appears three times.
    $this->assertSession()
      ->pageTextMatchesCount(3, '/faculty/');
    // Override replaces the label, so the new label appears twice.
    $this->assertSession()
      ->pageTextMatchesCount(2, '/Academic staff/');
    // Override replaces the label, so the old label does not appear.
    $this->assertSession()
      ->pageTextMatchesCount(0, '/Faculty/');

    // Tests manual reset to default configuration.
    $this->submitForm(['additional' => ''], 'Save configuration');
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test the configuration has been updated.
    $reset_config = $this->config('myacademicid_user_fields.types')
      ->get('additional');
    $this->assertEmpty($reset_config);

    // Test default values on the page once more.
    $this->assertSession()
      ->pageTextMatchesCount(8, '/Default/');
    $this->assertSession()
      ->pageTextMatchesCount(8, '/domain.tld/');
    $this->assertSession()
      ->pageTextMatchesCount(0, '/Config/');
    $this->assertSession()
      ->pageTextMatchesCount(0, '/Override/');

  }

}
