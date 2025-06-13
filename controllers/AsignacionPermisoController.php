<?php

namespace Controllers;

use Exception;
use Model\AsignacionPermiso;
use Model\ActiveRecord;
use MVC\Router;

class AsignacionPermisoController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('asignacion_permiso/index', []);
    }

    public static function guardarAPI()
{
    getHeadersApi();

    $campos = ['asignacion_usuario_id', 'asignacion_permiso_id', 'asignacion_usuario_asigno', 'asignacion_motivo'];

    foreach ($campos as $campo) {
        if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
            return;
        }
    }

    // Validar que los IDs sean números válidos
    $usuario_id = filter_var($_POST['asignacion_usuario_id'], FILTER_VALIDATE_INT);
    $permiso_id = filter_var($_POST['asignacion_permiso_id'], FILTER_VALIDATE_INT);
    $usuario_asigno = filter_var($_POST['asignacion_usuario_asigno'], FILTER_VALIDATE_INT);

    if (!$usuario_id || !$permiso_id || !$usuario_asigno) {
        http_response_code(400);
        echo json_encode(['codigo' => 0, 'mensaje' => 'IDs inválidos']);
        return;
    }

    // Verificar que no sea auto-asignación
    if ($usuario_id === $usuario_asigno) {
        http_response_code(400);
        echo json_encode(['codigo' => 0, 'mensaje' => 'Un usuario no puede asignarse permisos a sí mismo']);
        return;
    }

    // Verificar que el permiso no esté ya asignado al usuario
    $permisoExiste = self::fetchArray(
        "SELECT COUNT(*) as count FROM asig_permisos 
         WHERE asignacion_usuario_id = ? AND asignacion_permiso_id = ? AND asignacion_situacion = 1",
        [$usuario_id, $permiso_id]
    );

    if ($permisoExiste[0]['count'] > 0) {
        http_response_code(400);
        echo json_encode(['codigo' => 0, 'mensaje' => 'Este permiso ya está asignado al usuario']);
        return;
    }

    try {
        // Obtener la fecha actual en formato correcto
        $fechaActual = date('Y-m-d H:i:s');
        
        $asignacion = new AsignacionPermiso([
            'asignacion_usuario_id' => $usuario_id,
            'asignacion_permiso_id' => $permiso_id,
            'asignacion_fecha' => $fechaActual,
            'asignacion_usuario_asigno' => $usuario_asigno,
            'asignacion_motivo' => trim($_POST['asignacion_motivo']),
            'asignacion_situacion' => 1
        ]);

        $resultado = $asignacion->crear();
        
        if ($resultado['resultado']) {
            echo json_encode(['codigo' => 1, 'mensaje' => 'Permiso asignado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al crear la asignación']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
    }
}

    public static function buscarAPI()
    {
        try {
            $sql = "SELECT ap.*, p.permiso_nombre, a.app_nombre_corto,
                           u.usuario_nom1, u.usuario_ape1
                    FROM asig_permisos ap
                    INNER JOIN permiso p ON ap.asignacion_permiso_id = p.permiso_id
                    INNER JOIN aplicacion a ON p.app_id = a.app_id
                    LEFT JOIN usuario u ON ap.asignacion_usuario_id = u.usuario_id
                    WHERE ap.asignacion_situacion = 1
                    ORDER BY ap.asignacion_fecha DESC";
            
            $asignaciones = self::fetchArray($sql);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $asignaciones]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener los datos', 'detalle' => $e->getMessage()]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        $id = $_POST['asignacion_id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        try {
            $asignacion = AsignacionPermiso::find($id);
            if (!$asignacion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Asignación no encontrada']);
                return;
            }

            $asignacion->sincronizar([
                'asignacion_usuario_id' => $_POST['asignacion_usuario_id'],
                'asignacion_permiso_id' => $_POST['asignacion_permiso_id'],
                'asignacion_usuario_asigno' => $_POST['asignacion_usuario_asigno'],
                'asignacion_motivo' => $_POST['asignacion_motivo']
            ]);

            $resultado = $asignacion->actualizar();
            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Asignación actualizada correctamente']);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar']);
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
            $asignacion = AsignacionPermiso::find($id);
            if (!$asignacion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Asignación no encontrada']);
                return;
            }

            $asignacion->sincronizar([
                'asignacion_situacion' => 0,
                'asignacion_fecha_quito' => date('Y-m-d')
            ]);
            $resultado = $asignacion->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Permiso retirado correctamente']);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo retirar el permiso']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}