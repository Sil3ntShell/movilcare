<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use Model\Rol;
use MVC\Router;

class RolController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('rol/index', []);
    }

    // API: Guardar Rol
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'rol_nombre', 'rol_descripcion'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar nombre de rol
        if (strlen($_POST['rol_nombre']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de rol demasiado corto']);
            return;
        }

        // Verificar duplicidad de nombre
        $existe = Rol::verificarRolExistente($_POST['rol_nombre']);
        if ($existe['nombre_existe']) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de rol ya registrado']);
            return;
        }

        // Crear rol
        try {
            $rol = new Rol([
                'rol_nombre' => $_POST['rol_nombre'],
                'rol_descripcion' => $_POST['rol_descripcion'],
                'rol_situacion' => 1
            ]);
            $rol->crear();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Rol registrado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Roles
    public static function buscarAPI()
    {
        try {
            $roles = self::fetchArray("SELECT * FROM rol WHERE rol_situacion = 1 ORDER BY rol_nombre ASC");
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $roles]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener los datos', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Modificar Rol
    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['rol_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validar campos requeridos
        $campos = [
            'rol_nombre', 'rol_descripcion'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar nombre de rol
        if (strlen($_POST['rol_nombre']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de rol demasiado corto']);
            return;
        }

        try {
            $rol = Rol::find($id);

            if (!$rol) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Rol no encontrado']);
                return;
            }

            // Verificar duplicidad de nombre (excluyendo el rol actual)
            $existe = Rol::verificarRolExistente($_POST['rol_nombre'], $id);
            if ($existe['nombre_existe']) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de rol ya registrado por otro rol']);
                return;
            }

            // Sincronizar todos los campos necesarios
            $rol->sincronizar([
                'rol_nombre' => $_POST['rol_nombre'],
                'rol_descripcion' => $_POST['rol_descripcion'],
                'rol_situacion' => 1
            ]);

            $resultado = $rol->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Rol actualizado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el rol']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Eliminar Rol (lógico)
    public static function eliminarAPI()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            // Verificar si el rol tiene usuarios asignados
            $usuariosConRol = self::fetchArray("SELECT COUNT(*) as total FROM usuario WHERE rol_id = " . intval($id) . " AND usuario_situacion = 1");
            
            if ($usuariosConRol[0]['total'] > 0) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se puede eliminar el rol porque tiene usuarios asignados']);
                return;
            }

            $rol = Rol::find($id);
            if (!$rol) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Rol no encontrado']);
                return;
            }

            $rol->sincronizar(['rol_situacion' => 0]);
            $rol->actualizar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Rol eliminado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}