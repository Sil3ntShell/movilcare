<?php

namespace Controllers;

use Exception;
use Model\Ruta;
use Model\HistorialActividad;
use MVC\Router;

class RutasController
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('rutas/index', []);
    }

    public static function guardarAPI()
    {
        getHeadersApi();
        session_start();

        $campos = ['ruta_app_id', 'ruta_nombre', 'ruta_path'];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        try {
            $ruta = new Ruta([
                'ruta_app_id' => $_POST['ruta_app_id'],
                'ruta_nombre' => $_POST['ruta_nombre'],
                'ruta_path' => $_POST['ruta_path'],
                'ruta_descripcion' => $_POST['ruta_descripcion'] ?? '',
                'ruta_icono' => $_POST['ruta_icono'] ?? '',
                'ruta_orden' => $_POST['ruta_orden'] ?? 0,
                'ruta_padre_id' => $_POST['ruta_padre_id'] ?? null,
                'ruta_situacion' => 1
            ]);

            // Validar
            $alertas = $ruta->validar();
            if (!empty($alertas)) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error de validación', 'alertas' => $alertas]);
                return;
            }

            $resultado = $ruta->crear();

            if ($resultado['resultado']) {
                // Registrar en historial
                HistorialActividad::registrarCreacion(
                    $_SESSION['usuario_id'] ?? 0,
                    null,
                    'rutas',
                    $resultado['id'],
                    $_POST
                );

                echo json_encode(['codigo' => 1, 'mensaje' => 'Ruta registrada correctamente', 'id' => $resultado['id']]);
            } else {
                http_response_code(500);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar la ruta']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al registrar ruta', 'detalle' => $e->getMessage()]);
        }
    }

    public static function buscarAPI()
    {
        try {
            $appId = $_GET['app_id'] ?? null;
            
            if ($appId) {
                $rutas = Ruta::obtenerRutasPorAplicacion($appId);
            } else {
                $rutas = Ruta::fetchArray("SELECT r.*, a.app_nombre_corto 
                                          FROM rutas r 
                                          INNER JOIN aplicacion a ON r.ruta_app_id = a.app_id 
                                          WHERE r.ruta_situacion = 1 
                                          ORDER BY a.app_nombre_corto, r.ruta_orden, r.ruta_nombre");
            }
            
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $rutas]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener las rutas', 'detalle' => $e->getMessage()]);
        }
    }

    public static function menuUsuarioAPI()
    {
        session_start();
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $appId = $_GET['app_id'] ?? null;

        if (!$usuarioId || !$appId) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Faltan parámetros']);
            return;
        }

        try {
            $menu = Ruta::obtenerMenuPorUsuario($usuarioId, $appId);
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $menu]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener el menú', 'detalle' => $e->getMessage()]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();
        session_start();

        $id = $_POST['ruta_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        $campos = ['ruta_app_id', 'ruta_nombre', 'ruta_path'];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        try {
            $ruta = Ruta::find($id);

            if (!$ruta) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Ruta no encontrada']);
                return;
            }

            // Guardar datos anteriores para historial
            $datosAnteriores = [
                'ruta_app_id' => $ruta->ruta_app_id,
                'ruta_nombre' => $ruta->ruta_nombre,
                'ruta_path' => $ruta->ruta_path,
                'ruta_descripcion' => $ruta->ruta_descripcion
            ];

            $ruta->sincronizar([
                'ruta_app_id' => $_POST['ruta_app_id'],
                'ruta_nombre' => $_POST['ruta_nombre'],
                'ruta_path' => $_POST['ruta_path'],
                'ruta_descripcion' => $_POST['ruta_descripcion'] ?? '',
                'ruta_icono' => $_POST['ruta_icono'] ?? '',
                'ruta_orden' => $_POST['ruta_orden'] ?? 0,
                'ruta_padre_id' => $_POST['ruta_padre_id'] ?? null,
                'ruta_situacion' => 1
            ]);

            // Validar
            $alertas = $ruta->validar();
            if (!empty($alertas)) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error de validación', 'alertas' => $alertas]);
                return;
            }

            $resultado = $ruta->actualizar();

            if ($resultado['resultado']) {
                // Registrar en historial
                HistorialActividad::registrarModificacion(
                    $_SESSION['usuario_id'] ?? 0,
                    null,
                    'rutas',
                    $id,
                    $datosAnteriores,
                    $_POST
                );

                echo json_encode(['codigo' => 1, 'mensaje' => 'Ruta actualizada correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar la ruta']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    public static function eliminarAPI()
    {
        session_start();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $ruta = Ruta::find($id);
            if (!$ruta) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Ruta no encontrada']);
                return;
            }

            // Verificar si tiene rutas hijas
            $resultado = Ruta::eliminarRuta($id);
            
            if (!$resultado['resultado']) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => $resultado['mensaje']]);
                return;
            }

            // Registrar en historial
            HistorialActividad::registrarEliminacion(
                $_SESSION['usuario_id'] ?? 0,
                null,
                'rutas',
                $id,
                ['ruta_nombre' => $ruta->ruta_nombre, 'ruta_path' => $ruta->ruta_path]
            );

            echo json_encode(['codigo' => 1, 'mensaje' => 'Ruta eliminada correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}