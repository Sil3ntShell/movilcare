<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use Model\Modelo;
use Model\Marca;
use MVC\Router;

class ModeloController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        isAuth();
        $router->render('modelo/index', []);
    }

    // API: Guardar Modelo
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'marca_id', 'modelo_nombre', 'modelo_descripcion'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar nombre de modelo
        if (strlen($_POST['modelo_nombre']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de modelo demasiado corto']);
            return;
        }

        // Validar que la marca existe y está activa
        $marcaValida = self::fetchArray("SELECT marca_id, marca_situacion FROM marca WHERE marca_id = " . intval($_POST['marca_id']) . " AND marca_situacion = 1");
        if (empty($marcaValida)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Marca no válida']);
            return;
        }

        // Verificar duplicidad de nombre de modelo para la misma marca
        $existe = Modelo::verificarModeloExistente($_POST['marca_id'], $_POST['modelo_nombre']);
        if ($existe['nombre_existe']) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Modelo ya registrado para esta marca']);
            return;
        }

        // Crear modelo
        try {
            $modelo = new Modelo([
                'marca_id' => $_POST['marca_id'],
                'modelo_nombre' => $_POST['modelo_nombre'],
                'modelo_descripcion' => $_POST['modelo_descripcion'],
                'modelo_situacion' => 1
            ]);
            $modelo->crear();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Modelo registrado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Modelos
    public static function buscarAPI()
    {
        try {
            $modelos = self::fetchArray("
                SELECT 
                    m.modelo_id,
                    m.marca_id,
                    m.modelo_nombre,
                    m.modelo_descripcion,
                    m.modelo_fecha_creacion,
                    m.modelo_situacion,
                    ma.marca_nombre
                FROM modelo m
                INNER JOIN marca ma ON m.marca_id = ma.marca_id
                WHERE m.modelo_situacion = 1
                ORDER BY ma.marca_nombre ASC, m.modelo_nombre ASC
            ");
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $modelos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener los datos', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Obtener Marcas para el select
    public static function obtenerMarcasAPI()
    {
        try {
            error_log("Iniciando obtenerMarcasAPI");
            $marcas = Marca::obtenerMarcasActivas();
            error_log("Marcas obtenidas: " . count($marcas));
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $marcas]);
        } catch (Exception $e) {
            error_log("Error en obtenerMarcasAPI: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener marcas', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Modificar Modelo
    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['modelo_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validar campos requeridos
        $campos = [
            'marca_id', 'modelo_nombre', 'modelo_descripcion'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar nombre de modelo
        if (strlen($_POST['modelo_nombre']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de modelo demasiado corto']);
            return;
        }

        // Validar que la marca existe y está activa
        $marcaValida = self::fetchArray("SELECT marca_id, marca_situacion FROM marca WHERE marca_id = " . intval($_POST['marca_id']) . " AND marca_situacion = 1");
        if (empty($marcaValida)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Marca no válida']);
            return;
        }

        try {
            $modelo = Modelo::find($id);

            if (!$modelo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Modelo no encontrado']);
                return;
            }

            // Verificar duplicidad de nombre (excluyendo el modelo actual)
            $existe = Modelo::verificarModeloExistente($_POST['marca_id'], $_POST['modelo_nombre'], $id);
            if ($existe['nombre_existe']) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Modelo ya registrado para esta marca']);
                return;
            }

            // Sincronizar todos los campos necesarios
            $modelo->sincronizar([
                'marca_id' => $_POST['marca_id'],
                'modelo_nombre' => $_POST['modelo_nombre'],
                'modelo_descripcion' => $_POST['modelo_descripcion'],
                'modelo_situacion' => 1
            ]);

            $resultado = $modelo->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Modelo actualizado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el modelo']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Eliminar Modelo (lógico)
    public static function eliminarAPI()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $modelo = Modelo::find($id);
            if (!$modelo) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Modelo no encontrado']);
                return;
            }

            $modelo->sincronizar(['modelo_situacion' => 0]);
            $modelo->actualizar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Modelo eliminado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}