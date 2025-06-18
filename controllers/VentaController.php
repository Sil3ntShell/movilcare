<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Venta;
use Model\DetalleVenta;

class VentaController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('venta/index', []);
    }

    // API: Guardar Venta con Detalle
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'cliente_id', 'empleado_id', 'venta_subtotal', 'venta_descuento', 
            'venta_impuestos', 'venta_total', 'venta_forma_pago', 'venta_estado'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                exit;
            }
        }

        // Validar que haya al menos un detalle
        $detalles = json_decode($_POST['detalles'] ?? '[]', true);
        if (empty($detalles)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Debe agregar al menos un producto o servicio']);
            exit;
        }

        // Validar totales
        $total = floatval($_POST['venta_total']);
        if ($total <= 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El total de la venta debe ser mayor a 0']);
            exit;
        }

        // Crear venta
        try {
            $venta = new Venta([
                'cliente_id' => intval($_POST['cliente_id']),
                'empleado_id' => intval($_POST['empleado_id']),
                'venta_subtotal' => floatval($_POST['venta_subtotal']),
                'venta_descuento' => floatval($_POST['venta_descuento']),
                'venta_impuestos' => floatval($_POST['venta_impuestos']),
                'venta_total' => floatval($_POST['venta_total']),
                'venta_forma_pago' => trim($_POST['venta_forma_pago']),
                'venta_estado' => trim($_POST['venta_estado']),
                'venta_observaciones' => trim($_POST['venta_observaciones'] ?? ''),
                'venta_situacion' => 1
            ]);
            
            $resultado = $venta->crear();
            
            if (!$resultado['resultado']) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al crear la venta']);
                exit;
            }

            $ventaId = $resultado['id'];

            // Crear detalles de venta
            foreach ($detalles as $detalle) {
                $detalleVenta = new DetalleVenta([
                    'venta_id' => $ventaId,
                    'inventario_id' => !empty($detalle['inventario_id']) ? intval($detalle['inventario_id']) : null,
                    'orden_id' => !empty($detalle['orden_id']) ? intval($detalle['orden_id']) : null,
                    'detalle_tipo_item' => trim($detalle['tipo_item'] ?? 'PRODUCTO'),
                    'detalle_descripcion' => trim($detalle['descripcion']),
                    'detalle_cantidad' => intval($detalle['cantidad']),
                    'detalle_precio_unitario' => floatval($detalle['precio_unitario']),
                    'detalle_subtotal' => floatval($detalle['subtotal']),
                    'detalle_situacion' => 1
                ]);

                $resultadoDetalle = $detalleVenta->crear();

                if (!$resultadoDetalle['resultado']) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'Error al crear detalle de venta']);
                    exit;
                }
            }

            // Generar número de venta
            $año = date('Y');
            $mes = date('m');
            $numeroVenta = sprintf("V-%s%s-%04d", $año, $mes, $ventaId);

            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Venta registrada correctamente',
                'numero_venta' => $numeroVenta,
                'venta_id' => $ventaId
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar venta', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Ventas
    public static function buscarAPI()
    {
        try {
            $query = "SELECT * FROM venta WHERE venta_situacion = 1 ORDER BY venta_id DESC";
            $ventas = self::fetchArray($query);

            foreach ($ventas as &$venta) {
                $año = date('Y', strtotime($venta['venta_fecha']));
                $mes = date('m', strtotime($venta['venta_fecha']));
                $venta['numero_venta'] = sprintf("V-%s%s-%04d", $año, $mes, $venta['venta_id']);
                
                $venta['cliente_nombre'] = 'Cliente ID: ' . $venta['cliente_id'];
                $venta['empleado_nombre'] = 'Empleado ID: ' . $venta['empleado_id'];

                $venta['detalle'] = [];
            }

            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Ventas encontradas',
                'data' => $ventas
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cargar ventas',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // API: Eliminar Venta
    public static function eliminarAPI()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['venta_id'] ?? $_POST['venta_id'] ?? $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            exit;
        }

        try {
            $venta = Venta::find($id);
            if (!$venta) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Venta no encontrada']);
                exit;
            }

            $venta->sincronizar(['venta_situacion' => 0]);
            $resultado = $venta->actualizar();

            if ($resultado['resultado']) {
                $deleteDetalles = "UPDATE detalle_venta SET detalle_situacion = 0 WHERE venta_id = " . intval($id);
                self::$db->exec($deleteDetalles);

                echo json_encode(['codigo' => 1, 'mensaje' => 'Venta eliminada correctamente']);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo eliminar la venta']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Cambiar estado de venta
    public static function cambiarEstadoAPI()
    {
        getHeadersApi();
        
        $venta_id = $_POST['venta_id'] ?? null;
        $nuevo_estado = $_POST['nuevo_estado'] ?? null;
        
        if (!$venta_id || !$nuevo_estado) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            exit;
        }

        $estados_validos = ['PENDIENTE', 'PROCESANDO', 'COMPLETADA', 'CANCELADA', 'FACTURADA'];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Estado no válido']);
            exit;
        }

        try {
            $venta = Venta::find($venta_id);
            if (!$venta) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Venta no encontrada']);
                exit;
            }

            $venta->sincronizar(['venta_estado' => $nuevo_estado]);
            $resultado = $venta->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Estado actualizado correctamente']);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el estado']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al actualizar estado', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Obtener detalle específico de una venta
    public static function obtenerDetalleAPI()
    {
        $venta_id = $_GET['venta_id'] ?? null;
        
        if (!$venta_id) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID de venta requerido']);
            return;
        }

        try {
            $query = "SELECT * FROM detalle_venta WHERE venta_id = " . intval($venta_id) . " AND detalle_situacion = 1 ORDER BY detalle_id ASC";
            $detalle = self::fetchArray($query);
            
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Detalle obtenido',
                'data' => $detalle
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener detalle',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}
