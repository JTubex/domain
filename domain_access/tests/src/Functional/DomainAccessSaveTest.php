<?php

namespace Drupal\Tests\domain_access\Functional;

use Drupal\Tests\domain\Functional\DomainTestBase;

/**
 * Tests behavior for saving the domain access field elements.
 *
 * @group domain_access
 */
class DomainAccessSaveTest extends DomainTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['domain', 'domain_access', 'field', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create 5 domains.
    $this->domainCreateTestDomains(5);
  }

  /**
   * Basic test setup.
   */
  public function testDomainAccessSave() {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    // Save a node programatically.
    $node = $storage->create([
      'type' => 'article',
      'title' => 'Test node',
      'uid' => '1',
      'status' => 1,
      DOMAIN_ACCESS_FIELD => ['example_com'],
      DOMAIN_ACCESS_ALL_FIELD => 1,
    ]);
    $node->save();

    // Load the node.
    $node = $storage->load(1);

    // Check that two values are set properly.
    $manager = \Drupal::service('domain_access.manager');
    $values = $manager->getAccessValues($node);
    $this->assertCount(1, $values, 'Node saved with one domain records.');
    $value = $manager->getAllValue($node);
    $this->assertTrue($value, 'Node saved to all affiliates.');

    // Save a node with different values.
    $node = $storage->create([
      'type' => 'article',
      'title' => 'Test node',
      'uid' => '1',
      'status' => 1,
      DOMAIN_ACCESS_FIELD => ['example_com', 'one_example_com'],
      DOMAIN_ACCESS_ALL_FIELD => 0,
    ]);
    $node->save();

    // Load and check the node.
    $node = $storage->load(2);
    $values = $manager->getAccessValues($node);
    $this->assertCount(2, $values, 'Node saved with two domain records.');
    $value = $manager->getAllValue($node);
    $this->assertFalse($value, 'Node not saved to all affiliates.');
  }

}
