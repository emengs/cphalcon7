<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2014 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  |          ZhuZongXin <dreamsxin@qq.com>                                 |
  +------------------------------------------------------------------------+
*/

class CustomResponse extends Phalcon\Http\Response {
	public function setContent($content) {
		$e = $this->dispatcher->getLastException();
		$v = $this->dispatcher->getReturnedValue();
		parent::setContent($content.'Response');
	}
}

class ApplicationMvcTest extends PHPUnit_Framework_TestCase
{

	public function testApplicationSingleModule()
	{
		// Creates the autoloader
		$loader = new \Phalcon\Loader();

		$loader->registerDirs(array(
			'unit-tests/controllers/'
		));

		$loader->register();

		$_GET['_url'] = '/test2/index';

		Phalcon\Di::reset();
		$di = new Phalcon\Di\FactoryDefault();

		$di->set('view', function() {
			$view = new \Phalcon\Mvc\View();
			$view->setViewsDir('unit-tests/views/');
			return $view;
		});

		$application = new Phalcon\Mvc\Application();
		$application->setDi($di);
		$this->assertEquals($application->handle()->getContent(), '<html>here</html>'.PHP_EOL);

		$loader->unregister();
	}

	public function testApplicationModulesDefinition()
	{
		// Creates the autoloader
		$loader = new \Phalcon\Loader();

		$loader->registerNamespaces(array(
			'Frontend\Controllers' => 'unit-tests/modules/frontend/controllers/',
			'Backend\Controllers' => 'unit-tests/modules/backend/controllers/'
		));

		$loader->register();

		$_GET['_url'] = '/index';

		Phalcon\Di::reset();

		$di = new Phalcon\Di\FactoryDefault();

		$di->set('router', function(){

			$router = new Phalcon\Mvc\Router(false);

			$router->add('/index', array(
				'controller' => 'index',
				'module' => 'frontend',
				'namespace' => 'Frontend\Controllers\\'
			));

			return $router;
		});

		$application = new Phalcon\Mvc\Application();

		$application->registerModules(array(
			'frontend' => array(
				'path' => 'unit-tests/modules/frontend/Module.php',
				'className' => 'Frontend\Module',
			),
			'backend' => array(
				'path' => 'unit-tests/modules/backend/Module.php',
				'className' => 'Backend\Module',
			),
		));

		$this->assertEquals($application->handle()->getContent(), '<html>here</html>'.PHP_EOL);

		$loader->unregister();
	}

	public function testApplicationModulesDefinitionClosure()
	{
		// Creates the autoloader
		$loader = new \Phalcon\Loader();

		$loader->registerNamespaces(array(
			'Frontend\Controllers' => 'unit-tests/modules/frontend/controllers/',
			'Backend\Controllers' => 'unit-tests/modules/backend/controllers/'
		));

		$loader->register();

		$_GET['_url'] = '/login';

		Phalcon\Di::reset();

		$di = new Phalcon\Di\FactoryDefault();

		$di->set('router', function(){

			$router = new Phalcon\Mvc\Router(false);

			$router->add('/index', array(
				'controller' => 'index',
				'module' => 'frontend',
				'namespace' => 'Frontend\Controllers\\'
			));

			$router->add('/login', array(
				'controller' => 'login',
				'module' => 'backend',
				'namespace' => 'Backend\Controllers\\'
			));

			return $router;
		});

		$application = new Phalcon\Mvc\Application();

		$view = new \Phalcon\Mvc\View();

		$application->registerModules(array(
			'frontend' => function($di) use ($view) {
				$di->set('view', function() use ($view) {
					$view = new \Phalcon\Mvc\View();
					$view->setViewsDir('unit-tests/modules/frontend/views/');
					return $view;
				});
			},
			'backend' => function($di) use ($view) {
				$di->set('view', function() use($view) {
					$view->setViewsDir('unit-tests/modules/backend/views/');
					return $view;
				});
			},
		));

		$this->assertEquals($application->handle()->getContent(), '<html>here</html>'.PHP_EOL);

		$loader->unregister();
	}

	public function testApplicationModuleNamespaceDefinition()
	{
		Phalcon\Di::reset();

		// Creates the autoloader
		$loader = new \Phalcon\Loader();

		$loader->registerNamespaces(array(
			'Frontend\Controllers' => 'unit-tests/modules/frontend/controllers/',
		));

		$loader->register();

		$di = new Phalcon\Di\FactoryDefault();

		$di->set('router', function(){
			$router = new Phalcon\Mvc\Router(false);

			$router->add('/index', array(
				'controller' => 'index',
				'module' => 'frontend',
			));

			return $router;
		});

		$application = new Phalcon\Mvc\Application();

		$application->registerModules(array(
			'frontend' => array(
				'path' => 'unit-tests/modules/frontend/Module.php',
				'className' => 'Frontend\Module',
				'namespaceName' => 'Frontend\Controllers',
			)
		));

		$this->assertEquals($application->handle('/index')->getContent(), '<html>here</html>'.PHP_EOL);

		$loader->unregister();
	}

	public function testHMVC()
	{
		// Creates the autoloader
		$loader = new \Phalcon\Loader();

		$loader->registerDirs(array(
			'unit-tests/controllers/'
		));

		$loader->register();

		$_GET['_url'] = '/hmvc/one';

		Phalcon\Di::reset();
		$di = new Phalcon\Di\FactoryDefault();

		$application = new Phalcon\Mvc\Application();
		$di->set('view', function() {
			$view = new \Phalcon\Mvc\View();
			$view->disable();
			return $view;
		});

		$this->assertEquals($application->handle()->getContent(), 'one-two-one');

		$loader->unregister();
	}

	public function testApplicationCustomResponse()
	{
		// Creates the autoloader
		$loader = new \Phalcon\Loader();

		$loader->registerDirs(array(
			'unit-tests/controllers/'
		));

		$loader->register();

		$_GET['_url'] = '/continue2/index';

		Phalcon\Di::reset();
		$di = new Phalcon\Di\FactoryDefault();

		$di->set('view', function() {
			$view = new \Phalcon\Mvc\View();
			$view->setViewsDir('unit-tests/views/');
			return $view;
		}, TRUE);
		$di->set('response', 'CustomResponse');
		$di->view->setVar('after', 'afterExecuteRoute');

		$application = new Phalcon\Mvc\Application();
		$application->setDi($di);
		$this->assertEquals($application->handle()->getContent(), "<html>Continue afterExecuteRoute</html>".PHP_EOL."Response");
		$loader->unregister();
	}

	public function testApplicationAttachEvent()
	{
		// Creates the autoloader
		$loader = new \Phalcon\Loader();

		$loader->registerDirs(array(
			'unit-tests/controllers/'
		));

		$loader->register();

		$_GET['_url'] = '/continue2/index';

		Phalcon\Di::reset();
		$di = new Phalcon\Di\FactoryDefault();

		$di->set('view', function() {
			$view = new \Phalcon\Mvc\View();
			$view->setViewsDir('unit-tests/views/');
			return $view;
		}, TRUE);

		$di->dispatcher->attachEvent('afterExecuteRoute', function(){
			$this->di->view->setVar('after', 'afterExecuteRoute');
		});

		$application = new Phalcon\Mvc\Application();
		$application->setDi($di);
		$application->attachEvent('afterSendResponse', function($response){
			$response->setContent($response->getContent().'Response');
		});
		$this->assertEquals($application->handle()->getContent(), "<html>Continue afterExecuteRoute</html>".PHP_EOL."Response");
		$loader->unregister();
	}

}
