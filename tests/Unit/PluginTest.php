<?php

namespace JdSupport\Tests\Unit;

use JdSupport\Core\Plugin;
use WP_UnitTestCase;

/**
 * Plugin Test
 */
class PluginTest extends WP_UnitTestCase
{
	/**
	 * Test plugin initialization
	 */
	public function testPluginInitialization()
	{
		$plugin = new Plugin();

		$this->assertInstanceOf(Plugin::class, $plugin);
		$this->assertEquals('1.1.8', $plugin->getVersion());
		$this->assertEquals('jd_support', $plugin->getPluginName());
	}

	/**
	 * Test plugin container
	 */
	public function testPluginContainer()
	{
		$plugin = new Plugin();
		$container = $plugin->getContainer();

		$this->assertNotNull($container);
		$this->assertTrue($container->has('config'));
		$this->assertTrue($container->has('security'));
		$this->assertTrue($container->has('admin'));
		$this->assertTrue($container->has('cache'));
		$this->assertTrue($container->has('svg'));
		$this->assertTrue($container->has('version'));
	}
}
