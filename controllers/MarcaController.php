<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use Model\Marca;
use MVC\Router;

class MarcaController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('marca/index', []);
    }

    // API: Guardar Marca
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'marca_nombre', 'marca_descripcion'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar nombre de marca
        if (strlen($_POST['marca_nombre']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de marca demasiado corto']);
            return;
        }

        // Verificar duplicidad de nombre
        $existe = Marca::verificarMarcaExistente($_POST['marca_nombre']);
        if ($existe['nombre_existe']) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de marca ya registrado']);
            return;
        }

        // Crear marca
        try {
            $marca = new Marca([
                'marca_nombre' => $_POST['marca_nombre'],
                'marca_descripcion' => $_POST['marca_descripcion'],
                'marca_situacion' => 1
            ]);
            $marca->crear();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Marca registrada correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Marcas
    public static function buscarAPI()
    {
        try {
            $marcas = self::fetchArray("SELECT * FROM marca WHERE marca_situacion = 1 ORDER BY marca_nombre ASC");
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $marcas]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener los datos', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Modificar Marca
    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['marca_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validar campos requeridos
        $campos = [
            'marca_nombre', 'marca_descripcion'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar nombre de marca
        if (strlen($_POST['marca_nombre']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de marca demasiado corto']);
            return;
        }

        try {
            $marca = Marca::find($id);

            if (!$marca) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Marca no encontrada']);
                return;
            }

            // Verificar duplicidad de nombre (excluyendo la marca actual)
            $existe = Marca::verificarMarcaExistente($_POST['marca_nombre'], $id);
            if ($existe['nombre_existe']) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre de marca ya registrado por otra marca']);
                return;
            }

            // Sincronizar todos los campos necesarios
            $marca->sincronizar([
                'marca_nombre' => $_POST['marca_nombre'],
                'marca_descripcion' => $_POST['marca_descripcion'],
                'marca_situacion' => 1
            ]);

            $resultado = $marca->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Marca actualizada correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar la marca']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Eliminar Marca (lógico)
    public static function eliminarAPI()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $marca = Marca::find($id);
            if (!$marca) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Marca no encontrada']);
                return;
            }

            $marca->sincronizar(['marca_situacion' => 0]);
            $marca->actualizar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Marca eliminada correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}