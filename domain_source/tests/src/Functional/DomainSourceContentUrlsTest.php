<?php

namespace Drupal\Tests\domain_source\Functional;

use Drupal\Core\Url;
use Drupal\Tests\domain\Functional\DomainTestBase;

/**
 * Tests behavior for getting all URLs for an entity.
 *
 * @group domain_source
 */
class DomainSourceContentUrlsTest extends DomainTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['domain', 'domain_access', 'domain_source', 'field', 'node', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create 4 domains.
    DomainTestBase::domainCreateTestDomains(4);
  }

  /**
   * Tests domain source URLs.
   */
  public function testDomainSourceUrls() {
    // Create a node, assigned to a source domain.
    $id = 'one_example_com';

    $nodes_values = [
      'type' => 'page',
      'title' => 'foo',
      DOMAIN_ACCESS_FIELD => ['example_com', 'one_example_com', 'two_example_com'],
      DOMAIN_ACCESS_ALL_FIELD => 0,
      DOMAIN_SOURCE_FIELD => $id,
    ];
    $node = $this->createNode($nodes_values);

    // Variables for our tests.
    $path = 'node/1';
    $domains = \Drupal::entityTypeManager()->getStorage('domain')->loadMultiple();
    $source = $domains[$id];
    $expected = $source->getPath() . $path;
    $route_name = 'entity.node.canonical';
    $route_parameters = ['node' => 1];
    $uri = 'entity:' . $path;
    $uri_path = '/' . $path;
    $options = [];

    // Get the link using Url::fromRoute().
    $url = Url::fromRoute($route_name, $route_parameters, $options)->toString();
    $this->assertEquals($expected, $url, 'fromRoute');

    // Get the link using Url::fromUserInput()
    $url = Url::fromUserInput($uri_path, $options)->toString();
    $this->assertEquals($expected, $url, 'fromUserInput');

    // Get the link using Url::fromUri()
    $url = Url::fromUri($uri, $options)->toString();
    $this->assertEquals($expected, $url, 'fromUri');

    // Get the path processor service.
    $paths = \Drupal::service('domain_access.manager');
    $urls = $paths->getContentUrls($node);
    $expected = [
      $id => $domains[$id]->getPath() . 'node/1',
      'example_com' => $domains['example_com']->getPath() . 'node/1',
      'two_example_com' => $domains['two_example_com']->getPath() . 'node/1',
    ];

    $this->assertEquals($expected, $urls);
  }

}
