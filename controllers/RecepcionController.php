<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Recepcion;

class RecepcionController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('recepcion/index', []);
    }

    // API: Guardar Recepción
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'cliente_id', 'empleado_id', 'recepcion_tipo_celular', 'recepcion_marca', 
            'recepcion_motivo_ingreso'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar que cliente y empleado existen
        $cliente = self::fetchArray("SELECT cliente_id FROM cliente WHERE cliente_id = " . intval($_POST['cliente_id']) . " AND cliente_situacion = 1");
        if (empty($cliente)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Cliente no válido']);
            return;
        }

        $empleado = self::fetchArray("SELECT empleado_id FROM empleado WHERE empleado_id = " . intval($_POST['empleado_id']) . " AND empleado_situacion = 1");
        if (empty($empleado)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Empleado no válido']);
            return;
        }

        // Validar costo estimado si se proporciona
        $costoEstimado = 0;
        if (!empty($_POST['recepcion_costo_estimado'])) {
            if (!is_numeric($_POST['recepcion_costo_estimado']) || $_POST['recepcion_costo_estimado'] < 0) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El costo estimado debe ser un número válido']);
                return;
            }
            $costoEstimado = floatval($_POST['recepcion_costo_estimado']);
        }

        // Validar tiempo estimado
        $tiempoEstimado = 1;
        if (!empty($_POST['recepcion_tiempo_estimado'])) {
            if (!is_numeric($_POST['recepcion_tiempo_estimado']) || $_POST['recepcion_tiempo_estimado'] <= 0) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El tiempo estimado debe ser mayor a 0']);
                return;
            }
            $tiempoEstimado = intval($_POST['recepcion_tiempo_estimado']);
        }

        // Crear recepción
        try {
            $recepcion = new Recepcion([
                'cliente_id' => intval($_POST['cliente_id']),
                'empleado_id' => intval($_POST['empleado_id']),
                'recepcion_tipo_celular' => trim($_POST['recepcion_tipo_celular']),
                'recepcion_marca' => trim($_POST['recepcion_marca']),
                'recepcion_modelo' => trim($_POST['recepcion_modelo'] ?? ''),
                'recepcion_imei' => trim($_POST['recepcion_imei'] ?? ''),
                'recepcion_numero_serie' => trim($_POST['recepcion_numero_serie'] ?? ''),
                'recepcion_motivo_ingreso' => trim($_POST['recepcion_motivo_ingreso']),
                'recepcion_estado_dispositivo' => trim($_POST['recepcion_estado_dispositivo'] ?? ''),
                'recepcion_accesorios' => trim($_POST['recepcion_accesorios'] ?? ''),
                'recepcion_observaciones_cliente' => trim($_POST['recepcion_observaciones_cliente'] ?? ''),
                'recepcion_costo_estimado' => $costoEstimado,
                'recepcion_tiempo_estimado' => $tiempoEstimado,
                'recepcion_estado' => 'RECIBIDO',
                'recepcion_situacion' => 1
            ]);

            $resultado = $recepcion->crear();

            if ($resultado && $resultado['resultado']) {
                // Generar número de recepción
                $año = date('Y');
                $mes = date('m');
                $numeroRecepcion = sprintf("REC-%s%s-%04d", $año, $mes, $resultado['id']);
                
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Recepción registrada correctamente',
                    'numero_recepcion' => $numeroRecepcion,
                    'recepcion_id' => $resultado['id']
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar recepción']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Recepciones
    public static function buscarAPI()
    {
        try {
            $query = "SELECT 
                        r.recepcion_id,
                        r.cliente_id,
                        r.empleado_id,
                        r.recepcion_fecha,
                        r.recepcion_tipo_celular,
                        r.recepcion_marca,
                        r.recepcion_modelo,
                        r.recepcion_imei,
                        r.recepcion_numero_serie,
                        r.recepcion_motivo_ingreso,
                        r.recepcion_estado_dispositivo,
                        r.recepcion_accesorios,
                        r.recepcion_observaciones_cliente,
                        r.recepcion_costo_estimado,
                        r.recepcion_tiempo_estimado,
                        r.recepcion_estado,
                        r.recepcion_situacion,
                        TRIM(c.cliente_nom1 || ' ' || NVL(c.cliente_nom2, '') || ' ' || c.cliente_ape1 || ' ' || NVL(c.cliente_ape2, '')) as cliente_nombre,
                        c.cliente_tel as cliente_telefono,
                        TRIM(e.empleado_nom1 || ' ' || NVL(e.empleado_nom2, '') || ' ' || e.empleado_ape1 || ' ' || NVL(e.empleado_ape2, '')) as empleado_nombre
                      FROM recepcion r
                      LEFT JOIN cliente c ON r.cliente_id = c.cliente_id
                      LEFT JOIN empleado e ON r.empleado_id = e.empleado_id
                      WHERE r.recepcion_situacion = 1
                      ORDER BY r.recepcion_fecha DESC, r.recepcion_id DESC";
                      
            $recepciones = self::fetchArray($query);
            
             // DEBUG: Log para ver qué está devolviendo
            // error_log("Consulta SQL: " . $query);
            // error_log("Recepciones encontradas: " . count($recepciones));
            // error_log("Datos: " . json_encode($recepciones));
            
            // Procesar datos adicionales
            foreach ($recepciones as &$recepcion) {
                // Asegurar que recepcion_estado tenga un valor por defecto
                if (empty($recepcion['recepcion_estado'])) {
                    $recepcion['recepcion_estado'] = 'RECIBIDO';
                }
                
                // Formatear fecha
                if ($recepcion['recepcion_fecha']) {
                    $recepcion['fecha_formateada'] = date('d/m/Y H:i', strtotime($recepcion['recepcion_fecha']));
                }
                
                // Formatear costo
                if ($recepcion['recepcion_costo_estimado']) {
                    $recepcion['costo_formateado'] = 'Q. ' . number_format($recepcion['recepcion_costo_estimado'], 2);
                } else {
                    $recepcion['costo_formateado'] = 'Por definir';
                }
                
                // Generar número de recepción
                $año = date('Y', strtotime($recepcion['recepcion_fecha']));
                $mes = date('m', strtotime($recepcion['recepcion_fecha']));
                $recepcion['numero_recepcion'] = sprintf("REC-%s%s-%04d", $año, $mes, $recepcion['recepcion_id']);
            }
            
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Recepciones encontradas',
                'data' => $recepciones
            ]);
            
        } catch (Exception $e) {
            error_log("Error en buscarAPI: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cargar recepciones',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // API: Modificar Recepción
    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['recepcion_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validar campos requeridos
        $campos = [
            'cliente_id', 'empleado_id', 'recepcion_tipo_celular', 'recepcion_marca', 
            'recepcion_motivo_ingreso'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        try {
            $recepcion = Recepcion::find($id);

            if (!$recepcion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Recepción no encontrada']);
                return;
            }

            // Sincronizar todos los campos necesarios
            $recepcion->sincronizar([
                'cliente_id' => intval($_POST['cliente_id']),
                'empleado_id' => intval($_POST['empleado_id']),
                'recepcion_tipo_celular' => trim($_POST['recepcion_tipo_celular']),
                'recepcion_marca' => trim($_POST['recepcion_marca']),
                'recepcion_modelo' => trim($_POST['recepcion_modelo'] ?? ''),
                'recepcion_imei' => trim($_POST['recepcion_imei'] ?? ''),
                'recepcion_numero_serie' => trim($_POST['recepcion_numero_serie'] ?? ''),
                'recepcion_motivo_ingreso' => trim($_POST['recepcion_motivo_ingreso']),
                'recepcion_estado_dispositivo' => trim($_POST['recepcion_estado_dispositivo'] ?? ''),
                'recepcion_accesorios' => trim($_POST['recepcion_accesorios'] ?? ''),
                'recepcion_observaciones_cliente' => trim($_POST['recepcion_observaciones_cliente'] ?? ''),
                'recepcion_costo_estimado' => !empty($_POST['recepcion_costo_estimado']) ? floatval($_POST['recepcion_costo_estimado']) : 0,
                'recepcion_tiempo_estimado' => !empty($_POST['recepcion_tiempo_estimado']) ? intval($_POST['recepcion_tiempo_estimado']) : 1,
                'recepcion_estado' => trim($_POST['recepcion_estado'] ?? 'RECIBIDO'),
                'recepcion_situacion' => 1
            ]);

            $resultado = $recepcion->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Recepción actualizada correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar la recepción']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Eliminar Recepción (lógico)
    public static function eliminarAPI()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['recepcion_id'] ?? $_POST['recepcion_id'] ?? $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $recepcion = Recepcion::find($id);
            if (!$recepcion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Recepción no encontrada']);
                return;
            }

            $recepcion->sincronizar(['recepcion_situacion' => 0]);
            $recepcion->actualizar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Recepción eliminada correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Cambiar estado de recepción
    public static function cambiarEstadoAPI()
    {
        getHeadersApi();
        
        $recepcion_id = $_POST['recepcion_id'] ?? null;
        $nuevo_estado = $_POST['nuevo_estado'] ?? null;
        
        if (!$recepcion_id || !$nuevo_estado) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Datos incompletos']);
            return;
        }

        $estados_validos = ['RECIBIDO', 'EN_DIAGNOSTICO', 'ESPERANDO_REPUESTOS', 'EN_REPARACION', 'REPARADO', 'ENTREGADO', 'CANCELADO'];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Estado no válido']);
            return;
        }

        try {
            $recepcion = Recepcion::find($recepcion_id);
            if (!$recepcion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Recepción no encontrada']);
                return;
            }

            $recepcion->sincronizar(['recepcion_estado' => $nuevo_estado]);
            $resultado = $recepcion->actualizar();

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