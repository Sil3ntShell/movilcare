<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Empleado;

class EmpleadoController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('empleado/index', []);
    }

    /**
     * API para buscar empleados
     */
    public static function buscarAPI()
    {
        ob_clean();
        
        try {
            $query = "SELECT 
                        e.empleado_id,
                        e.usuario_id,
                        e.empleado_nom1,
                        e.empleado_nom2,
                        e.empleado_ape1,
                        e.empleado_ape2,
                        e.empleado_dpi,
                        e.empleado_tel,
                        e.empleado_correo,
                        e.empleado_especialidad,
                        e.empleado_fecha_contratacion,
                        e.empleado_salario,
                        e.empleado_situacion,
                        u.usuario_fotografia,
                        u.rol_id,
                        r.rol_nombre
                      FROM empleado e
                      LEFT JOIN usuario u ON e.usuario_id = u.usuario_id
                      LEFT JOIN rol r ON u.rol_id = r.rol_id
                      WHERE e.empleado_situacion = 1
                      ORDER BY e.empleado_fecha_contratacion DESC, e.empleado_id DESC";
                      
            $empleados = self::fetchArray($query);
            
            // Procesar datos adicionales
            foreach ($empleados as &$empleado) {
                if ($empleado['empleado_fecha_contratacion']) {
                    $empleado['fecha_formateada'] = date('d/m/Y', strtotime($empleado['empleado_fecha_contratacion']));
                }
                
                // Formatear salario
                $empleado['salario_formateado'] = 'Q. ' . number_format($empleado['empleado_salario'], 2);
            }
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Empleados encontrados',
                'data' => $empleados
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cargar empleados',
                'detalle' => $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * API para guardar empleado
     */
    public static function guardarAPI()
    {
        ob_clean();
        
        try {
            // Validaciones básicas
            if (empty($_POST['empleado_nom1'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El primer nombre es obligatorio']);
                exit();
            }

            if (empty($_POST['empleado_ape1'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El primer apellido es obligatorio']);
                exit();
            }

            if (empty($_POST['empleado_tel']) || strlen($_POST['empleado_tel']) != 8) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
                exit();
            }

            if (empty($_POST['empleado_dpi']) || strlen($_POST['empleado_dpi']) != 13) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI debe tener 13 dígitos']);
                exit();
            }

            if (empty($_POST['empleado_correo']) || !filter_var($_POST['empleado_correo'], FILTER_VALIDATE_EMAIL)) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
                exit();
            }

            if (empty($_POST['empleado_especialidad'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'La especialidad es obligatoria']);
                exit();
            }

            if (empty($_POST['empleado_salario']) || !is_numeric($_POST['empleado_salario']) || $_POST['empleado_salario'] <= 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El salario debe ser un número mayor a 0']);
                exit();
            }

            // Verificar duplicados
            $checkCorreo = "SELECT COUNT(*) as count FROM empleado WHERE empleado_correo = '" . addslashes($_POST['empleado_correo']) . "' AND empleado_situacion = 1";
            $resultCorreo = self::fetchArray($checkCorreo);
            if ($resultCorreo[0]['count'] > 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El correo ya está registrado']);
                exit();
            }

            $checkDPI = "SELECT COUNT(*) as count FROM empleado WHERE empleado_dpi = '" . addslashes($_POST['empleado_dpi']) . "' AND empleado_situacion = 1";
            $resultDPI = self::fetchArray($checkDPI);
            if ($resultDPI[0]['count'] > 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI ya está registrado']);
                exit();
            }

            // Crear empleado
            $empleado = new Empleado([
                'usuario_id' => !empty($_POST['usuario_id']) ? intval($_POST['usuario_id']) : null,
                'empleado_nom1' => ucwords(strtolower(trim($_POST['empleado_nom1']))),
                'empleado_nom2' => ucwords(strtolower(trim($_POST['empleado_nom2'] ?? ''))),
                'empleado_ape1' => ucwords(strtolower(trim($_POST['empleado_ape1']))),
                'empleado_ape2' => ucwords(strtolower(trim($_POST['empleado_ape2'] ?? ''))),
                'empleado_dpi' => trim($_POST['empleado_dpi']),
                'empleado_tel' => intval($_POST['empleado_tel']),
                'empleado_correo' => strtolower(trim($_POST['empleado_correo'])),
                'empleado_especialidad' => trim($_POST['empleado_especialidad']),
                'empleado_salario' => floatval($_POST['empleado_salario']),
                'empleado_situacion' => 1
            ]);

            $resultado = $empleado->crear();

            if ($resultado && $resultado['resultado']) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Empleado registrado correctamente'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al guardar empleado'
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
     * API para actualizar empleado
     */
    public static function actualizarAPI()
    {
        ob_clean();
        
        try {
            $empleado_id = $_POST['empleado_id'] ?? null;
            
            if (!$empleado_id) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido']);
                exit();
            }

            $empleado = Empleado::obtenerEmpleadoActivo($empleado_id);
            if (!$empleado) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'Empleado no encontrado']);
                exit();
            }

            // Validaciones (las mismas que en guardar)
            if (empty($_POST['empleado_nom1'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El primer nombre es obligatorio']);
                exit();
            }

            // Verificar duplicados excluyendo el empleado actual
            $checkCorreo = "SELECT COUNT(*) as count FROM empleado WHERE empleado_correo = '" . addslashes($_POST['empleado_correo']) . "' AND empleado_id != $empleado_id AND empleado_situacion = 1";
            $resultCorreo = self::fetchArray($checkCorreo);
            if ($resultCorreo[0]['count'] > 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El correo ya está en uso']);
                exit();
            }

            // Actualizar campos
            $empleado->usuario_id = !empty($_POST['usuario_id']) ? intval($_POST['usuario_id']) : null;
            $empleado->empleado_nom1 = ucwords(strtolower(trim($_POST['empleado_nom1'])));
            $empleado->empleado_nom2 = ucwords(strtolower(trim($_POST['empleado_nom2'] ?? '')));
            $empleado->empleado_ape1 = ucwords(strtolower(trim($_POST['empleado_ape1'])));
            $empleado->empleado_ape2 = ucwords(strtolower(trim($_POST['empleado_ape2'] ?? '')));
            $empleado->empleado_dpi = trim($_POST['empleado_dpi']);
            $empleado->empleado_tel = intval($_POST['empleado_tel']);
            $empleado->empleado_correo = strtolower(trim($_POST['empleado_correo']));
            $empleado->empleado_especialidad = trim($_POST['empleado_especialidad']);
            $empleado->empleado_salario = floatval($_POST['empleado_salario']);

            $resultado = $empleado->guardar();

            if ($resultado && $resultado['resultado']) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Empleado actualizado correctamente'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al actualizar empleado'
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
     * API para eliminar empleado
     */
    public static function eliminarAPI()
    {
        ob_clean();
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $empleado_id = $input['empleado_id'] ?? $_POST['empleado_id'] ?? null;
            
            if (!$empleado_id) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido']);
                exit();
            }

            // Usar query directa para actualizar solo el estado
            $query = "UPDATE empleado SET empleado_situacion = 0 WHERE empleado_id = " . intval($empleado_id);
            $resultado = self::$db->exec($query);

            if ($resultado !== false) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Empleado eliminado correctamente'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al eliminar empleado'
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