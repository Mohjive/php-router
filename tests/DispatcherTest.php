<?php
include_once(dirname(__FILE__) . '/../lib/SplClassLoader.php');
include_once(dirname(__FILE__) . '/../lib/Dispatcher.php');
include_once(dirname(__FILE__) . '/../lib/Route.php');


/*----------------------------------------------------------------------------*/

class DispatcherTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass() {
                mkdir('./Controllers', 0777, true);


        $contents = "<?php\n"
                  . "class fooClass {\n"
                  . "    public function bar( \$args ) {\n"
                  . "        return 'bar';\n"
                  . "    }\n"
                  . "}\n"
                  . "?>\n"
                  ;

        $fh = fopen('./Controllers/fooClass.php', 'w');
        fwrite($fh, $contents);
        fclose($fh);


        $contents2 = "<?php\n"
                   . "namespace Controllers;"
                  . "class Foo {\n"
                  . "    public function bar( \$args ) {\n"
                  . "        return 'bar';\n"
                  . "    }\n"
                  . "}\n"
                  . "?>\n"
                  ;
        $fh2 = fopen('./Controllers/Foo.php', 'w');
        fwrite($fh2, $contents2);
        fclose($fh2);

        $contents3 = "";


        $fh3 = fopen('./Controllers/noclassnameClass.php', 'w');
        fwrite($fh3, $contents3);
        fclose($fh3);

        $loader = new SplClassLoader('Controllers',  dirname(__FILE__));
        $loader->register();

        $loader = new SplClassLoader(null,  dirname(__FILE__) . '/Controllers');
        $loader->register();

	}
    public static function tearDownAfterClass()
    {

        @unlink('Controllers/fooClass.php');
        @unlink('Controllers/noclassnameClass.php');
        @unlink('Controllers/Foo.php');
          rmdir('./Controllers');
    }

    /**
     * @expectedException AutoLoaderClassFileNotFoundException
     */
    public function testCatchClassFileNotFound()
    {
        $path = '/no_class/bar/55';

        $route = $this->getMock('Route');
        $route->expects($this->any())
              ->method('matchMap')
              ->will($this->returnValue(true));
        $route->expects($this->any())
              ->method('getMapClass')
              ->will($this->returnValue('doesnotExistClass'));
        $route->expects($this->any())
              ->method('getMapMethod')
              ->will($this->returnValue('method'));

        $route->matchMap($path);

        $dispatcher = new Dispatcher;
        $dispatcher->dispatch( $route );
    }

    /**
     * @expectedException AutoLoaderClassNotFoundException
     */
    public function testCatchClassNameNotFound()
    {


        $route = $this->getMock('Route');
        $route->expects($this->any())
              ->method('matchMap')
              ->will($this->returnValue(true));
        $route->expects($this->any())
              ->method('getMapClass')
              ->will($this->returnValue('noclassnameClass'));
        $route->expects($this->any())
              ->method('getMapMethod')
              ->will($this->returnValue('method'));

        $dispatcher = new Dispatcher;
        $dispatcher->dispatch( $route );
    }

    /**
     * @expectedException classNotSpecifiedException
     */
    public function testCatchClassNotSpecified()
    {
        $route = $this->getMock('Route');
        $route->expects($this->any())
              ->method('matchMap')
              ->will($this->returnValue(true));
        $route->expects($this->any())
              ->method('getMapClass')
              ->will($this->returnValue(''));
        $route->expects($this->any())
              ->method('getMapMethod')
              ->will($this->returnValue('method'));

        $dispatcher = new Dispatcher;
        $dispatcher->dispatch( $route );
    }

    /**
     * @expectedException badClassNameException
     */
    public function testCatchBadClassName()
    {
        $route = $this->getMock('Route');
        $route->expects($this->any())
              ->method('matchMap')
              ->will($this->returnValue(true));
        $route->expects($this->any())
              ->method('getMapClass')
              ->will($this->returnValue('foo\"'));
        $route->expects($this->any())
              ->method('getMapMethod')
              ->will($this->returnValue('method'));

        $dispatcher = new Dispatcher;
        $dispatcher->dispatch( $route );
    }

    /**
     * @expectedException methodNotSpecifiedException
     */
    public function testCatchMethodNotSpecified()
    {

        $route = $this->getMock('Route');
        $route->expects($this->any())
              ->method('matchMap')
              ->will($this->returnValue(true));
        $route->expects($this->any())
              ->method('getMapClass')
              ->will($this->returnValue('foo'));
        $route->expects($this->any())
              ->method('getMapMethod')
              ->will($this->returnValue(''));

        $dispatcher = new Dispatcher;
        $dispatcher->dispatch( $route );
    }

    /**
     * @expectedException classMethodNotFoundException
     */
    public function testCatchClassMethodNotFound()
    {

        $route = $this->getMock('Route');
        $route->expects($this->any())
              ->method('matchMap')
              ->will($this->returnValue(true));
        $route->expects($this->any())
              ->method('getMapClass')
              ->will($this->returnValue('foo'));
        $route->expects($this->any())
              ->method('getMapMethod')
              ->will($this->returnValue('nomethod'));

        $dispatcher = new Dispatcher;
        $dispatcher->setSuffix('Class');
        $dispatcher->dispatch( $route );
    }



    public function testSuccessfulDispatch()
    {

        $route = $this->getMock('Route');

        $route->expects($this->any())
              ->method('matchMap')
              ->will($this->returnValue(true));

        $route->expects($this->any())
              ->method('getMapClass')
              ->will($this->returnValue('foo'));

        $route->expects($this->any())
              ->method('getMapMethod')
              ->will($this->returnValue('bar'));


        $route->expects($this->any())
              ->method('getMapArguments')
              ->will($this->returnValue(array(55)));


        $dispatcher = new Dispatcher;
        $dispatcher->setSuffix('Class');

        $this->assertTrue($route->matchMap('/foo/bar/55'));

        $res = $dispatcher->dispatch($route);
        $this->assertEquals('bar', $res);
    }



    public function testNameSpace()
    {

        $route = $this->getMock('Route');

        $route->expects($this->any())
              ->method('matchMap')
              ->will($this->returnValue(true));

        $route->expects($this->any())
              ->method('getMapClass')
              ->will($this->returnValue('Foo'));

        $route->expects($this->any())
              ->method('getMapMethod')
              ->will($this->returnValue('bar'));

        $route->expects($this->any())
              ->method('getMapArguments')
              ->will($this->returnValue(array(55)));

        $route->expects($this->any())
              ->method('getMapNameSpace')
              ->will($this->returnValue('Controllers'));


        $dispatcher = new Dispatcher;

        $this->assertTrue($route->matchMap('/foo/bar/55'));

        $res = $dispatcher->dispatch($route);
        $this->assertEquals('bar', $res);
    }

    public function testMethodsAreChainable()
    {
      $dispatcher = new Dispatcher();

      $this->assertSame($dispatcher, $dispatcher->setSuffix(''));
    }
}

