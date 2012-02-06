<?php

use Laravel\Routing\Router;

class RoutingTest extends PHPUnit_Framework_TestCase {

	/**
	 * Destroy the testing environment.
	 */
	public function setUp()
	{
		Bundle::$started = array();
		Router::$names = array();
		Router::$routes = array();
	}

	/**
	 * Destroy the testing environment.
	 */
	public function tearDown()
	{
		Bundle::$started = array();
		Router::$names = array();
		Router::$routes = array();
	}

	/**
	 * Test the Router::find method.
	 *
	 * @group laravel
	 */
	public function testNamedRoutesCanBeLocatedByTheRouter()
	{
		Router::register('GET /', array('name' => 'home'));
		Router::register('GET /dashboard', array('name' => 'dashboard'));

		$home = Router::find('home');
		$dashboard = Router::find('dashboard');

		$this->assertTrue(isset($home['GET /']));
		$this->assertTrue(isset($dashboard['GET /dashboard']));
	}

	/**
	 * Test the basic routing mechanism.
	 *
	 * @group laravel
	 */
	public function testBasicRouteCanBeRouted()
	{
		Router::register('GET /', function() {});
		Router::register(array('GET /home', 'GET /main'), function() {});

		$this->assertEquals('GET /', Router::route('GET', '/')->key);
		$this->assertEquals('GET /home', Router::route('GET', '/home')->key);
		$this->assertEquals('GET /main', Router::route('GET', '/main')->key);
	}

	/**
	 * Test that the router can handle basic wildcards.
	 *
	 * @group laravel
	 */
	public function testWildcardRoutesCanBeRouted()
	{
		Router::register('GET /user/(:num)', function() {});
		Router::register('GET /profile/(:any)/(:num)', function() {});

		$this->assertNull(Router::route('GET', 'user/1.5'));
		$this->assertNull(Router::route('GET', 'user/taylor'));
		$this->assertEquals(array(25), Router::route('GET', 'user/25')->parameters);
		$this->assertEquals('GET /user/(:num)', Router::route('GET', 'user/1')->key);

		$this->assertNull(Router::route('GET', 'profile/1/otwell'));
		$this->assertNull(Router::route('POST', 'profile/taylor/1'));
		$this->assertNull(Router::route('GET', 'profile/taylor/otwell'));
		$this->assertNull(Router::route('GET', 'profile/taylor/1/otwell'));
		$this->assertEquals(array('taylor', 25), Router::route('GET', 'profile/taylor/25')->parameters);
		$this->assertEquals('GET /profile/(:any)/(:num)', Router::route('GET', 'profile/taylor/1')->key);
	}

	/**
	 * Test that optional wildcards can be routed.
	 *
	 * @group laravel
	 */
	public function testOptionalWildcardsCanBeRouted()
	{
		Router::register('GET /user/(:num?)', function() {});
		Router::register('GET /profile/(:any)/(:any?)', function() {});

		$this->assertNull(Router::route('GET', 'user/taylor'));
		$this->assertEquals('GET /user/(:num?)', Router::route('GET', 'user')->key);
		$this->assertEquals(array(25), Router::route('GET', 'user/25')->parameters);
		$this->assertEquals('GET /user/(:num?)', Router::route('GET', 'user/1')->key);

		$this->assertNull(Router::route('GET', 'profile/taylor/otwell/test'));
		$this->assertEquals('GET /profile/(:any)/(:any?)', Router::route('GET', 'profile/taylor')->key);
		$this->assertEquals('GET /profile/(:any)/(:any?)', Router::route('GET', 'profile/taylor/25')->key);
		$this->assertEquals('GET /profile/(:any)/(:any?)', Router::route('GET', 'profile/taylor/otwell')->key);
		$this->assertEquals(array('taylor', 'otwell'), Router::route('GET', 'profile/taylor/otwell')->parameters);
	}

	/**
	 * Test that basic controller routing is working.
	 *
	 * @group laravel
	 */
	public function testBasicRouteToControllerIsRouted()
	{
		$this->assertEquals('auth@index', Router::route('GET', 'auth')->action['uses']);
		$this->assertEquals('home@index', Router::route('GET', 'home/index')->action['uses']);
		$this->assertEquals('home@profile', Router::route('GET', 'home/profile')->action['uses']);
		$this->assertEquals('admin.panel@index', Router::route('GET', 'admin/panel')->action['uses']);
		$this->assertEquals('admin.panel@show', Router::route('GET', 'admin/panel/show')->action['uses']);
	}

	/**
	 * Test basic bundle route resolution.
	 *
	 * @group laravel
	 */
	public function testRoutesToBundlesCanBeResolved()
	{
		$this->assertNull(Router::route('GET', 'dashboard/foo'));
		$this->assertEquals('GET /dashboard', Router::route('GET', 'dashboard')->key);
	}

	/**
	 * Test bundle controller route resolution.
	 *
	 * @group laravel
	 */
	public function testBundleControllersCanBeResolved()
	{
		$this->assertEquals('dashboard::panel@index', Router::route('GET', 'dashboard/panel')->action['uses']);
		$this->assertEquals('dashboard::panel@show', Router::route('GET', 'dashboard/panel/show')->action['uses']);
	}

	/**
	 * Test foreign characters can be used in routes.
	 *
	 * @group laravel
	 */
	public function testForeignCharsInRoutes()
	{
		Router::register('GET /'.urlencode('مدرس_رياضيات').'/(:any)', function() {});
		Router::register('GET /'.urlencode('مدرس_رياضيات'), function() {});
		Router::register('GET /'.urlencode('ÇœŪ'), function() {});
		Router::register('GET /'.urlencode('私は料理が大好き'), function() {});

		$this->assertEquals(array('مدرس_رياضيات'), Router::route('GET', urlencode('مدرس_رياضيات').'/'.urlencode('مدرس_رياضيات'))->parameters);
		$this->assertEquals('GET /'.urlencode('مدرس_رياضيات'), Router::route('GET', urlencode('مدرس_رياضيات'))->key);
		$this->assertEquals('GET /'.urlencode('ÇœŪ'), Router::route('GET', urlencode('ÇœŪ'))->key);
		$this->assertEquals('GET /'.urlencode('私は料理が大好き'), Router::route('GET', urlencode('私は料理が大好き'))->key);
	}

}