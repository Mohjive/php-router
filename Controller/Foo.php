<?php
namespace Controller;
class Foo {
    function index(){
        echo 'index';
    }


    function test($alpha = null, $beta = null, $gama = null){
        echo 'foo::test()' . PHP_EOL;
        
        print_r(func_get_args());
    }
}