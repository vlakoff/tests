<?php

class ControllerTest extends PHPUnit_Framework_TestCase {

	/**
	 * Setup the testing environment.
	 */
	public function setUp()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
	}

	/**
	 * Tear down the testing environment.
	 */
	public function tearDown()
	{
		unset(Filter::$filters['test-all-before']);
		unset(Filter::$filters['test-all-after']);
		unset(Filter::$filters['test-profile-before']);
		unset($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Test the Controller::call method.
	 *
	 * @group laravel
	 */
	public function testBasicControllerActionCanBeCalled()
	{
		$this->assertEquals('action_index', Controller::call('auth@index')->content);
		$this->assertEquals('Admin_Panel_Index', Controller::call('admin.panel@index')->content);
		$this->assertEquals('Taylor', Controller::call('auth@profile', array('Taylor'))->content);
		$this->assertEquals('Dashboard_Panel_Index', Controller::call('dashboard::panel@index')->content);
	}

	/**
	 * Test basic controller filters are called.
	 *
	 * @group laravel
	 */
	public function testAssignedBeforeFiltersAreRun()
	{
		$_SERVER['test-all-after'] = false;
		$_SERVER['test-all-before'] = false;

		Controller::call('filter@index');

		$this->assertTrue($_SERVER['test-all-after']);
		$this->assertTrue($_SERVER['test-all-before']);
	}

	/**
	 * Test that "only" filters only apply to their assigned methods.
	 *
	 * @group laravel
	 */
	public function testOnlyFiltersOnlyApplyToTheirAssignedMethods()
	{
		$_SERVER['test-profile-before'] = false;

		Controller::call('filter@index');

		$this->assertFalse($_SERVER['test-profile-before']);

		Controller::call('filter@profile');

		$this->assertTrue($_SERVER['test-profile-before']);
	}

	/**
	 * Test that "except" filters only apply to the excluded methods.
	 *
	 * @group laravel
	 */
	public function testExceptFiltersOnlyApplyToTheExlucdedMethods()
	{
		$_SERVER['test-except'] = false;

		Controller::call('filter@index');
		Controller::call('filter@profile');

		$this->assertFalse($_SERVER['test-except']);

		Controller::call('filter@show');

		$this->assertTrue($_SERVER['test-except']);
	}

	/**
	 * Test that filters can be constrained by the request method.
	 *
	 * @group laravel
	 */
	public function testFiltersCanBeConstrainedByRequestMethod()
	{
		$_SERVER['test-on-post'] = false;

		$_SERVER['REQUEST_METHOD'] = 'GET';
		Controller::call('filter@index');

		$this->assertFalse($_SERVER['test-on-post']);

		$_SERVER['REQUEST_METHOD'] = 'POST';
		Controller::call('filter@index');

		$this->assertTrue($_SERVER['test-on-post']);

		$_SERVER['test-on-get-put'] = false;

		$_SERVER['REQUEST_METHOD'] = 'POST';
		Controller::call('filter@index');

		$this->assertFalse($_SERVER['test-on-get-put']);

		$_SERVER['REQUEST_METHOD'] = 'PUT';
		Controller::call('filter@index');

		$this->assertTrue($_SERVER['test-on-get-put']);
	}

	public function testGlobalBeforeFilterIsNotCalledByController()
	{
		$_SERVER['before'] = false;
		$_SERVER['after'] = false;

		Controller::call('auth@index');

		$this->assertFalse($_SERVER['before']);
		$this->assertFalse($_SERVER['after']);
	}

	/**
	 * Test that before filters can override the controller response.
	 *
	 * @group laravel
	 */
	public function testBeforeFiltersCanOverrideResponses()
	{
		$this->assertEquals('Filtered!', Controller::call('filter@login')->content);
	}

	/**
	 * Test that after filters do not affect the response.
	 *
	 * @group laravel
	 */
	public function testAfterFiltersDoNotAffectControllerResponse()
	{
		$this->assertEquals('action_logout', Controller::call('filter@logout')->content);
	}

	/**
	 * Test that filter parameters are passed to the filter.
	 *
	 * @group laravel
	 */
	public function testFilterParametersArePassedToTheFilter()
	{
		$this->assertEquals('12', Controller::call('filter@edit')->content);
	}

	public function testMultipleFiltersCanBeAssignedToAnAction()
	{
		$_SERVER['test-multi-1'] = false;
		$_SERVER['test-multi-2'] = false;

		Controller::call('filter@save');

		$this->assertTrue($_SERVER['test-multi-1']);
		$this->assertTrue($_SERVER['test-multi-2']);
	}

}