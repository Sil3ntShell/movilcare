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

    /**
     * API para buscar tipos de servicio
     */
    public static function buscarAPI()
    {
        ob_clean();
        
        try {
            $query = "SELECT 
                        tipo_servicio_id,
                        tipo_servicio_nombre,
                        tipo_servicio_descripcion,
                        tipo_servicio_precio_base,
                        tipo_servicio_tiempo_estimado,
                        tipo_servicio_situacion
                      FROM tipo_servicio 
                      WHERE tipo_servicio_situacion = 1
                      ORDER BY tipo_servicio_nombre ASC";
                      
            $tiposServicio = self::fetchArray($query);
            
            // Procesar datos adicionales
            foreach ($tiposServicio as &$tipo) {
                // Formatear precio
                $tipo['precio_formateado'] = 'Q. ' . number_format($tipo['tipo_servicio_precio_base'], 2);
                
                // Formatear tiempo
                $minutos = $tipo['tipo_servicio_tiempo_estimado'];
                if ($minutos < 60) {
                    $tipo['tiempo_formateado'] = $minutos . ' min';
                } else if ($minutos < 1440) {
                    $horas = floor($minutos / 60);
                    $mins = $minutos % 60;
                    $tipo['tiempo_formateado'] = $horas . 'h' . ($mins > 0 ? ' ' . $mins . 'm' : '');
                } else {
                    $dias = floor($minutos / 1440);
                    $horasRestantes = floor(($minutos % 1440) / 60);
                    $tipo['tiempo_formateado'] = $dias . ' días' . ($horasRestantes > 0 ? ' ' . $horasRestantes . 'h' : '');
                }
            }
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tipos de servicio encontrados',
                'data' => $tiposServicio
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cargar tipos de servicio',
                'detalle' => $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * API para guardar tipo de servicio
     */
    public static function guardarAPI()
    {
        ob_clean();
        
        try {
            // Validaciones básicas
            if (empty($_POST['tipo_servicio_nombre'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre del servicio es obligatorio']);
                exit();
            }

            if (empty($_POST['tipo_servicio_precio_base']) || !is_numeric($_POST['tipo_servicio_precio_base']) || $_POST['tipo_servicio_precio_base'] <= 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El precio base debe ser un número mayor a 0']);
                exit();
            }

            if (empty($_POST['tipo_servicio_tiempo_estimado']) || !is_numeric($_POST['tipo_servicio_tiempo_estimado']) || $_POST['tipo_servicio_tiempo_estimado'] <= 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El tiempo estimado debe ser un número mayor a 0']);
                exit();
            }

            // Verificar duplicados
            $nombreExiste = TipoServicio::verificarNombreExistente($_POST['tipo_servicio_nombre']);
            if ($nombreExiste) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe un servicio con este nombre']);
                exit();
            }

            // Crear tipo de servicio
            $tipoServicio = new TipoServicio([
                'tipo_servicio_nombre' => trim($_POST['tipo_servicio_nombre']),
                'tipo_servicio_descripcion' => trim($_POST['tipo_servicio_descripcion'] ?? ''),
                'tipo_servicio_precio_base' => floatval($_POST['tipo_servicio_precio_base']),
                'tipo_servicio_tiempo_estimado' => intval($_POST['tipo_servicio_tiempo_estimado']),
                'tipo_servicio_situacion' => 1
            ]);

            $resultado = $tipoServicio->crear();

            if ($resultado && $resultado['resultado']) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Tipo de servicio registrado correctamente'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al guardar tipo de servicio'
                ]);
            }

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * API para actualizar tipo de servicio
     */
    public static function actualizarAPI()
    {
        ob_clean();
        
        try {
            $tipo_servicio_id = $_POST['tipo_servicio_id'] ?? null;
            
            if (!$tipo_servicio_id) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido']);
                exit();
            }

            $tipoServicio = TipoServicio::obtenerActivoPorId($tipo_servicio_id);
            if (!$tipoServicio) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'Tipo de servicio no encontrado']);
                exit();
            }

            // Validaciones (las mismas que en guardar)
            if (empty($_POST['tipo_servicio_nombre'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre del servicio es obligatorio']);
                exit();
            }

            // Verificar duplicados excluyendo el tipo de servicio actual
            $nombreExiste = TipoServicio::verificarNombreExistente($_POST['tipo_servicio_nombre'], $tipo_servicio_id);
            if ($nombreExiste) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe otro servicio con este nombre']);
                exit();
            }

            // Actualizar campos
            $tipoServicio->tipo_servicio_nombre = trim($_POST['tipo_servicio_nombre']);
            $tipoServicio->tipo_servicio_descripcion = trim($_POST['tipo_servicio_descripcion'] ?? '');
            $tipoServicio->tipo_servicio_precio_base = floatval($_POST['tipo_servicio_precio_base']);
            $tipoServicio->tipo_servicio_tiempo_estimado = intval($_POST['tipo_servicio_tiempo_estimado']);

            $resultado = $tipoServicio->guardar();

            if ($resultado && $resultado['resultado']) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Tipo de servicio actualizado correctamente'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al actualizar tipo de servicio'
                ]);
            }

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * API para eliminar tipo de servicio
     */
    public static function eliminarAPI()
    {
        ob_clean();
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $tipo_servicio_id = $input['tipo_servicio_id'] ?? $_POST['tipo_servicio_id'] ?? null;
            
            if (!$tipo_servicio_id) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido']);
                exit();
            }

            // Verificar si el tipo de servicio está siendo usado en órdenes de trabajo
            $checkUso = "SELECT COUNT(*) as count FROM orden_trabajo WHERE tipo_servicio_id = " . intval($tipo_servicio_id) . " AND orden_situacion = 1";
            $resultUso = self::fetchArray($checkUso);
            
            if ($resultUso[0]['count'] > 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se puede eliminar: el tipo de servicio está siendo usado en órdenes de trabajo']);
                exit();
            }

            // Usar query directa para actualizar solo el estado
            $query = "UPDATE tipo_servicio SET tipo_servicio_situacion = 0 WHERE tipo_servicio_id = " . intval($tipo_servicio_id);
            $resultado = self::$db->exec($query);

            if ($resultado !== false) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Tipo de servicio eliminado correctamente'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al eliminar tipo de servicio'
                ]);
            }

        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}