<?php

namespace Controllers;

use Exception;
use Model\Permiso;
use MVC\Router;
use Model\ActiveRecord;

class PermisoController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        isAuth();
        $router->render('permiso/index', []);
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = ['usuario_id', 'app_id', 'permiso_nombre', 'permiso_clave'];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar longitudes
        if (strlen($_POST['permiso_nombre']) < 3) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre del permiso debe tener al menos 3 caracteres']);
            return;
        }

        if (strlen($_POST['permiso_clave']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'La clave del permiso debe tener al menos 2 caracteres']);
            return;
        }

        try {
            // Verificar si ya existe un permiso con la misma clave en la misma aplicación
            if (Permiso::verificarPermisoExistente($_POST['usuario_id'], $_POST['app_id'], $_POST['permiso_clave'])) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe un permiso con esta clave para este usuario y aplicación']);
                return;
            }

            $permiso = new Permiso([
                'usuario_id' => $_POST['usuario_id'],
                'app_id' => $_POST['app_id'],
                'permiso_nombre' => ucwords(strtolower(trim($_POST['permiso_nombre']))),
                'permiso_clave' => strtoupper(str_replace(' ', '_', trim($_POST['permiso_clave']))),
                'permiso_desc' => trim($_POST['permiso_desc'] ?? ''),
                'permiso_tipo' => $_POST['permiso_tipo'] ?? 'FUNCIONAL',
                'permiso_fecha' => date('Y-m-d'),
                'permiso_usuario_asigno' => $_POST['permiso_usuario_asigno'] ?? $_POST['usuario_id'],
                'permiso_motivo' => trim($_POST['permiso_motivo'] ?? ''),
                'permiso_situacion' => 1
            ]);

            $resultado = $permiso->crear();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Permiso registrado correctamente']);
        } catch (Exception $e) {
            error_log("Error guardando permiso: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    public static function buscarAPI()
    {
        try {
            $appId = $_GET['app_id'] ?? null;
            $usuarioId = $_GET['usuario_id'] ?? null;
            $tipo = $_GET['tipo'] ?? null;
            
            $sql = "SELECT p.*, a.app_nombre_corto, a.app_nombre_largo,
                           u.usuario_nom1, u.usuario_ape1,
                           ua.usuario_nom1 as asigno_nom1, ua.usuario_ape1 as asigno_ape1
                    FROM permiso p 
                    INNER JOIN aplicacion a ON p.app_id = a.app_id
                    LEFT JOIN usuario u ON p.usuario_id = u.usuario_id
                    LEFT JOIN usuario ua ON p.permiso_usuario_asigno = ua.usuario_id
                    WHERE p.permiso_situacion = 1";
            
            if ($appId) {
                $sql .= " AND p.app_id = " . intval($appId);
            }
            
            if ($usuarioId) {
                $sql .= " AND p.usuario_id = " . intval($usuarioId);
            }
            
            if ($tipo) {
                $sql .= " AND p.permiso_tipo = '" . self::sanitizarCadena($tipo) . "'";
            }
            
            $sql .= " ORDER BY a.app_nombre_corto, p.permiso_tipo, p.permiso_nombre";
            
            $permisos = self::fetchArray($sql);
            
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $permisos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener los datos', 'detalle' => $e->getMessage()]);
        }
    }

    public static function tiposAPI()
    {
        try {
            $tipos = [
                ['valor' => 'FUNCIONAL', 'texto' => 'Funcional - Acceso a funcionalidades'],
                ['valor' => 'MENU', 'texto' => 'Menú - Acceso a opciones de menú'],
                ['valor' => 'REPORTE', 'texto' => 'Reporte - Generación de reportes'],
                ['valor' => 'ADMIN', 'texto' => 'Administración - Gestión del sistema'],
                ['valor' => 'ESPECIAL', 'texto' => 'Especial - Permisos especiales']
            ];
            
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $tipos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener tipos', 'detalle' => $e->getMessage()]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        $id = $_POST['permiso_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        $campos = ['usuario_id', 'app_id', 'permiso_nombre', 'permiso_clave'];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validaciones
        if (strlen($_POST['permiso_nombre']) < 3) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre del permiso debe tener al menos 3 caracteres']);
            return;
        }

        if (strlen($_POST['permiso_clave']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'La clave del permiso debe tener al menos 2 caracteres']);
            return;
        }

        try {
            $permiso = Permiso::find($id);

            if (!$permiso) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Permiso no encontrado']);
                return;
            }

            // Verificar si ya existe otro permiso con la misma clave
            if (Permiso::verificarPermisoExistente($_POST['usuario_id'], $_POST['app_id'], $_POST['permiso_clave'], $id)) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe otro permiso con esta clave para este usuario y aplicación']);
                return;
            }

            $permiso->sincronizar([
                'usuario_id' => $_POST['usuario_id'],
                'app_id' => $_POST['app_id'],
                'permiso_nombre' => ucwords(strtolower(trim($_POST['permiso_nombre']))),
                'permiso_clave' => strtoupper(str_replace(' ', '_', trim($_POST['permiso_clave']))),
                'permiso_desc' => trim($_POST['permiso_desc'] ?? ''),
                'permiso_tipo' => $_POST['permiso_tipo'] ?? 'FUNCIONAL',
                'permiso_usuario_asigno' => $_POST['permiso_usuario_asigno'] ?? $_POST['usuario_id'],
                'permiso_motivo' => trim($_POST['permiso_motivo'] ?? ''),
                'permiso_situacion' => 1
            ]);

            $resultado = $permiso->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Permiso actualizado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el permiso']);
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
            $permiso = Permiso::find($id);
            if (!$permiso) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Permiso no encontrado']);
                return;
            }

            $permiso->sincronizar(['permiso_situacion' => 0]);
            $resultado = $permiso->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Permiso eliminado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo eliminar el permiso']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }

    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}