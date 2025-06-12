<?php

namespace Controllers;

use MVC\Router;

class AppController {
    public static function index(Router $router){
       
        $router->render('pages/index', []);
        //  $router->render('login/index', [], 'layout/layout_login');
    }

}