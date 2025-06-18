<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use Model\Inventario;
use Model\Modelo;
use MVC\Router;

class InventarioController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        isAuth();
        $router->render('inventario/index', []);
    }

    // API: Guardar Inventario
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'modelo_id', 'inventario_numero_serie', 'inventario_imei', 'inventario_estado',
            'inventario_precio_compra', 'inventario_precio_venta', 'inventario_stock_disponible',
            'inventario_ubicacion', 'inventario_observaciones'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validaciones específicas
        if (strlen($_POST['inventario_numero_serie']) < 5) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Número de serie demasiado corto']);
            return;
        }

        if (strlen($_POST['inventario_imei']) != 15) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'IMEI debe tener exactamente 15 dígitos']);
            return;
        }

        if (!is_numeric($_POST['inventario_precio_compra']) || $_POST['inventario_precio_compra'] <= 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Precio de compra inválido']);
            return;
        }

        if (!is_numeric($_POST['inventario_precio_venta']) || $_POST['inventario_precio_venta'] <= 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Precio de venta inválido']);
            return;
        }

        if (!is_numeric($_POST['inventario_stock_disponible']) || $_POST['inventario_stock_disponible'] < 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Stock inválido']);
            return;
        }

        // Validar que el modelo existe y está activo
        $modeloValido = self::fetchArray("SELECT modelo_id FROM modelo WHERE modelo_id = " . intval($_POST['modelo_id']) . " AND modelo_situacion = 1");
        if (empty($modeloValido)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Modelo no válido']);
            return;
        }

        // Verificar duplicidad de número de serie e IMEI
        $existe = Inventario::verificarInventarioExistente($_POST['inventario_numero_serie'], $_POST['inventario_imei']);
        if ($existe['serie_existe']) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Número de serie ya registrado']);
            return;
        }
        if ($existe['imei_existe']) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'IMEI ya registrado']);
            return;
        }

        // Crear inventario
        try {
            $inventario = new Inventario([
                'modelo_id' => $_POST['modelo_id'],
                'inventario_numero_serie' => $_POST['inventario_numero_serie'],
                'inventario_imei' => $_POST['inventario_imei'],
                'inventario_estado' => $_POST['inventario_estado'],
                'inventario_precio_compra' => $_POST['inventario_precio_compra'],
                'inventario_precio_venta' => $_POST['inventario_precio_venta'],
                'inventario_stock_disponible' => $_POST['inventario_stock_disponible'],
                'inventario_ubicacion' => $_POST['inventario_ubicacion'],
                'inventario_observaciones' => $_POST['inventario_observaciones'],
                'inventario_situacion' => 1
            ]);
            $inventario->crear();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Producto agregado al inventario correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Inventario
    public static function buscarAPI()
    {
        try {
            $inventarios = self::fetchArray("
                SELECT 
                    i.inventario_id,
                    i.modelo_id,
                    i.inventario_numero_serie,
                    i.inventario_imei,
                    i.inventario_estado,
                    i.inventario_precio_compra,
                    i.inventario_precio_venta,
                    i.inventario_stock_disponible,
                    i.inventario_ubicacion,
                    i.inventario_fecha_ingreso,
                    i.inventario_fecha_actualizacion,
                    i.inventario_observaciones,
                    i.inventario_situacion,
                    m.modelo_nombre,
                    ma.marca_nombre
                FROM inventario i
                INNER JOIN modelo m ON i.modelo_id = m.modelo_id
                INNER JOIN marca ma ON m.marca_id = ma.marca_id
                WHERE i.inventario_situacion = 1
                ORDER BY i.inventario_fecha_ingreso DESC, ma.marca_nombre ASC, m.modelo_nombre ASC
            ");
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $inventarios]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener los datos', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Obtener Modelos para el select
    public static function obtenerModelosAPI()
    {
        try {
            $modelos = Modelo::obtenerModelosActivos();
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $modelos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener modelos', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Modificar Inventario
    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['inventario_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validar campos requeridos
        $campos = [
            'modelo_id', 'inventario_numero_serie', 'inventario_imei', 'inventario_estado',
            'inventario_precio_compra', 'inventario_precio_venta', 'inventario_stock_disponible',
            'inventario_ubicacion', 'inventario_observaciones'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validaciones específicas (mismas que en guardar)
        if (strlen($_POST['inventario_numero_serie']) < 5) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Número de serie demasiado corto']);
            return;
        }

        if (strlen($_POST['inventario_imei']) != 15) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'IMEI debe tener exactamente 15 dígitos']);
            return;
        }

        if (!is_numeric($_POST['inventario_precio_compra']) || $_POST['inventario_precio_compra'] <= 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Precio de compra inválido']);
            return;
        }

        if (!is_numeric($_POST['inventario_precio_venta']) || $_POST['inventario_precio_venta'] <= 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Precio de venta inválido']);
            return;
        }

        if (!is_numeric($_POST['inventario_stock_disponible']) || $_POST['inventario_stock_disponible'] < 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Stock inválido']);
            return;
        }

        // Validar que el modelo existe y está activo
        $modeloValido = self::fetchArray("SELECT modelo_id FROM modelo WHERE modelo_id = " . intval($_POST['modelo_id']) . " AND modelo_situacion = 1");
        if (empty($modeloValido)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Modelo no válido']);
            return;
        }

        try {
            $inventario = Inventario::find($id);

            if (!$inventario) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Producto no encontrado']);
                return;
            }

            // Verificar duplicidad (excluyendo el registro actual)
            $existe = Inventario::verificarInventarioExistente($_POST['inventario_numero_serie'], $_POST['inventario_imei'], $id);
            if ($existe['serie_existe']) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Número de serie ya registrado por otro producto']);
                return;
            }
            if ($existe['imei_existe']) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'IMEI ya registrado por otro producto']);
                return;
            }

            // Sincronizar todos los campos necesarios
            $inventario->sincronizar([
                'modelo_id' => $_POST['modelo_id'],
                'inventario_numero_serie' => $_POST['inventario_numero_serie'],
                'inventario_imei' => $_POST['inventario_imei'],
                'inventario_estado' => $_POST['inventario_estado'],
                'inventario_precio_compra' => $_POST['inventario_precio_compra'],
                'inventario_precio_venta' => $_POST['inventario_precio_venta'],
                'inventario_stock_disponible' => $_POST['inventario_stock_disponible'],
                'inventario_ubicacion' => $_POST['inventario_ubicacion'],
                'inventario_observaciones' => $_POST['inventario_observaciones'],
                'inventario_situacion' => 1
            ]);

            $resultado = $inventario->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Producto actualizado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el producto']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Eliminar Inventario (lógico)
    public static function eliminarAPI()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $inventario = Inventario::find($id);
            if (!$inventario) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Producto no encontrado']);
                return;
            }

            $inventario->sincronizar(['inventario_situacion' => 0]);
            $inventario->actualizar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Producto eliminado del inventario correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}