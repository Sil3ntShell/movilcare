<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Aplicacion;

class AplicacionController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('aplicacion/index', []);
    }

    // API: Guardar Aplicación
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'app_nombre_largo', 'app_nombre_medium', 'app_nombre_corto'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar longitudes
        if (strlen($_POST['app_nombre_corto']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre corto debe tener al menos 2 caracteres']);
            return;
        }

        if (strlen($_POST['app_nombre_largo']) < 5) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre largo debe tener al menos 5 caracteres']);
            return;
        }

        // Verificar duplicidad
        try {
            $existe = Aplicacion::verificarAplicacionExistente($_POST['app_nombre_corto']);
            
            if ($existe) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe una aplicación con ese nombre corto']);
                return;
            }
        } catch (Exception $e) {
            error_log("Error verificando duplicidad: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error verificando datos existentes']);
            return;
        }

        // Crear aplicación
        try {
            $aplicacion = new Aplicacion([
                'app_nombre_largo' => ucwords(strtolower(trim($_POST['app_nombre_largo']))),
                'app_nombre_medium' => ucwords(strtolower(trim($_POST['app_nombre_medium']))),
                'app_nombre_corto' => strtoupper(trim($_POST['app_nombre_corto'])),
                'app_fecha_creacion' => date('Y-m-d'),
                'app_situacion' => 1
            ]);
            
            $resultado = $aplicacion->crear();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Aplicación registrada correctamente']);
        } catch (Exception $e) {
            error_log("Error guardando aplicación: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Aplicaciones
    public static function buscarAPI()
    {
        try {
            $aplicaciones = self::fetchArray("SELECT * FROM aplicacion WHERE app_situacion = 1 ORDER BY app_nombre_corto");
        
            if (!is_array($aplicaciones)) { $aplicaciones = []; }


            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Aplicaciones obtenidas correctamente',
                'data' => $aplicaciones
            ]);

        } catch (Exception $e) {
            error_log("Error buscando aplicaciones: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las aplicaciones',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    // API: Modificar Aplicación
    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['app_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validar campos requeridos
        $campos = [
            'app_nombre_largo', 'app_nombre_medium', 'app_nombre_corto'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validaciones
        if (strlen($_POST['app_nombre_corto']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre corto debe tener al menos 2 caracteres']);
            return;
        }

        if (strlen($_POST['app_nombre_largo']) < 5) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El nombre largo debe tener al menos 5 caracteres']);
            return;
        }

        try {
            $aplicacion = Aplicacion::find($id);

            if (!$aplicacion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Aplicación no encontrada']);
                return;
            }

            // Verificar duplicidad (excluyendo la aplicación actual)
            try {
                $existe = Aplicacion::verificarAplicacionExistente($_POST['app_nombre_corto'], $id);
                
                if ($existe) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe otra aplicación con ese nombre corto']);
                    return;
                }
            } catch (Exception $e) {
                error_log("Error verificando duplicidad en modificar: " . $e->getMessage());
                // Continuar sin verificar duplicidad si hay error
            }

            // Sincronizar datos
            $aplicacion->sincronizar([
                'app_nombre_largo' => ucwords(strtolower(trim($_POST['app_nombre_largo']))),
                'app_nombre_medium' => ucwords(strtolower(trim($_POST['app_nombre_medium']))),
                'app_nombre_corto' => strtoupper(trim($_POST['app_nombre_corto'])),
                'app_situacion' => 1
            ]);

            $resultado = $aplicacion->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Aplicación actualizada correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar la aplicación']);
            }

        } catch (Exception $e) {
            error_log("Error modificando aplicación: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Eliminar Aplicación
    public static function eliminarAPI()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $aplicacion = Aplicacion::find($id);
            if (!$aplicacion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Aplicación no encontrada']);
                return;
            }

            $aplicacion->sincronizar(['app_situacion' => 0]);
            $resultado = $aplicacion->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Aplicación eliminada correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo eliminar la aplicación']);
            }

        } catch (Exception $e) {
            error_log("Error eliminando aplicación: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}