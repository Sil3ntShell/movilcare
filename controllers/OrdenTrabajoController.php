<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\OrdenTrabajo;

class OrdenTrabajoController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        isAuth();s
        $router->render('ordentrabajo/index', []);
    }

    // API: Guardar Orden de Trabajo
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'recepcion_id', 'tipo_servicio_id', 'orden_diagnostico'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar que la recepción existe y está activa
        $recepcion = self::fetchArray("SELECT recepcion_id FROM recepcion WHERE recepcion_id = " . intval($_POST['recepcion_id']) . " AND recepcion_situacion = 1");
        if (empty($recepcion)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Recepción no válida']);
            return;
        }

        // Validar que el tipo de servicio existe
        $tipoServicio = self::fetchArray("SELECT tipo_servicio_id FROM tipo_servicio WHERE tipo_servicio_id = " . intval($_POST['tipo_servicio_id']) . " AND tipo_servicio_situacion = 1");
        if (empty($tipoServicio)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Tipo de servicio no válido']);
            return;
        }

        // Validar empleado si se proporciona
        $empleadoId = null;
        if (!empty($_POST['empleado_id'])) {
            $empleado = self::fetchArray("SELECT empleado_id FROM empleado WHERE empleado_id = " . intval($_POST['empleado_id']) . " AND empleado_situacion = 1");
            if (empty($empleado)) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Empleado no válido']);
                return;
            }
            $empleadoId = intval($_POST['empleado_id']);
        }

        // Validar costos
        $costoRepuestos = 0;
        if (!empty($_POST['orden_costo_repuestos'])) {
            if (!is_numeric($_POST['orden_costo_repuestos']) || $_POST['orden_costo_repuestos'] < 0) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El costo de repuestos debe ser un número válido']);
                return;
            }
            $costoRepuestos = floatval($_POST['orden_costo_repuestos']);
        }

        $costoManoObra = 0;
        if (!empty($_POST['orden_costo_mano_obra'])) {
            if (!is_numeric($_POST['orden_costo_mano_obra']) || $_POST['orden_costo_mano_obra'] < 0) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El costo de mano de obra debe ser un número válido']);
                return;
            }
            $costoManoObra = floatval($_POST['orden_costo_mano_obra']);
        }

        // Crear orden de trabajo
        try {
            // Procesar fechas
            $fechaInicio = null;
            if (!empty($_POST['orden_fecha_inicio'])) {
                $fechaInicio = $_POST['orden_fecha_inicio'];
                // Si viene en formato datetime-local, convertir
                if (strpos($fechaInicio, 'T') !== false) {
                    $fechaInicio = str_replace('T', ' ', $fechaInicio);
                    if (strlen($fechaInicio) == 16) { // YYYY-MM-DD HH:MM
                        $fechaInicio .= ':00'; // Agregar segundos
                    }
                }
            }

            $fechaFinalizacion = null;
            if (!empty($_POST['orden_fecha_finalizacion'])) {
                $fechaFinalizacion = $_POST['orden_fecha_finalizacion'];
                // Si viene en formato datetime-local, convertir
                if (strpos($fechaFinalizacion, 'T') !== false) {
                    $fechaFinalizacion = str_replace('T', ' ', $fechaFinalizacion);
                    if (strlen($fechaFinalizacion) == 16) { // YYYY-MM-DD HH:MM
                        $fechaFinalizacion .= ':00'; // Agregar segundos
                    }
                }
            }

            $ordenTrabajo = new OrdenTrabajo([
                'recepcion_id' => intval($_POST['recepcion_id']),
                'empleado_id' => $empleadoId,
                'tipo_servicio_id' => intval($_POST['tipo_servicio_id']),
                'orden_fecha_inicio' => $fechaInicio,
                'orden_fecha_finalizacion' => $fechaFinalizacion,
                'orden_diagnostico' => trim($_POST['orden_diagnostico']),
                'orden_trabajo_realizado' => trim($_POST['orden_trabajo_realizado'] ?? ''),
                'orden_repuestos_utilizados' => trim($_POST['orden_repuestos_utilizados'] ?? ''),
                'orden_costo_repuestos' => $costoRepuestos,
                'orden_costo_mano_obra' => $costoManoObra,
                'orden_costo_total' => $costoRepuestos + $costoManoObra,
                'orden_estado' => trim($_POST['orden_estado'] ?? 'ASIGNADA'),
                'orden_observaciones' => trim($_POST['orden_observaciones'] ?? ''),
                'orden_situacion' => 1
            ]);

            $resultado = $ordenTrabajo->crear();

            if ($resultado && $resultado['resultado']) {
                // Generar número de orden
                $año = date('Y');
                $mes = date('m');
                $numeroOrden = sprintf("OT-%s%s-%04d", $año, $mes, $resultado['id']);
                
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Orden de trabajo registrada correctamente',
                    'numero_orden' => $numeroOrden,
                    'orden_id' => $resultado['id']
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar orden de trabajo']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Órdenes de Trabajo
    public static function buscarAPI()
    {
        try {
            $query = "SELECT 
                        ot.orden_id,
                        ot.recepcion_id,
                        ot.empleado_id,
                        ot.tipo_servicio_id,
                        ot.orden_fecha_asignacion,
                        ot.orden_fecha_inicio,
                        ot.orden_fecha_finalizacion,
                        ot.orden_diagnostico,
                        ot.orden_trabajo_realizado,
                        ot.orden_repuestos_utilizados,
                        ot.orden_costo_repuestos,
                        ot.orden_costo_mano_obra,
                        ot.orden_costo_total,
                        ot.orden_estado,
                        ot.orden_observaciones,
                        ot.orden_situacion,
                        r.recepcion_tipo_celular,
                        r.recepcion_marca,
                        r.recepcion_modelo,
                        (c.cliente_nom1 || ' ' || c.cliente_ape1) as cliente_nombre,
                        c.cliente_tel as cliente_telefono,
                        NVL((e.empleado_nom1 || ' ' || e.empleado_ape1), 'Sin asignar') as empleado_nombre,
                        ts.tipo_servicio_nombre,
                        ts.tipo_servicio_descripcion,
                        ts.tipo_servicio_precio_base
                      FROM orden_trabajo ot
                      LEFT JOIN recepcion r ON ot.recepcion_id = r.recepcion_id
                      LEFT JOIN cliente c ON r.cliente_id = c.cliente_id
                      LEFT JOIN empleado e ON ot.empleado_id = e.empleado_id
                      LEFT JOIN tipo_servicio ts ON ot.tipo_servicio_id = ts.tipo_servicio_id
                      WHERE ot.orden_situacion = 1
                      ORDER BY ot.orden_fecha_asignacion DESC, ot.orden_id DESC";
                      
            $ordenes = self::fetchArray($query);
            
            // DEBUG: Log para ver qué está devolviendo
            error_log("Consulta SQL Órdenes: " . $query);
            error_log("Órdenes encontradas: " . count($ordenes));
            
            // Procesar datos adicionales
            foreach ($ordenes as &$orden) {
                // Asegurar que orden_estado tenga un valor por defecto
                if (empty($orden['orden_estado'])) {
                    $orden['orden_estado'] = 'ASIGNADA';
                }
                
                // Formatear fechas
                if ($orden['orden_fecha_asignacion']) {
                    $orden['fecha_asignacion_formateada'] = date('d/m/Y H:i', strtotime($orden['orden_fecha_asignacion']));
                }
                if ($orden['orden_fecha_inicio']) {
                    $orden['fecha_inicio_formateada'] = date('d/m/Y H:i', strtotime($orden['orden_fecha_inicio']));
                }
                if ($orden['orden_fecha_finalizacion']) {
                    $orden['fecha_finalizacion_formateada'] = date('d/m/Y H:i', strtotime($orden['orden_fecha_finalizacion']));
                }
                
                // Formatear costos
                $orden['costo_repuestos_formateado'] = 'Q. ' . number_format($orden['orden_costo_repuestos'], 2);
                $orden['costo_mano_obra_formateado'] = 'Q. ' . number_format($orden['orden_costo_mano_obra'], 2);
                $orden['costo_total_formateado'] = 'Q. ' . number_format($orden['orden_costo_total'], 2);
                
                // Generar número de orden
                $año = date('Y', strtotime($orden['orden_fecha_asignacion']));
                $mes = date('m', strtotime($orden['orden_fecha_asignacion']));
                $orden['numero_orden'] = sprintf("OT-%s%s-%04d", $año, $mes, $orden['orden_id']);
                
                // Información del dispositivo
                $orden['dispositivo_info'] = trim($orden['recepcion_marca'] . ' ' . ($orden['recepcion_modelo'] ?? '') . ' (' . $orden['recepcion_tipo_celular'] . ')');
            }
            
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Órdenes de trabajo encontradas',
                'data' => $ordenes
            ]);
            
        } catch (Exception $e) {
            error_log("Error en buscarAPI órdenes: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cargar órdenes de trabajo',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // API: Obtener recepciones disponibles para crear órdenes
    public static function obtenerRecepcionesAPI()
    {
        try {
            $query = "SELECT 
                        r.recepcion_id,
                        r.recepcion_tipo_celular,
                        r.recepcion_marca,
                        r.recepcion_modelo,
                        r.recepcion_motivo_ingreso,
                        (c.cliente_nom1 || ' ' || c.cliente_ape1) as cliente_nombre,
                        c.cliente_tel
                      FROM recepcion r
                      LEFT JOIN cliente c ON r.cliente_id = c.cliente_id
                      WHERE r.recepcion_situacion = 1 
                      AND r.recepcion_estado IN ('RECIBIDO', 'EN_DIAGNOSTICO', 'REPARADO')
                      ORDER BY r.recepcion_fecha DESC";
                      
            $recepciones = self::fetchArray($query);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $recepciones]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener recepciones', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Obtener tipos de servicio
    public static function obtenerTiposServicioAPI()
    {
        try {
            $tiposServicio = self::fetchArray("SELECT * FROM tipo_servicio WHERE tipo_servicio_situacion = 1 ORDER BY tipo_servicio_nombre ASC");
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $tiposServicio]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener tipos de servicio', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Modificar Orden de Trabajo
    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['orden_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        try {
            $orden = OrdenTrabajo::find($id);

            if (!$orden) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Orden de trabajo no encontrada']);
                return;
            }

            // Calcular costo total
            $costoRepuestos = !empty($_POST['orden_costo_repuestos']) ? floatval($_POST['orden_costo_repuestos']) : 0;
            $costoManoObra = !empty($_POST['orden_costo_mano_obra']) ? floatval($_POST['orden_costo_mano_obra']) : 0;
            $costoTotal = $costoRepuestos + $costoManoObra;

            // Procesar fechas
            $fechaInicio = null;
            if (!empty($_POST['orden_fecha_inicio'])) {
                $fechaInicio = $_POST['orden_fecha_inicio'];
                // Si viene en formato datetime-local, convertir
                if (strpos($fechaInicio, 'T') !== false) {
                    $fechaInicio = str_replace('T', ' ', $fechaInicio);
                    if (strlen($fechaInicio) == 16) { // YYYY-MM-DD HH:MM
                        $fechaInicio .= ':00'; // Agregar segundos
                    }
                }
            }

            $fechaFinalizacion = null;
            if (!empty($_POST['orden_fecha_finalizacion'])) {
                $fechaFinalizacion = $_POST['orden_fecha_finalizacion'];
                // Si viene en formato datetime-local, convertir
                if (strpos($fechaFinalizacion, 'T') !== false) {
                    $fechaFinalizacion = str_replace('T', ' ', $fechaFinalizacion);
                    if (strlen($fechaFinalizacion) == 16) { // YYYY-MM-DD HH:MM
                        $fechaFinalizacion .= ':00'; // Agregar segundos
                    }
                }
            }

            // Sincronizar todos los campos necesarios
            $orden->sincronizar([
                'empleado_id' => !empty($_POST['empleado_id']) ? intval($_POST['empleado_id']) : null,
                'tipo_servicio_id' => intval($_POST['tipo_servicio_id']),
                'orden_fecha_inicio' => $fechaInicio,
                'orden_fecha_finalizacion' => $fechaFinalizacion,
                'orden_diagnostico' => trim($_POST['orden_diagnostico']),
                'orden_trabajo_realizado' => trim($_POST['orden_trabajo_realizado'] ?? ''),
                'orden_repuestos_utilizados' => trim($_POST['orden_repuestos_utilizados'] ?? ''),
                'orden_costo_repuestos' => $costoRepuestos,
                'orden_costo_mano_obra' => $costoManoObra,
                'orden_costo_total' => $costoTotal,
                'orden_estado' => trim($_POST['orden_estado'] ?? 'ASIGNADA'),
                'orden_observaciones' => trim($_POST['orden_observaciones'] ?? ''),
                'orden_situacion' => 1
            ]);

            $resultado = $orden->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Orden de trabajo actualizada correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar la orden de trabajo']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Eliminar Orden de Trabajo (lógico)
    public static function eliminarAPI()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['orden_id'] ?? $_POST['orden_id'] ?? $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $orden = OrdenTrabajo::find($id);
            if (!$orden) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Orden de trabajo no encontrada']);
                return;
            }

            $orden->sincronizar(['orden_situacion' => 0]);
            $orden->actualizar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Orden de trabajo eliminada correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Cambiar estado de orden de trabajo
    public static function cambiarEstadoAPI()
    {
        getHeadersApi();
        
        $orden_id = $_POST['orden_id'] ?? null;
        $nuevo_estado = $_POST['nuevo_estado'] ?? null;
        
        if (!$orden_id || !$nuevo_estado) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            return;
        }

        $estados_validos = ['ASIGNADA', 'EN_PROGRESO', 'EN_ESPERA_REPUESTOS', 'PAUSADA', 'COMPLETADA', 'CANCELADA', 'ENTREGADA'];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Estado no válido']);
            return;
        }

        try {
            $orden = OrdenTrabajo::find($orden_id);
            if (!$orden) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Orden de trabajo no encontrada']);
                return;
            }

            $orden->sincronizar(['orden_estado' => $nuevo_estado]);
            $resultado = $orden->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Estado actualizado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el estado']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al actualizar estado', 'detalle' => $e->getMessage()]);
        }
    }
}