<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\TipoServicio;

class TipoServicioController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('tiposervicio/index', []);
    }

    public static function buscarAPI()
    {
        try {
            $condiciones = ["tipo_servicio_situacion = 1"];
            $where = implode(" AND ", $condiciones);
            $sql = "SELECT * FROM tipo_servicio WHERE $where ORDER BY tipo_servicio_nombre ASC";
            $data = self::fetchArray($sql);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tipos de servicio obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los tipos de servicio',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        // Validaciones básicas
        if (empty($_POST['tipo_servicio_nombre'])) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre del servicio es obligatorio']);
            return;
        }

        if (empty($_POST['tipo_servicio_precio_base']) || !is_numeric($_POST['tipo_servicio_precio_base']) || $_POST['tipo_servicio_precio_base'] <= 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El precio base debe ser un número mayor a 0']);
            return;
        }

        if (empty($_POST['tipo_servicio_tiempo_estimado']) || !is_numeric($_POST['tipo_servicio_tiempo_estimado']) || $_POST['tipo_servicio_tiempo_estimado'] <= 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El tiempo estimado debe ser un número mayor a 0']);
            return;
        }

        // Crear tipo de servicio
        try {
            $tipoServicio = new TipoServicio([
                'tipo_servicio_nombre' => trim($_POST['tipo_servicio_nombre']),
                'tipo_servicio_descripcion' => trim($_POST['tipo_servicio_descripcion'] ?? ''),
                'tipo_servicio_precio_base' => floatval($_POST['tipo_servicio_precio_base']),
                'tipo_servicio_tiempo_estimado' => intval($_POST['tipo_servicio_tiempo_estimado']),
                'tipo_servicio_situacion' => 1
            ]);

            $tipoServicio->crear();
            echo json_encode(['codigo' => 1, 'mensaje' => 'Tipo de servicio registrado correctamente']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['tipo_servicio_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validaciones básicas (igual que en guardar)
        if (empty($_POST['tipo_servicio_nombre'])) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre del servicio es obligatorio']);
            return;
        }

        if (empty($_POST['tipo_servicio_precio_base']) || !is_numeric($_POST['tipo_servicio_precio_base']) || $_POST['tipo_servicio_precio_base'] <= 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El precio base debe ser un número mayor a 0']);
            return;
        }

        if (empty($_POST['tipo_servicio_tiempo_estimado']) || !is_numeric($_POST['tipo_servicio_tiempo_estimado']) || $_POST['tipo_servicio_tiempo_estimado'] <= 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El tiempo estimado debe ser un número mayor a 0']);
            return;
        }

        // ELIMINAR ESTAS LÍNEAS de tu TipoServicioController.php actual:
        $checkUso = "SELECT COUNT(*) as count FROM orden_trabajo WHERE tipo_servicio_id = " . intval($id) . " AND orden_situacion = 1";
        $resultUso = self::fetchArray($checkUso);

        if ($resultUso[0]['count'] > 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'No se puede modificar: el tipo de servicio está siendo usado en órdenes de trabajo']);
            return;
        }

        try {
            $tipoServicio = TipoServicio::find($id);

            if (!$tipoServicio) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Tipo de servicio no encontrado']);
                return;
            }

            // Sincronizar sin codificar caracteres
            $tipoServicio->sincronizar([
                'tipo_servicio_nombre' => trim($_POST['tipo_servicio_nombre']),
                'tipo_servicio_descripcion' => trim($_POST['tipo_servicio_descripcion'] ?? ''),
                'tipo_servicio_precio_base' => floatval($_POST['tipo_servicio_precio_base']),
                'tipo_servicio_tiempo_estimado' => intval($_POST['tipo_servicio_tiempo_estimado']),
                'tipo_servicio_situacion' => 1
            ]);

            $resultado = $tipoServicio->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Tipo de servicio actualizado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el tipo de servicio']);
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
            $tipoServicio = TipoServicio::find($id);
            if (!$tipoServicio) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Tipo de servicio no encontrado']);
                return;
            }

            $tipoServicio->sincronizar(['tipo_servicio_situacion' => 0]);
            $tipoServicio->actualizar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Tipo de servicio eliminado correctamente']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}