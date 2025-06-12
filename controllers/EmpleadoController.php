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
     * API para buscar empleados - MEJORADO
     */
    public static function buscarAPI()
    {
        try {
            // Usar el método del modelo que incluye toda la información necesaria
            $empleados = Empleado::obtenerEmpleadosConUsuario();

            // Procesar datos adicionales
            foreach ($empleados as &$empleado) {
                // Formatear fecha de contratación
                if (!empty($empleado['empleado_fecha_contratacion'])) {
                    $empleado['fecha_formateada'] = date('d/m/Y', strtotime($empleado['empleado_fecha_contratacion']));
                } else {
                    $empleado['fecha_formateada'] = 'N/A';
                }
                
                // Formatear salario
                $empleado['salario_formateado'] = 'Q. ' . number_format($empleado['empleado_salario'], 2);
                
                // Verificar si la foto del usuario existe físicamente
                if (!empty($empleado['usuario_fotografia'])) {
                    $rutaFoto = $_SERVER['DOCUMENT_ROOT'] . '/empresa_celulares/storage/fotos_usuarios/' . $empleado['usuario_fotografia'];
                    if (!file_exists($rutaFoto)) {
                        $empleado['usuario_fotografia'] = ''; // Limpiar si el archivo no existe
                    }
                }
                
                // Asegurar que rol_nombre tenga un valor
                if (empty($empleado['rol_nombre'])) {
                    $empleado['rol_nombre'] = 'Sin rol';
                }
            }
            
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Empleados encontrados correctamente',
                'data' => $empleados
            ]);
            
        } catch (Exception $e) {
            error_log("Error buscando empleados: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cargar empleados',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    /**
     * API para guardar empleado - MEJORADO
     */
    public static function guardarAPI()
    {
        getHeadersApi();
        
        try {
            // Validar campos obligatorios
            $campos = [
                'empleado_nom1', 'empleado_ape1', 'empleado_tel', 'empleado_dpi',
                'empleado_correo', 'empleado_especialidad', 'empleado_salario'
            ];

            foreach ($campos as $campo) {
                if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                    return;
                }
            }

            // Validar longitudes
            if (strlen($_POST['empleado_nom1']) < 2 || strlen($_POST['empleado_ape1']) < 2) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre o apellido demasiado corto']);
                return;
            }

            // Validar teléfono
            $tel = filter_var($_POST['empleado_tel'], FILTER_SANITIZE_NUMBER_INT);
            if (strlen($tel) != 8) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
                return;
            }

            // Validar DPI
            if (strlen($_POST['empleado_dpi']) != 13) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI debe tener 13 dígitos']);
                return;
            }

            // Validar correo
            if (!filter_var($_POST['empleado_correo'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
                return;
            }

            // Validar salario
            if (!is_numeric($_POST['empleado_salario']) || $_POST['empleado_salario'] <= 0) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El salario debe ser un número mayor a 0']);
                return;
            }

            // Verificar duplicidad usando el método del modelo
            try {
                $existe = Empleado::verificarEmpleadoExistente($_POST['empleado_correo'], $_POST['empleado_dpi']);
                
                if (isset($existe['correo_existe']) && $existe['correo_existe'] > 0) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'El correo ya está registrado']);
                    return;
                }
                
                if (isset($existe['dpi_existe']) && $existe['dpi_existe'] > 0) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI ya está registrado']);
                    return;
                }
            } catch (Exception $e) {
                error_log("Error verificando duplicidad: " . $e->getMessage());
                // Continuar sin verificar duplicidad si hay error
            }

            // Crear empleado
            $empleado = new Empleado([
                'usuario_id' => !empty($_POST['usuario_id']) ? intval($_POST['usuario_id']) : null,
                'empleado_nom1' => ucwords(strtolower(trim($_POST['empleado_nom1']))),
                'empleado_nom2' => ucwords(strtolower(trim($_POST['empleado_nom2'] ?? ''))),
                'empleado_ape1' => ucwords(strtolower(trim($_POST['empleado_ape1']))),
                'empleado_ape2' => ucwords(strtolower(trim($_POST['empleado_ape2'] ?? ''))),
                'empleado_dpi' => trim($_POST['empleado_dpi']),
                'empleado_tel' => $_POST['empleado_tel'],
                'empleado_correo' => strtolower(trim($_POST['empleado_correo'])),
                'empleado_especialidad' => trim($_POST['empleado_especialidad']),
                'empleado_salario' => floatval($_POST['empleado_salario']),
                'empleado_situacion' => 1
            ]);

            $resultado = $empleado->crear();

            if ($resultado && $resultado['resultado']) {
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Empleado registrado correctamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al guardar empleado'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error guardando empleado: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al guardar empleado',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    /**
     * API para actualizar empleado - MEJORADO
     */
    public static function modificarAPI()
    {
        getHeadersApi();
        
        try {
            $empleado_id = $_POST['empleado_id'] ?? null;
            
            if (!$empleado_id) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
                return;
            }

            // Validar campos obligatorios (mismo código que en guardar)
            $campos = [
                'empleado_nom1', 'empleado_ape1', 'empleado_tel', 'empleado_dpi',
                'empleado_correo', 'empleado_especialidad', 'empleado_salario'
            ];

            foreach ($campos as $campo) {
                if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                    return;
                }
            }

            // Validaciones (mismo código que en guardar)
            if (strlen($_POST['empleado_nom1']) < 2 || strlen($_POST['empleado_ape1']) < 2) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre o apellido demasiado corto']);
                return;
            }

            $tel = filter_var($_POST['empleado_tel'], FILTER_SANITIZE_NUMBER_INT);
            if (strlen($tel) != 8) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
                return;
            }

            if (strlen($_POST['empleado_dpi']) != 13) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI debe tener 13 dígitos']);
                return;
            }

            if (!filter_var($_POST['empleado_correo'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
                return;
            }

            if (!is_numeric($_POST['empleado_salario']) || $_POST['empleado_salario'] <= 0) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'El salario debe ser un número mayor a 0']);
                return;
            }

            // Encontrar el empleado
            $empleado = Empleado::find($empleado_id);
            if (!$empleado) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Empleado no encontrado']);
                return;
            }

            // Verificar duplicidad excluyendo el empleado actual
            try {
                $existe = Empleado::verificarEmpleadoExistente($_POST['empleado_correo'], $_POST['empleado_dpi'], $empleado_id);
                
                if (isset($existe['correo_existe']) && $existe['correo_existe'] > 0) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'El correo ya está registrado por otro empleado']);
                    return;
                }
                
                if (isset($existe['dpi_existe']) && $existe['dpi_existe'] > 0) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI ya está registrado por otro empleado']);
                    return;
                }
            } catch (Exception $e) {
                error_log("Error verificando duplicidad en modificar: " . $e->getMessage());
                // Continuar sin verificar duplicidad si hay error
            }

            // Sincronizar datos
            $empleado->sincronizar([
                'usuario_id' => !empty($_POST['usuario_id']) ? intval($_POST['usuario_id']) : null,
                'empleado_nom1' => ucwords(strtolower(trim($_POST['empleado_nom1']))),
                'empleado_nom2' => ucwords(strtolower(trim($_POST['empleado_nom2'] ?? ''))),
                'empleado_ape1' => ucwords(strtolower(trim($_POST['empleado_ape1']))),
                'empleado_ape2' => ucwords(strtolower(trim($_POST['empleado_ape2'] ?? ''))),
                'empleado_dpi' => trim($_POST['empleado_dpi']),
                'empleado_tel' => $_POST['empleado_tel'],
                'empleado_correo' => strtolower(trim($_POST['empleado_correo'])),
                'empleado_especialidad' => trim($_POST['empleado_especialidad']),
                'empleado_salario' => floatval($_POST['empleado_salario']),
                'empleado_situacion' => 1
            ]);

            $resultado = $empleado->actualizar();

            if ($resultado && $resultado['resultado']) {
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Empleado actualizado correctamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se pudo actualizar el empleado'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error modificando empleado: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al modificar empleado',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    /**
     * API para eliminar empleado - MEJORADO
     */
    public static function eliminarAPI()
    {
        try {
            $empleado_id = $_GET['id'] ?? null;
            
            if (!$empleado_id) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
                return;
            }

            $empleado = Empleado::find($empleado_id);
            if (!$empleado) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Empleado no encontrado']);
                return;
            }

            $empleado->sincronizar(['empleado_situacion' => 0]);
            $resultado = $empleado->actualizar();

            if ($resultado && $resultado['resultado']) {
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Empleado eliminado correctamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se pudo eliminar el empleado'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error eliminando empleado: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al eliminar empleado',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}