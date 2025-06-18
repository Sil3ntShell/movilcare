<?php

namespace Controllers;

use Model\ActiveRecord;
use MVC\Router;
use Exception;

class LoginController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        // CORREGIDO: verificar si ya hay sesión antes de iniciarla
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            header('Location: /' . $_ENV['APP_NAME'] . '/');
            exit;
        }

        $router->render('login/index', [], 'layout/layout_login');
    }

    public static function login()
    {
        // Limpiar cualquier output previo
        if (ob_get_level()) {
            ob_clean();
        }
        
        getHeadersApi();
        
        // verificar si ya hay sesión antes de iniciarla
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            if (empty($_POST['usuario_correo']) || empty($_POST['usuario_contra'])) {
                if (ob_get_level()) ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Correo y contraseña son obligatorios'
                ], JSON_UNESCAPED_SLASHES);
                exit;
            }

            $correo = trim($_POST['usuario_correo']);
            $contrasena = trim($_POST['usuario_contra']);

            // Validar formato de correo
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                if (ob_get_level()) ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El formato del correo electrónico no es válido'
                ], JSON_UNESCAPED_SLASHES);
                exit;
            }

            // usar sanitizarCadena y quote para evitar SQL injection
            $correoSanitizado = sanitizarCadena($correo);
            $queryExisteUser = "SELECT usuario_id, usuario_nom1, usuario_nom2, usuario_ape1, usuario_ape2, usuario_contra, usuario_dpi, usuario_correo 
                                FROM usuario 
                                WHERE usuario_correo = " . self::$db->quote($correoSanitizado) . " AND usuario_situacion = 1";

            $existeUsuario = ActiveRecord::fetchFirst($queryExisteUser);

            // verificar mejor si el usuario existe
            if ($existeUsuario && is_array($existeUsuario) && isset($existeUsuario['usuario_contra'])) {
                $passDB = $existeUsuario['usuario_contra'];

                // verificar si la contraseña está hasheada o es texto plano
                $passwordValid = false;
                if (strlen($passDB) > 10 && strpos($passDB, '$') === 0) {
                    // La contraseña parece estar hasheada
                    $passwordValid = password_verify($contrasena, $passDB);
                } else {
                    // La contraseña está en texto plano (para desarrollo)
                    $passwordValid = ($contrasena === $passDB);
                }

                if ($passwordValid) {
                    $nombreCompleto = trim($existeUsuario['usuario_nom1'] . ' ' . $existeUsuario['usuario_nom2'] . ' ' . $existeUsuario['usuario_ape1'] . ' ' . $existeUsuario['usuario_ape2']);

                    $_SESSION['user'] = $nombreCompleto;
                    $_SESSION['user_id'] = $existeUsuario['usuario_id'];
                    $_SESSION['correo'] = $existeUsuario['usuario_correo'];
                    $_SESSION['dpi'] = $existeUsuario['usuario_dpi'];
                    $_SESSION['login'] = true;
                    $_SESSION['tiempo_limite'] = time() + (2 * 60 * 60); // 2 horas

                    // Inicializar array de permisos
                    $_SESSION['permisos'] = [];

                    // la consulta de permisos tenía error en el JOIN
                    $sqlPermisos = "SELECT ap.*, a.app_nombre_corto, p.permiso_clave 
                                    FROM asig_permisos ap 
                                    INNER JOIN permiso p ON ap.asignacion_permiso_id = p.permiso_id 
                                    INNER JOIN aplicacion a ON p.app_id = a.app_id 
                                    WHERE ap.asignacion_usuario_id = {$existeUsuario['usuario_id']} 
                                    AND ap.asignacion_situacion = 1 
                                    AND a.app_situacion = 1 
                                    AND p.permiso_situacion = 1";

                    try {
                        $permisos = ActiveRecord::fetchArray($sqlPermisos);

                        if (!empty($permisos)) {
                            foreach ($permisos as $permiso) {
                                $_SESSION[$permiso['permiso_clave']] = true;
                                $_SESSION['permisos'][] = $permiso['permiso_clave'];
                            }
                        } else {
                            // Si no tiene permisos asignados, solo acceso básico
                            $_SESSION['USER'] = true;
                        }

                    } catch (Exception $dbError) {
                        $_SESSION['USER'] = true;
                    }

                    // Respuesta simplificada
                    if (ob_get_level()) {
                        ob_clean();
                    }
                    
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(200);
                    
                    echo json_encode([
                        'codigo' => 1,
                        'mensaje' => 'Usuario logueado exitosamente',
                        'redirect_url' => '/' . $_ENV['APP_NAME'] . '/'
                    ], JSON_UNESCAPED_SLASHES);
                    
                    exit;
                } else {
                    if (ob_get_level()) ob_clean();
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(401);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'La contraseña es incorrecta'
                    ], JSON_UNESCAPED_SLASHES);
                    exit;
                }
            } else {
                if (ob_get_level()) ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El correo electrónico no está registrado en el sistema'
                ], JSON_UNESCAPED_SLASHES);
                exit;
            }
        } catch (Exception $e) {
            if (ob_get_level()) ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al intentar loguearse',
                'detalle' => $e->getMessage()
            ], JSON_UNESCAPED_SLASHES);
            exit;
        }

        exit;
    }

    public static function logout()
    {
        // verificar si ya hay sesión antes de iniciarla
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        isAuth();
        $_SESSION = [];
        session_destroy();
        $login = $_ENV['APP_NAME'];
        header("Location: /$login/login");
        exit;
    }
}