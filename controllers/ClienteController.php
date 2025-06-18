<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Cliente;

class ClienteController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        isAuth();
        $router->render('cliente/index', []);
    }

    public static function buscarAPI()
    {
        try {
            $clientes = self::fetchArray("SELECT * FROM cliente WHERE cliente_situacion = 1 ORDER BY cliente_nom1 ASC");

            if (!is_array($clientes)) {
                $clientes = [];
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Clientes obtenidos correctamente',
                'data' => $clientes
            ]);

        } catch (Exception $e) {
            error_log("Error buscando clientes: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los clientes',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function guardarAPI()
{
    getHeadersApi();

    if (empty($_POST['cliente_nom1']) || empty($_POST['cliente_ape1']) || empty($_POST['cliente_tel']) || empty($_POST['cliente_direc'])) {
        http_response_code(400);
        echo json_encode(['codigo' => 0, 'mensaje' => 'Faltan campos obligatorios']);
        return;
    }

    if (!empty($_POST['cliente_dpi']) && strlen($_POST['cliente_dpi']) != 13) {
        http_response_code(400);
        echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI debe tener 13 dígitos']);
        return;
    }

    if (!empty($_POST['cliente_tel']) && strlen($_POST['cliente_tel']) != 8) {
        http_response_code(400);
        echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
        return;
    }

    try {
        $existe = Cliente::verificarDpiNitExistente($_POST['cliente_dpi'] ?? '', $_POST['cliente_nit'] ?? '');

        if ($existe['dpi_existe']) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe un cliente con este DPI']);
            return;
        }

        if ($existe['nit_existe']) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Ya existe un cliente con este NIT']);
            return;
        }

        // Aquí CORREGIMOS la fecha:
        $fechaNacimiento = $_POST['cliente_fecha_nacimiento'] ?? null;

        if (!empty($fechaNacimiento)) {
            // Si viene como DD/MM/YYYY — convertir
            if (strpos($fechaNacimiento, '/')) {
                $partes = explode('/', $fechaNacimiento);
                $fechaNacimiento = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
            }

            // Si viene como YYYY-MM-DD (correcto), la dejamos igual

            // Validar que sea fecha válida
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Formato de fecha inválido']);
                return;
            }
        } else {
            // Si viene vacía, mandamos NULL
            $fechaNacimiento = null;
        }

        $cliente = new Cliente([
            'cliente_nom1' => trim($_POST['cliente_nom1']),
            'cliente_nom2' => trim($_POST['cliente_nom2'] ?? ''),
            'cliente_ape1' => trim($_POST['cliente_ape1']),
            'cliente_ape2' => trim($_POST['cliente_ape2'] ?? ''),
            'cliente_dpi' => trim($_POST['cliente_dpi'] ?? ''),
            'cliente_nit' => trim($_POST['cliente_nit'] ?? ''),
            'cliente_correo' => trim($_POST['cliente_correo'] ?? ''),
            'cliente_tel' => intval($_POST['cliente_tel']),
            'cliente_direc' => trim($_POST['cliente_direc']),
            'cliente_fecha_nacimiento' => $fechaNacimiento,
            'cliente_observaciones' => trim($_POST['cliente_observaciones'] ?? ''),
            'cliente_situacion' => 1
        ]);

        $cliente->crear();

        echo json_encode(['codigo' => 1, 'mensaje' => 'Cliente registrado correctamente']);

    } catch (Exception $e) {
        error_log("Error guardando cliente: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
    }
}


    public static function modificarAPI()
    {
        getHeadersApi();

        $id = $_POST['cliente_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Cliente no encontrado']);
                return;
            }

            $cliente->sincronizar([
                'cliente_nom1' => trim($_POST['cliente_nom1']),
                'cliente_nom2' => trim($_POST['cliente_nom2'] ?? ''),
                'cliente_ape1' => trim($_POST['cliente_ape1']),
                'cliente_ape2' => trim($_POST['cliente_ape2'] ?? ''),
                'cliente_dpi' => trim($_POST['cliente_dpi'] ?? ''),
                'cliente_nit' => trim($_POST['cliente_nit'] ?? ''),
                'cliente_correo' => trim($_POST['cliente_correo'] ?? ''),
                'cliente_tel' => intval($_POST['cliente_tel']),
                'cliente_direc' => trim($_POST['cliente_direc']),
                'cliente_fecha_nacimiento' => $_POST['cliente_fecha_nacimiento'] ?? null,
                'cliente_observaciones' => trim($_POST['cliente_observaciones'] ?? ''),
                'cliente_situacion' => 1
            ]);

            $resultado = $cliente->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Cliente actualizado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el cliente']);
            }

        } catch (Exception $e) {
            error_log("Error modificando cliente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    public static function eliminarAPI()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $cliente = Cliente::find($id);
            if (!$cliente) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Cliente no encontrado']);
                return;
            }

            $cliente->sincronizar(['cliente_situacion' => 0]);
            $resultado = $cliente->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Cliente eliminado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo eliminar el cliente']);
            }

        } catch (Exception $e) {
            error_log("Error eliminando cliente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}
