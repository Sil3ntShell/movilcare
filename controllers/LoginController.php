<?php

namespace Controllers;

use Model\ActiveRecord;
use MVC\Router;
use Exception;

class LoginController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('login/index', [], 'layout/layout_login');
    }

    // Método para la API de login (llamado desde JavaScript)
    public static function loginAPI() 
    {
        getHeadersApi();
        
        try {
            $dpi = htmlspecialchars($_POST['usu_codigo'] ?? '');
            $contrasena = htmlspecialchars($_POST['usu_password'] ?? '');

            if (empty($dpi) || empty($contrasena)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'DPI y contraseña son requeridos'
                ]);
                exit;
            }

            $queryExisteUser = "SELECT usuario_id, usuario_nom1, usuario_nom2, usuario_ape1, usuario_ape2, usuario_contra, usuario_dpi, rol_id 
                               FROM usuario 
                               WHERE usuario_dpi = '$dpi' AND usuario_situacion = 1";

            $existeUsuario = ActiveRecord::fetchArray($queryExisteUser)[0] ?? null;

            if ($existeUsuario) {
                $passDB = $existeUsuario['usuario_contra'];

                if (password_verify($contrasena, $passDB)) {
                    // Verificar si ya hay una sesión activa
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }

                    $nombreCompleto = trim($existeUsuario['usuario_nom1'] . ' ' . $existeUsuario['usuario_nom2'] . ' ' . $existeUsuario['usuario_ape1'] . ' ' . $existeUsuario['usuario_ape2']);
                    
                    $_SESSION['user_id'] = $existeUsuario['usuario_id'];
                    $_SESSION['user'] = $nombreCompleto;
                    $_SESSION['dpi'] = $dpi;
                    $_SESSION['rol_id'] = $existeUsuario['rol_id'];
                    $_SESSION['login_time'] = time();

                    // Actualizar último acceso
                    $updateAcceso = "UPDATE usuario SET usuario_ultimo_acceso = CURRENT YEAR TO SECOND WHERE usuario_id = " . $existeUsuario['usuario_id'];
                    ActiveRecord::SQL($updateAcceso);

                    echo json_encode([
                        'codigo' => 1,
                        'mensaje' => 'Usuario logueado exitosamente',
                        'usuario' => $nombreCompleto
                    ]);
                } else {
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'La contraseña que ingresó es incorrecta'
                    ]);
                }
            } else {
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario que intenta loguearse NO EXISTE'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al intentar loguearse',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // Método heredado (por si se usa desde formularios tradicionales)
    public static function login() 
    {
        self::loginAPI();
    }

    public static function logout()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: /empresa_celulares/login');
        exit;
    }
}