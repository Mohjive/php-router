<?php
/**
 * @author Rob Apodaca <rob.apodaca@gmail.com>
 * @copyright Copyright (c) 2009, Rob Apodaca
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://robap.github.com/php-router/
 */
class Dispatcher
{
    /**
     * The suffix used to append to the class name
     * @var string
     */
    protected $suffix;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->setSuffix('');
    }

    /**
     * Attempts to dispatch the supplied Route object. Returns false if it fails
     * @param Route $route
     * @param mixed $context
     * @throws badClassNameException
     * @throws classMethodNotFoundException
     * @throws classNotSpecifiedException
     * @throws methodNotSpecifiedException
     * @return mixed - result of controller method or FALSE on error
     */
    public function dispatch( Route $route, $context = null )
    {
        $namespace  = trim($route->getMapNameSpace());
        $class      = trim($route->getMapClass());
        $method     = trim($route->getMapMethod());
        $arguments  = $route->getMapArguments();

        if( '' === $class )
            throw new classNotSpecifiedException('Class Name not specified in route: ' . $route);

        if( '' === $method )
            throw new methodNotSpecifiedException('Method Name not specified in route: ' . $route);

        //Because the class could have been matched as a dynamic element,
        // it would mean that the value in $class is untrusted. Therefore,
        // it may only contain alphanumeric characters. Anything not matching
        // the regexp is considered potentially harmful.
        $class = str_replace('\\', '', $class);
        preg_match('/^[a-zA-Z0-9_]+$/', $class, $matches);
        if( count($matches) !== 1 )
            throw new badClassNameException('Disallowed characters in class name ' . $class);

        //Apply the suffix
        $class = $class . str_replace($this->getFileExtension(), '', $this->suffix);
        // add namespace to class
        if($namespace){
            $class = $namespace . '\\' . $class;
        }

        $obj = new $class($context);

        //Check for the method
        if( FALSE === method_exists($class, $method))
            throw new classMethodNotFoundException('The method: "' . $method . '" was not found in class: "' . $class . '"');

        //All above checks should have confirmed that the class can be instatiated
        // and the method can be called
        return $this->dispatchController($class, $method, $arguments, $context);
    }

    /**
     * Create instance of controller and dispatch to it's method passing
     * arguments. Override to change behavior.
     *
     * @param string $class
     * @param string $method
     * @param array $args
     * @return mixed - result of controller method
     */
    protected function dispatchController($class, $method, $args, $context = null)
    {
        $obj = new $class($context);
        return call_user_func(array($obj, $method), $args);
    }

    /**
     * Sets a suffix to append to the class name being dispatched
     * @param string $suffix
     * @return Dispatcher
     */
    public function setSuffix( $suffix )
    {
        $this->suffix = $suffix . $this->getFileExtension();

        return $this;
    }

    public function getFileExtension()
    {
        return '.php';
    }
}

class badClassNameException extends Exception{}
class classMethodNotFoundException extends Exception{}
class classNotSpecifiedException extends Exception{}
class methodNotSpecifiedException extends Exception{}

