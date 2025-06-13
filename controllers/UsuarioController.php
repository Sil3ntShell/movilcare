<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Usuario;

class UsuarioController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('usuario/index', []);
    }

    // API: Guardar Usuario
    public static function guardarAPI()
    {
        getHeadersApi();

        $campos = [
            'usuario_nom1', 'usuario_nom2', 'usuario_ape1', 'usuario_ape2',
            'usuario_tel', 'usuario_direc', 'usuario_dpi', 'usuario_correo',
            'usuario_contra'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validar nombre y apellido
        if (strlen($_POST['usuario_nom1']) < 2 || strlen($_POST['usuario_ape1']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre o apellido demasiado corto']);
            return;
        }

        // Validar teléfono
        $tel = filter_var($_POST['usuario_tel'], FILTER_SANITIZE_NUMBER_INT);
        if (strlen($tel) != 8) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
            return;
        }

        // Validar DPI
        if (strlen($_POST['usuario_dpi']) != 13) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI debe tener 13 dígitos']);
            return;
        }

        // Validar correo
        if (!filter_var($_POST['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
            return;
        }

        // Validar contraseña
        if (strlen($_POST['usuario_contra']) < 10) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'La contraseña debe tener al menos 10 caracteres']);
            return;
        }

        // Verificar duplicidad
        try {
            $existe = Usuario::verificarUsuarioExistente($_POST['usuario_correo'], $_POST['usuario_dpi']);
            
            if (isset($existe['email_existe']) && $existe['email_existe'] > 0) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Correo ya registrado']);
                return;
            }
            
            if (isset($existe['dpi_existe']) && $existe['dpi_existe'] > 0) {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'DPI ya registrado']);
                return;
            }
        } catch (Exception $e) {
            error_log("Error verificando duplicidad: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error verificando datos existentes']);
            return;
        }

        // Procesar foto
        $nombreFoto = '';
        try {
            $nombreFoto = self::procesarFotografia();
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => $e->getMessage()]);
            return;
        }

        // Crear usuario
        try {
            $token = bin2hex(random_bytes(32));
            
            $usuario = new Usuario([
                'usuario_nom1' => ucwords(strtolower(trim($_POST['usuario_nom1']))),
                'usuario_nom2' => ucwords(strtolower(trim($_POST['usuario_nom2']))),
                'usuario_ape1' => ucwords(strtolower(trim($_POST['usuario_ape1']))),
                'usuario_ape2' => ucwords(strtolower(trim($_POST['usuario_ape2']))),
                'usuario_tel' => $_POST['usuario_tel'],
                'usuario_direc' => $_POST['usuario_direc'],
                'usuario_dpi' => $_POST['usuario_dpi'],
                'usuario_correo' => strtolower(trim($_POST['usuario_correo'])),
                'usuario_contra' => password_hash($_POST['usuario_contra'], PASSWORD_DEFAULT),
                'usuario_token' => $token,
                'usuario_fotografia' => $nombreFoto,
                'usuario_situacion' => 1
            ]);
            
            $resultado = $usuario->crear();

            echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario registrado correctamente']);
        } catch (Exception $e) {
            error_log("Error guardando usuario: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Buscar Usuarios
    public static function buscarAPI()
    {
        try {
            // Consulta simple sin JOIN de roles
            $usuarios = self::fetchArray("SELECT * FROM usuario WHERE usuario_situacion = 1");

            // Procesar cada usuario para verificar fotos
            foreach ($usuarios as &$usuario) {
                // Verificar si la foto existe físicamente
                if (!empty($usuario['usuario_fotografia'])) {
                    $rutaFoto = $_SERVER['DOCUMENT_ROOT'] . '/empresa_celulares/storage/fotos_usuarios/' . $usuario['usuario_fotografia'];
                    if (!file_exists($rutaFoto)) {
                        $usuario['usuario_fotografia'] = ''; // Limpiar si el archivo no existe
                    }
                }
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Usuarios obtenidos correctamente',
                'data' => $usuarios
            ]);

        } catch (Exception $e) {
            error_log("Error buscando usuarios: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los usuarios',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    // API: Modificar Usuario
    public static function modificarAPI()
    {
        getHeadersApi();
        
        $id = $_POST['usuario_id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado']);
            return;
        }

        // Validar campos requeridos
        $campos = [
            'usuario_nom1', 'usuario_nom2', 'usuario_ape1', 'usuario_ape2',
            'usuario_tel', 'usuario_direc', 'usuario_dpi', 'usuario_correo'
        ];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"]);
                return;
            }
        }

        // Validaciones (mismo código que en guardarAPI)
        if (strlen($_POST['usuario_nom1']) < 2 || strlen($_POST['usuario_ape1']) < 2) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Nombre o apellido demasiado corto']);
            return;
        }

        $tel = filter_var($_POST['usuario_tel'], FILTER_SANITIZE_NUMBER_INT);
        if (strlen($tel) != 8) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
            return;
        }

        if (strlen($_POST['usuario_dpi']) != 13) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI debe tener 13 dígitos']);
            return;
        }

        if (!filter_var($_POST['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
            return;
        }

        try {
            $usuario = Usuario::find($id);

            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no encontrado']);
                return;
            }

            // Verificar duplicidad (excluyendo el usuario actual)
            try {
                $existe = Usuario::verificarUsuarioExistente($_POST['usuario_correo'], $_POST['usuario_dpi'], $id);
                
                if (isset($existe['email_existe']) && $existe['email_existe'] > 0) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'Correo ya registrado por otro usuario']);
                    return;
                }
                
                if (isset($existe['dpi_existe']) && $existe['dpi_existe'] > 0) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'DPI ya registrado por otro usuario']);
                    return;
                }
            } catch (Exception $e) {
                error_log("Error verificando duplicidad en modificar: " . $e->getMessage());
                // Continuar sin verificar duplicidad si hay error
            }

            // Obtener foto actual
            $fotoActual = $usuario->usuario_fotografia ?? '';

            // Procesar nueva foto si se sube
            $nombreFoto = $fotoActual; // Mantener foto actual por defecto
            if (isset($_FILES['usuario_fotografia']) && $_FILES['usuario_fotografia']['error'] === UPLOAD_ERR_OK) {
                try {
                    $nombreFoto = self::procesarFotografia();
                    
                    // Eliminar foto anterior si existe y es diferente
                    if ($fotoActual && $nombreFoto !== $fotoActual) {
                        $rutaAnterior = $_SERVER['DOCUMENT_ROOT'] . '/empresa_celulares/storage/fotos_usuarios/' . $fotoActual;
                        if (file_exists($rutaAnterior)) {
                            unlink($rutaAnterior);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error procesando nueva foto: " . $e->getMessage());
                    // Si falla la nueva foto, mantener la actual
                    $nombreFoto = $fotoActual;
                }
            }

            // Sincronizar datos
            $datosActualizacion = [
                'usuario_nom1' => ucwords(strtolower(trim($_POST['usuario_nom1']))),
                'usuario_nom2' => ucwords(strtolower(trim($_POST['usuario_nom2']))),
                'usuario_ape1' => ucwords(strtolower(trim($_POST['usuario_ape1']))),
                'usuario_ape2' => ucwords(strtolower(trim($_POST['usuario_ape2']))),
                'usuario_tel' => $_POST['usuario_tel'],
                'usuario_direc' => $_POST['usuario_direc'],
                'usuario_dpi' => $_POST['usuario_dpi'],
                'usuario_correo' => strtolower(trim($_POST['usuario_correo'])),
                'usuario_fotografia' => $nombreFoto,
                'usuario_situacion' => 1,
            ];

            // Actualizar contraseña solo si se proporciona
            if (!empty($_POST['usuario_contra']) && trim($_POST['usuario_contra']) !== '') {
                if (strlen($_POST['usuario_contra']) < 10) {
                    http_response_code(400);
                    echo json_encode(['codigo' => 0, 'mensaje' => 'La contraseña debe tener al menos 10 caracteres']);
                    return;
                }
                $datosActualizacion['usuario_contra'] = password_hash($_POST['usuario_contra'], PASSWORD_DEFAULT);
            }

            $usuario->sincronizar($datosActualizacion);
            $resultado = $usuario->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario actualizado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar el usuario']);
            }

        } catch (Exception $e) {
            error_log("Error modificando usuario: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()]);
        }
    }

    // API: Eliminar Usuario
    public static function eliminarAPI()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido']);
            return;
        }

        try {
            $usuario = Usuario::find($id);
            if (!$usuario) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no encontrado']);
                return;
            }

            $usuario->sincronizar(['usuario_situacion' => 0]);
            $resultado = $usuario->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Usuario eliminado correctamente']);
            } else {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo eliminar el usuario']);
            }

        } catch (Exception $e) {
            error_log("Error eliminando usuario: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()]);
        }
    }

    // Método para procesar fotografía
    private static function procesarFotografia()
    {
        // Si no hay archivo, retornar vacío
        if (!isset($_FILES['usuario_fotografia']) || $_FILES['usuario_fotografia']['error'] === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        $archivo = $_FILES['usuario_fotografia'];

        // Validar errores de subida
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo: ' . $archivo['error']);
        }

        // Validar tamaño (máximo 2MB)
        $tamañoMaximo = 2 * 1024 * 1024; // 2MB en bytes
        if ($archivo['size'] > $tamañoMaximo) {
            throw new Exception('El archivo es muy grande. Máximo permitido: 2MB');
        }

        // Validar tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $tipoMime = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($tipoMime, $tiposPermitidos)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten: JPG, JPEG, PNG');
        }

        // Crear directorio si no existe
        $directorioDestino = $_SERVER['DOCUMENT_ROOT'] . '/empresa_celulares/storage/fotos_usuarios/';
        
        if (!is_dir($directorioDestino)) {
            if (!mkdir($directorioDestino, 0755, true)) {
                throw new Exception('No se pudo crear el directorio de fotografías');
            }
        }

        // Generar nombre único para el archivo
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'usuario_' . uniqid() . '_' . time() . '.' . $extension;
        $rutaCompleta = $directorioDestino . $nombreArchivo;

        // Mover archivo al directorio de destino
        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            throw new Exception('Error al guardar el archivo en el servidor');
        }

        // Verificar que el archivo se guardó correctamente
        if (!file_exists($rutaCompleta)) {
            throw new Exception('El archivo no se guardó correctamente');
        }

        return $nombreArchivo;
    }
}