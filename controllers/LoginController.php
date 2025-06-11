<?php

namespace Controllers;

use MVC\Router;

class LoginController {
    public static function renderizarPagina(Router $router) {

        $router->render('login/index', [], $layout = 'layout/layout_login');
    }


}