<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use Model\Cliente;
use MVC\Router;

class ClienteController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        //  hasPermission(['permiso1']);
        
        $router->render('cliente/index', []);
    }

    // API: Guardar Cliente
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'cliente_nom1', 'cliente_nom2', 'cliente_ape1', 'cliente_ape2',
            'cliente_tel', 'cliente_correo', 'cliente_dpi', 'cliente_nit',
            'cliente_direc', 'cliente_observaciones'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar nombre y apellido
        if (strlen($_POST['cliente_nom1']) < 2 || strlen($_POST['cliente_ape1']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre o apellido demasiado corto']);
            return;
        }

        // Validar teléfono
        $tel = filter_var($_POST['cliente_tel'], FILTER_SANITIZE_NUMBER_INT);
        if (strlen($tel) != 8) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
            return;
        }

        // Validar correo
        if (!filter_var($_POST['cliente_correo'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
            return;
        }

        // Verificar duplicidad
        $existe = Cliente::verificarClienteExistente($_POST['cliente_correo'], $_POST['cliente_nit']);
        if ($existe['email_existe']) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Correo ya registrado']);
            return;
        }
        if ($existe['nit_existe']) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'NIT ya registrado']);
            return;
        }

        // Crear cliente
        try {
            $cliente = new Cliente([
                'cliente_nom1' => $_POST['cliente_nom1'],
                'cliente_nom2' => $_POST['cliente_nom2'],
                'cliente_ape1' => $_POST['cliente_ape1'],
                'cliente_ape2' => $_POST['cliente_ape2'],
                'cliente_dpi' => $_POST['cliente_dpi'],
                'cliente_nit' => $_POST['cliente_nit'],
                'cliente_correo' => $_POST['cliente_correo'],
                'cliente_tel' => $_POST['cliente_tel'],
                'cliente_direc' => $_POST['cliente_direc'],
                'cliente_observaciones' => $_POST['cliente_observaciones'],
                'cliente_fecha_registro' => date('Y-m-d'),
                'cliente_situacion' => 1
            ]);
            $cliente->crear();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Cliente registrado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Clientes
    public static function buscarAPI()
    {
        try {
            $clientes = self::fetchArray("SELECT * FROM cliente WHERE cliente_situacion = 1");
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $clientes]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener los datos', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Modificar Cliente

    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['cliente_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validar campos requeridos (igual que en guardarAPI)
        $campos = [
            'cliente_nom1', 'cliente_nom2', 'cliente_ape1', 'cliente_ape2',
            'cliente_tel', 'cliente_correo', 'cliente_dpi', 'cliente_nit',
            'cliente_direc', 'cliente_observaciones'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar nombre y apellido
        if (strlen($_POST['cliente_nom1']) < 2 || strlen($_POST['cliente_ape1']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre o apellido demasiado corto']);
            return;
        }

        // Validar teléfono
        $tel = filter_var($_POST['cliente_tel'], FILTER_SANITIZE_NUMBER_INT);
        if (strlen($tel) != 8) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
            return;
        }

        // Validar correo
        if (!filter_var($_POST['cliente_correo'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
            return;
        }

        try {
            $cliente = Cliente::find($id);

            if (!$cliente) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Cliente no encontrado']);
                return;
            }

            // Verificar duplicidad de correo y NIT (excluyendo el cliente actual)
            $existe = Cliente::verificarClienteExistente($_POST['cliente_correo'], $_POST['cliente_nit'], $id);
            if ($existe['email_existe']) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Correo ya registrado por otro cliente']);
                return;
            }
            if ($existe['nit_existe']) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'NIT ya registrado por otro cliente']);
                return;
            }

            // Sincronizar todos los campos necesarios
            $cliente->sincronizar([
                'cliente_nom1' => $_POST['cliente_nom1'],
                'cliente_nom2' => $_POST['cliente_nom2'],
                'cliente_ape1' => $_POST['cliente_ape1'],
                'cliente_ape2' => $_POST['cliente_ape2'],
                'cliente_dpi' => $_POST['cliente_dpi'],
                'cliente_nit' => $_POST['cliente_nit'],
                'cliente_correo' => $_POST['cliente_correo'],
                'cliente_tel' => $_POST['cliente_tel'],
                'cliente_direc' => $_POST['cliente_direc'],
                'cliente_observaciones' => $_POST['cliente_observaciones'],
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
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }


    // API: Eliminar Cliente (lógico)
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
            $cliente->actualizar();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Cliente eliminado correctamente']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }
}