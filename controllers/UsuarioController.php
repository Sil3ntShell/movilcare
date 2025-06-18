<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Usuario;

class UsuarioController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('usuario/index', []);
    }

    public static function buscarAPI()
    {
        try {
            $condiciones = ["usuario_situacion = 1"];
            $where = implode(" AND ", $condiciones);
            $sql = "SELECT * FROM usuario WHERE $where ORDER BY usuario_nom1 ASC";
            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Usuarios obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los usuarios',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        // Validaciones básicas
        if (empty($_POST['usuario_nom1'])) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El primer nombre es obligatorio']);
            return;
        }

        if (empty($_POST['usuario_ape1'])) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El primer apellido es obligatorio']);
            return;
        }

        if (empty($_POST['usuario_dpi']) || strlen($_POST['usuario_dpi']) != 13) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI debe tener 13 dígitos']);
            return;
        }

        if (empty($_POST['usuario_tel']) || !is_numeric($_POST['usuario_tel'])) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe ser un número']);
            return;
        }

        if (empty($_POST['usuario_correo']) || !filter_var($_POST['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El correo electrónico no es válido']);
            return;
        }

        if (empty($_POST['usuario_contra'])) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'La contraseña es obligatoria']);
            return;
        }

        try {
            $passwordHashed = password_hash($_POST['usuario_contra'], PASSWORD_DEFAULT);

            $usuario = new Usuario([
                'usuario_nom1' => trim($_POST['usuario_nom1']),
                'usuario_nom2' => trim($_POST['usuario_nom2'] ?? ''),
                'usuario_ape1' => trim($_POST['usuario_ape1']),
                'usuario_ape2' => trim($_POST['usuario_ape2'] ?? ''),
                'usuario_tel' => intval($_POST['usuario_tel']),
                'usuario_direc' => trim($_POST['usuario_direc'] ?? ''),
                'usuario_dpi' => trim($_POST['usuario_dpi']),
                'usuario_correo' => trim($_POST['usuario_correo']),
                'usuario_contra' => $passwordHashed,
                'usuario_token' => Usuario::generarToken(),
                'usuario_fotografia' => trim($_POST['usuario_fotografia'] ?? ''),
                'usuario_situacion' => 1
            ]);

            $usuario->crear();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario registrado correctamente']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        $id = $_POST['usuario_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validaciones básicas (igual que en guardar)
        if (empty($_POST['usuario_nom1'])) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El primer nombre es obligatorio']);
            return;
        }

        if (empty($_POST['usuario_ape1'])) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El primer apellido es obligatorio']);
            return;
        }

        if (empty($_POST['usuario_dpi']) || strlen($_POST['usuario_dpi']) != 13) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI debe tener 13 dígitos']);
            return;
        }

        if (empty($_POST['usuario_tel']) || !is_numeric($_POST['usuario_tel'])) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe ser un número']);
            return;
        }

        if (empty($_POST['usuario_correo']) || !filter_var($_POST['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El correo electrónico no es válido']);
            return;
        }

        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no encontrado']);
                return;
            }

            $usuario->sincronizar([
                'usuario_nom1' => trim($_POST['usuario_nom1']),
                'usuario_nom2' => trim($_POST['usuario_nom2'] ?? ''),
                'usuario_ape1' => trim($_POST['usuario_ape1']),
                'usuario_ape2' => trim($_POST['usuario_ape2'] ?? ''),
                'usuario_tel' => intval($_POST['usuario_tel']),
                'usuario_direc' => trim($_POST['usuario_direc'] ?? ''),
                'usuario_dpi' => trim($_POST['usuario_dpi']),
                'usuario_correo' => trim($_POST['usuario_correo']),
                'usuario_fotografia' => trim($_POST['usuario_fotografia'] ?? ''),
                'usuario_situacion' => 1
            ]);

            $resultado = $usuario->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario actualizado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el usuario']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    public static function eliminarAPI()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no encontrado']);
                return;
            }

            $usuario->sincronizar(['usuario_situacion' => 0]);
            $usuario->actualizar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario eliminado correctamente']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}
