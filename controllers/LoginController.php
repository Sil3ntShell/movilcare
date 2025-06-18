<?php



namespace Controllers;

use Model\ActiveRecord;
use MVC\Router;
use Exception;

class LoginController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            header('Location: /' . $_ENV['APP_NAME'] . '/');
            exit;
        }

        $router->render('login/index', [], 'layout/layout_login');
    }

    public static function login()
{
    getHeadersApi();

    try {
        if (empty($_POST['usuario_correo']) || empty($_POST['usuario_contra'])) {
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Correo y contraseña son obligatorios'
            ]);
            exit;
        }

        $correo = trim($_POST['usuario_correo']);
        $contrasena = trim($_POST['usuario_contra']);

        // Validar formato de correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'El formato del correo electrónico no es válido'
            ]);
            exit;
        }

        // Buscar el usuario
        $queryExisteUser = "SELECT usuario_id, usuario_nom1, usuario_nom2, usuario_ape1, usuario_ape2, usuario_contra, usuario_dpi, usuario_correo 
                            FROM usuario 
                            WHERE usuario_correo = '$correo' AND usuario_situacion = 1";

        $existeUsuario = ActiveRecord::fetchFirst($queryExisteUser);

        if ($existeUsuario) {
            $passDB = $existeUsuario['usuario_contra'];

            if (password_verify($contrasena, $passDB)) {
                $nombreCompleto = trim($existeUsuario['usuario_nom1'] . ' ' . $existeUsuario['usuario_nom2'] . ' ' . $existeUsuario['usuario_ape1'] . ' ' . $existeUsuario['usuario_ape2']);

                $_SESSION['user'] = $nombreCompleto;
                $_SESSION['user_id'] = $existeUsuario['usuario_id'];
                $_SESSION['correo'] = $existeUsuario['usuario_correo'];
                $_SESSION['dpi'] = $existeUsuario['usuario_dpi'];
                $_SESSION['login'] = true;
                $_SESSION['tiempo_limite'] = time() + (2 * 60 * 60); // 2 horas

                // Inicializar array de permisos
                $_SESSION['permisos'] = [];

                $sqlPermisos = "SELECT ap.*, a.app_nombre_corto, p.permiso_clave 
                                FROM asig_permisos ap 
                                INNER JOIN aplicacion a ON ap.asignacion_app_id = a.app_id 
                                INNER JOIN permiso p ON ap.asignacion_permiso_id = p.permiso_id 
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

                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Usuario logueado exitosamente',
                    'redirect_url' => '/' . $_ENV['APP_NAME'] . '/'
                ]);
            } else {
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La contraseña es incorrecta'
                ]);
            }
        } else {
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'El correo electrónico no está registrado en el sistema'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'codigo' => 0,
            'mensaje' => 'Error al intentar loguearse',
            'detalle' => $e->getMessage()
        ]);
    }

    exit;
}

    

    public static function logout()
    {
        isAuth();
        $_SESSION = [];
        $login = $_ENV['APP_NAME'];
        header("Location: /$login");
        exit;
    }
}
