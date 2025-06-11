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

        try {
            // Validar datos básicos de la venta
            if (empty($_POST['cliente_id']) || !is_numeric($_POST['cliente_id'])) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Debe seleccionar un cliente']);
                return;
            }

            if (empty($_POST['usuario_id']) || !is_numeric($_POST['usuario_id'])) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no válido']);
                return;
            }

            // Validar que haya al menos un detalle
            $detalles = json_decode($_POST['detalles'] ?? '[]', true);
            if (empty($detalles)) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Debe agregar al menos un producto o servicio']);
                return;
            }

            // Validar totales
            $subtotal = floatval($_POST['venta_subtotal'] ?? 0);
            $descuento = floatval($_POST['venta_descuento'] ?? 0);
            $impuestos = floatval($_POST['venta_impuestos'] ?? 0);
            $total = floatval($_POST['venta_total'] ?? 0);

            if ($total <= 0) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'El total de la venta debe ser mayor a 0']);
                return;
            }

            // Iniciar transacción
            self::$db->beginTransaction();

            // Crear venta
            $venta = new Venta([
                'cliente_id' => intval($_POST['cliente_id']),
                'usuario_id' => intval($_POST['usuario_id']),
                'venta_subtotal' => $subtotal,
                'venta_descuento' => $descuento,
                'venta_impuestos' => $impuestos,
                'venta_total' => $total,
                'venta_forma_pago' => trim($_POST['venta_forma_pago'] ?? 'EFECTIVO'),
                'venta_estado' => trim($_POST['venta_estado'] ?? 'PENDIENTE'),
                'venta_observaciones' => trim($_POST['venta_observaciones'] ?? ''),
                'venta_situacion' => 1
            ]);

            $resultadoVenta = $venta->crear();

            if (!$resultadoVenta || !$resultadoVenta['resultado']) {
                self::$db->rollBack();
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al crear la venta']);
                return;
            }

            $ventaId = $resultadoVenta['id'];

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

                if (!$resultadoDetalle || !$resultadoDetalle['resultado']) {
                    self::$db->rollBack();
                    echo json_encode(['codigo' => 0, 'mensaje' => 'Error al crear detalle de venta']);
                    return;
                }

                // Actualizar inventario si es producto (corregido el nombre del campo)
                if (!empty($detalle['inventario_id'])) {
                    $updateInventario = "UPDATE inventario SET inventario_stock_disponible = inventario_stock_disponible - " . intval($detalle['cantidad']) . 
                                      " WHERE inventario_id = " . intval($detalle['inventario_id']);
                    self::$db->exec($updateInventario);
                }
            }

            // Confirmar transacción
            self::$db->commit();

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
            self::$db->rollBack();
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar venta', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Ventas - Compatible con Informix
    public static function buscarAPI()
    {
        try {
            $query = "SELECT 
                        v.venta_id,
                        v.cliente_id,
                        v.usuario_id,
                        v.venta_fecha,
                        v.venta_subtotal,
                        v.venta_descuento,
                        v.venta_impuestos,
                        v.venta_total,
                        v.venta_forma_pago,
                        v.venta_estado,
                        v.venta_observaciones,
                        v.venta_situacion,
                        TRIM(c.cliente_nom1 || ' ' || c.cliente_ape1) as cliente_nombre,
                        c.cliente_tel as cliente_telefono,
                        TRIM(u.usuario_nom1 || ' ' || u.usuario_ape1) as usuario_nombre
                      FROM venta v
                      LEFT JOIN cliente c ON v.cliente_id = c.cliente_id
                      LEFT JOIN usuario u ON v.usuario_id = u.usuario_id
                      WHERE v.venta_situacion = 1
                      ORDER BY v.venta_fecha DESC, v.venta_id DESC";
                      
            $ventas = self::fetchArray($query);
            
            // Procesar datos adicionales
            foreach ($ventas as &$venta) {
                // Formatear fecha
                if ($venta['venta_fecha']) {
                    $venta['fecha_formateada'] = date('d/m/Y H:i', strtotime($venta['venta_fecha']));
                }
                
                // Formatear montos
                $venta['subtotal_formateado'] = 'Q. ' . number_format($venta['venta_subtotal'], 2);
                $venta['descuento_formateado'] = 'Q. ' . number_format($venta['venta_descuento'], 2);
                $venta['impuestos_formateado'] = 'Q. ' . number_format($venta['venta_impuestos'], 2);
                $venta['total_formateado'] = 'Q. ' . number_format($venta['venta_total'], 2);
                
                // Generar número de venta
                $año = date('Y', strtotime($venta['venta_fecha']));
                $mes = date('m', strtotime($venta['venta_fecha']));
                $venta['numero_venta'] = sprintf("V-%s%s-%04d", $año, $mes, $venta['venta_id']);

                // Obtener detalle de la venta
                $ventaObj = new Venta($venta);
                $venta['detalle'] = $ventaObj->obtenerDetalle();
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

    // API: Obtener productos del inventario - Compatible con Informix
    public static function obtenerProductosAPI()
    {
        try {
            $query = "SELECT 
                        i.inventario_id,
                        TRIM(m.marca_nombre || ' ' || mo.modelo_nombre || ' - ' || i.inventario_observaciones) as inventario_descripcion,
                        i.inventario_precio_venta,
                        i.inventario_stock_disponible as inventario_stock,
                        m.marca_nombre,
                        mo.modelo_nombre
                      FROM inventario i
                      LEFT JOIN modelo mo ON i.modelo_id = mo.modelo_id
                      LEFT JOIN marca m ON mo.marca_id = m.marca_id
                      WHERE i.inventario_situacion = 1 AND i.inventario_stock_disponible > 0
                      ORDER BY m.marca_nombre ASC, mo.modelo_nombre ASC";
                      
            $productos = self::fetchArray($query);
            
            // Debug: Log para verificar productos
            error_log("Productos obtenidos: " . count($productos));
            
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $productos]);
        } catch (Exception $e) {
            error_log("Error en obtenerProductosAPI: " . $e->getMessage());
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener productos', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Obtener órdenes de trabajo completadas para vender como servicio - Compatible con Informix
    public static function obtenerServiciosAPI()
    {
        try {
            $query = "SELECT 
                        ot.orden_id,
                        ot.orden_diagnostico,
                        ot.orden_costo_total,
                        TRIM(c.cliente_nom1 || ' ' || c.cliente_ape1) as cliente_nombre,
                        ts.tipo_servicio_nombre,
                        r.recepcion_marca,
                        r.recepcion_modelo,
                        r.recepcion_tipo_celular,
                        ot.orden_estado
                      FROM orden_trabajo ot
                      LEFT JOIN recepcion r ON ot.recepcion_id = r.recepcion_id
                      LEFT JOIN cliente c ON r.cliente_id = c.cliente_id
                      LEFT JOIN tipo_servicio ts ON ot.tipo_servicio_id = ts.tipo_servicio_id
                      WHERE ot.orden_situacion = 1 
                      AND ot.orden_estado IN ('COMPLETADA', 'REPARADO')
                      AND ot.orden_costo_total > 0
                      ORDER BY ot.orden_fecha_asignacion DESC";
                      
            $servicios = self::fetchArray($query);
            
            // Debug: Log para verificar qué datos se obtienen
            error_log("Servicios obtenidos: " . count($servicios));
            error_log("Query: " . $query);
            
            // Procesar datos para asegurar que tengan la información necesaria
            foreach ($servicios as &$servicio) {
                // Asegurar que todos los campos tengan valores por defecto
                $servicio['orden_diagnostico'] = $servicio['orden_diagnostico'] ?: 'Servicio completado';
                $servicio['tipo_servicio_nombre'] = $servicio['tipo_servicio_nombre'] ?: 'Servicio';
                $servicio['recepcion_marca'] = $servicio['recepcion_marca'] ?: '';
                $servicio['recepcion_modelo'] = $servicio['recepcion_modelo'] ?: '';
                $servicio['orden_costo_total'] = floatval($servicio['orden_costo_total'] ?: 0);
                
                // Crear descripción del dispositivo
                $dispositivo = trim($servicio['recepcion_marca'] . ' ' . $servicio['recepcion_modelo']);
                if (empty($dispositivo)) {
                    $dispositivo = $servicio['recepcion_tipo_celular'] ?: 'Dispositivo';
                }
                $servicio['dispositivo_descripcion'] = $dispositivo;
            }
            
            echo json_encode([
                'codigo' => 1, 
                'mensaje' => 'Servicios obtenidos correctamente', 
                'data' => $servicios,
                'total' => count($servicios)
            ]);
            
        } catch (Exception $e) {
            error_log("Error en obtenerServiciosAPI: " . $e->getMessage());
            echo json_encode([
                'codigo' => 0, 
                'mensaje' => 'Error al obtener servicios', 
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
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            // Iniciar transacción
            self::$db->beginTransaction();

            // Eliminar detalles (lógico)
            $deleteDetalles = "UPDATE detalle_venta SET detalle_situacion = 0 WHERE venta_id = " . intval($id);
            self::$db->exec($deleteDetalles);

            // Eliminar venta (lógico)
            $deleteVenta = "UPDATE venta SET venta_situacion = 0 WHERE venta_id = " . intval($id);
            self::$db->exec($deleteVenta);

            self::$db->commit();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Venta eliminada correctamente']);
        } catch (Exception $e) {
            self::$db->rollBack();
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
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            return;
        }

        $estados_validos = ['PENDIENTE', 'PROCESANDO', 'COMPLETADA', 'CANCELADA', 'FACTURADA'];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Estado no válido']);
            return;
        }

        try {
            $venta = Venta::find($venta_id);
            if (!$venta) {
                echo json_encode(['codigo' => 0, 'mensaje' => 'Venta no encontrada']);
                return;
            }

            $venta->sincronizar(['venta_estado' => $nuevo_estado]);
            $resultado = $venta->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Estado actualizado correctamente']);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el estado']);
            }

        } catch (Exception $e) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al actualizar estado', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Obtener clientes para el select
    public static function obtenerClientesAPI()
    {
        try {
            $query = "SELECT 
                        cliente_id,
                        TRIM(cliente_nom1 || ' ' || cliente_nom2 || ' ' || cliente_ape1 || ' ' || cliente_ape2) as nombre_completo,
                        cliente_tel,
                        cliente_correo
                      FROM cliente 
                      WHERE cliente_situacion = 1
                      ORDER BY cliente_nom1 ASC, cliente_ape1 ASC";
                      
            $clientes = self::fetchArray($query);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $clientes]);
        } catch (Exception $e) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener clientes', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Obtener empleados para el select
    public static function obtenerEmpleadosAPI()
    {
        try {
            $query = "SELECT 
                        empleado_id,
                        TRIM(empleado_nom1 || ' ' || empleado_nom2 || ' ' || empleado_ape1 || ' ' || empleado_ape2) as nombre_completo,
                        empleado_especialidad
                      FROM empleado 
                      WHERE empleado_situacion = 1
                      ORDER BY empleado_nom1 ASC, empleado_ape1 ASC";
                      
            $empleados = self::fetchArray($query);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $empleados]);
        } catch (Exception $e) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener empleados', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Obtener tipos de servicio para el select
    public static function obtenerTiposServicioAPI()
    {
        try {
            $query = "SELECT 
                        tipo_servicio_id,
                        tipo_servicio_nombre,
                        tipo_servicio_descripcion,
                        tipo_servicio_precio_base,
                        tipo_servicio_tiempo_estimado
                      FROM tipo_servicio 
                      WHERE tipo_servicio_situacion = 1
                      ORDER BY tipo_servicio_nombre ASC";
                      
            $tipos = self::fetchArray($query);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $tipos]);
        } catch (Exception $e) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener tipos de servicio', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Obtener estadísticas de ventas
    public static function obtenerEstadisticasAPI()
    {
        try {
            $query = "SELECT 
                        COUNT(*) as total_ventas,
                        SUM(venta_total) as total_ingresos,
                        AVG(venta_total) as promedio_venta,
                        MAX(venta_total) as venta_mayor,
                        MIN(venta_total) as venta_menor
                      FROM venta 
                      WHERE venta_situacion = 1 
                      AND venta_fecha >= CURRENT - 30 UNITS DAY";
                      
            $estadisticas = self::fetchArray($query);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $estadisticas[0] ?? []]);
        } catch (Exception $e) {
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener estadísticas', 'detalle' => $e->getMessage()]);
        }
    }
}