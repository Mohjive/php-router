<?php
/**
 * provides clean array of key values where the keys have the : removed
 */
class CleanDispatcher extends Dispatcher {

    protected function dispatchController($class, $method, $args, $context = null){

        $obj = new $class($context);
        foreach($args as $key => $arg){
            $newKey = substr($key, 1);
            $args[$newKey] = $arg;
            unset($args[$key]);
        }

        $obj = new $class($context);
        return call_user_func(array($obj, $method), $args);
    }

}