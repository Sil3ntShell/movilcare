<?php

function debuguear($variable) {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML
function s($html) {
    $s = htmlspecialchars($html);
    return $s;
}

// Función que revisa que el usuario este autenticado 
function isAuth() {
    // verificar si ya hay sesión antes de iniciarla
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if(!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        // redirigir al login del sistema
        header('Location: /' . $_ENV['APP_NAME'] . '/login');
        exit;
    }
    
    // Verificar si la sesión ha expirado

    if (isset($_SESSION['tiempo_limite']) && time() > $_SESSION['tiempo_limite']) {
        $_SESSION = [];
        session_destroy();
        header('Location: /' . $_ENV['APP_NAME'] . '/login');
        exit;
    }
    
    // Renovar tiempo de sesión si está activa
    $_SESSION['tiempo_limite'] = time() + (2 * 60 * 60);
}

function isAuthApi() {
    getHeadersApi();
    
    // verificar si ya hay sesión antes de iniciarla
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // usar la variable de sesión correcta
    if(!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        echo json_encode([    
            "mensaje" => "No esta autenticado",
            "codigo" => 4,
        ]);
        exit;
    }
    
    // Verificar expiración en API también
    if (isset($_SESSION['tiempo_limite']) && time() > $_SESSION['tiempo_limite']) {
        $_SESSION = [];
        session_destroy();
        echo json_encode([    
            "mensaje" => "Sesión expirada",
            "codigo" => 4,
        ]);
        exit;
    }
    
    // Renovar tiempo de sesión
    $_SESSION['tiempo_limite'] = time() + (2 * 60 * 60);
}

function isNotAuth(){
    // verificar si ya hay sesión antes de iniciarla
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // CORREGIDO: usar la variable de sesión correcta
    if(isset($_SESSION['login']) && $_SESSION['login'] === true) {
        header('Location: /' . $_ENV['APP_NAME'] . '/');
    }
}

function hasPermission(array $permisos){
    $comprobaciones = [];
    foreach ($permisos as $permiso) {
        $comprobaciones[] = !isset($_SESSION[$permiso]) ? false : true;
    }

    if(array_search(true, $comprobaciones) !== false){}else{
        header('Location: /' . $_ENV['APP_NAME'] . '/login');
    }
}

function hasPermissionApi(array $permisos){
    getHeadersApi();
    $comprobaciones = [];
    foreach ($permisos as $permiso) {
        $comprobaciones[] = !isset($_SESSION[$permiso]) ? false : true;
    }

    if(array_search(true, $comprobaciones) !== false){}else{
        echo json_encode([     
            "mensaje" => "No tiene permisos",
            "codigo" => 4,
        ]);
        exit;
    }
}

function getHeadersApi(){
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_clean();
    }
    return header("Content-type:application/json; charset=utf-8");
}

function asset($ruta){
    return "/". $_ENV['APP_NAME']."/public/" . $ruta;
}

// Función para sanitizar cadenas
function sanitizarCadena($valor) {
    return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
}