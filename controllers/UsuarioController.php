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

    /**
     * API para buscar usuarios
     */
    public static function buscarAPI()
    {
        // Limpiar cualquier output previo
        ob_clean();
        
        try {
            $query = "SELECT 
                        usuario_id,
                        usuario_nom1,
                        usuario_nom2,
                        usuario_ape1,
                        usuario_ape2,
                        usuario_tel,
                        usuario_direc,
                        usuario_dpi,
                        usuario_correo,
                        usuario_fotografia,
                        usuario_situacion,
                        rol_id
                      FROM usuario 
                      WHERE usuario_situacion = 1
                      ORDER BY usuario_id DESC";
                      
            $usuarios = self::fetchArray($query);
            
            // Agregar nombre del rol
            foreach ($usuarios as &$usuario) {
                try {
                    $rolQuery = "SELECT rol_nombre FROM rol WHERE rol_id = " . intval($usuario['rol_id']) . " AND rol_situacion = 1";
                    $rolResult = self::fetchArray($rolQuery);
                    $usuario['rol_nombre'] = (!empty($rolResult)) ? $rolResult[0]['rol_nombre'] : 'Sin rol';
                } catch (Exception $e) {
                    $usuario['rol_nombre'] = 'Sin rol';
                }
            }
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Usuarios encontrados',
                'data' => $usuarios
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al cargar usuarios',
                'detalle' => $e->getMessage()
            ]);
        }
        exit();
    }

    /**
     * API para guardar usuario
     */
    public static function guardarAPI()
    {
        // Limpiar cualquier output previo
        ob_clean();
        
        try {
            // Validaciones básicas
            if (empty($_POST['usuario_nom1'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El primer nombre es obligatorio']);
                exit();
            }

            if (empty($_POST['usuario_ape1'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El primer apellido es obligatorio']);
                exit();
            }

            if (empty($_POST['usuario_tel']) || strlen($_POST['usuario_tel']) != 8) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El teléfono debe tener 8 dígitos']);
                exit();
            }

            if (empty($_POST['usuario_dpi']) || strlen($_POST['usuario_dpi']) != 13) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI debe tener 13 dígitos']);
                exit();
            }

            if (empty($_POST['usuario_correo']) || !filter_var($_POST['usuario_correo'], FILTER_VALIDATE_EMAIL)) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'Correo inválido']);
                exit();
            }

            if (empty($_POST['rol_id'])) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'Debe seleccionar un rol']);
                exit();
            }

            if (empty($_POST['usuario_contra']) || strlen($_POST['usuario_contra']) < 10) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'La contraseña debe tener al menos 10 caracteres']);
                exit();
            }

            // Verificar duplicados usando queries simples
            $checkCorreo = "SELECT COUNT(*) as count FROM usuario WHERE usuario_correo = '" . addslashes($_POST['usuario_correo']) . "'";
            $resultCorreo = self::fetchArray($checkCorreo);
            if ($resultCorreo[0]['count'] > 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El correo ya está registrado']);
                exit();
            }

            $checkDPI = "SELECT COUNT(*) as count FROM usuario WHERE usuario_dpi = '" . addslashes($_POST['usuario_dpi']) . "'";
            $resultDPI = self::fetchArray($checkDPI);
            if ($resultDPI[0]['count'] > 0) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'El DPI ya está registrado']);
                exit();
            }

            // Procesar foto
            $nombreFoto = '';
            if (isset($_FILES['usuario_fotografia']) && $_FILES['usuario_fotografia']['error'] === UPLOAD_ERR_OK) {
                $directorioDestino = $_SERVER['DOCUMENT_ROOT'] . '/empresa_celulares/storage/fotos_usuarios/';
                
                if (!is_dir($directorioDestino)) {
                    mkdir($directorioDestino, 0755, true);
                }
                
                $extension = strtolower(pathinfo($_FILES['usuario_fotografia']['name'], PATHINFO_EXTENSION));
                $nombreFoto = 'usuario_' . time() . '_' . uniqid() . '.' . $extension;
                $rutaCompleta = $directorioDestino . $nombreFoto;
                
                if (!move_uploaded_file($_FILES['usuario_fotografia']['tmp_name'], $rutaCompleta)) {
                    $nombreFoto = '';
                }
            }

            // Crear usuario con token asegurado
            $token = bin2hex(random_bytes(32));
            
            $usuario = new Usuario([
                'usuario_nom1' => ucwords(strtolower(trim($_POST['usuario_nom1']))),
                'usuario_nom2' => ucwords(strtolower(trim($_POST['usuario_nom2'] ?? ''))),
                'usuario_ape1' => ucwords(strtolower(trim($_POST['usuario_ape1']))),
                'usuario_ape2' => ucwords(strtolower(trim($_POST['usuario_ape2'] ?? ''))),
                'usuario_tel' => trim($_POST['usuario_tel']),
                'usuario_direc' => trim($_POST['usuario_direc']),
                'usuario_dpi' => trim($_POST['usuario_dpi']),
                'usuario_correo' => strtolower(trim($_POST['usuario_correo'])),
                'usuario_contra' => password_hash($_POST['usuario_contra'], PASSWORD_DEFAULT),
                'usuario_token' => $token,
                'usuario_fotografia' => $nombreFoto ?: '',
                'usuario_situacion' => 1,
                'rol_id' => intval($_POST['rol_id'])
            ]);

            $resultado = $usuario->crear();

            if ($resultado && $resultado['resultado']) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Usuario registrado correctamente'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al guardar usuario'
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
     * API para actualizar usuario
     */
    public static function actualizarAPI()
    {
        // Limpiar cualquier output previo
        ob_clean();
        
        try {
            $usuario_id = $_POST['usuario_id'] ?? null;
            
            if (!$usuario_id) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido']);
                exit();
            }

            $usuario = Usuario::obtenerPorId($usuario_id);
            if (!$usuario) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'Usuario no encontrado']);
                exit();
            }

            // Actualizar campos básicos
            $usuario->usuario_nom1 = ucwords(strtolower(trim($_POST['usuario_nom1'])));
            $usuario->usuario_nom2 = ucwords(strtolower(trim($_POST['usuario_nom2'] ?? '')));
            $usuario->usuario_ape1 = ucwords(strtolower(trim($_POST['usuario_ape1'])));
            $usuario->usuario_ape2 = ucwords(strtolower(trim($_POST['usuario_ape2'] ?? '')));
            $usuario->usuario_tel = trim($_POST['usuario_tel']);
            $usuario->usuario_direc = trim($_POST['usuario_direc']);
            $usuario->usuario_correo = strtolower(trim($_POST['usuario_correo']));
            $usuario->usuario_dpi = trim($_POST['usuario_dpi']);
            $usuario->rol_id = intval($_POST['rol_id']);

            // Actualizar contraseña solo si se proporciona
            if (!empty($_POST['usuario_contra']) && trim($_POST['usuario_contra']) !== '') {
                $usuario->usuario_contra = password_hash($_POST['usuario_contra'], PASSWORD_DEFAULT);
            }

            // Procesar nueva foto si se sube
            if (isset($_FILES['usuario_fotografia']) && $_FILES['usuario_fotografia']['error'] === UPLOAD_ERR_OK) {
                $directorioDestino = $_SERVER['DOCUMENT_ROOT'] . '/empresa_celulares/storage/fotos_usuarios/';
                
                if (!is_dir($directorioDestino)) {
                    mkdir($directorioDestino, 0755, true);
                }
                
                $extension = strtolower(pathinfo($_FILES['usuario_fotografia']['name'], PATHINFO_EXTENSION));
                $nombreFoto = 'usuario_' . time() . '_' . uniqid() . '.' . $extension;
                $rutaCompleta = $directorioDestino . $nombreFoto;
                
                if (move_uploaded_file($_FILES['usuario_fotografia']['tmp_name'], $rutaCompleta)) {
                    // Eliminar foto anterior
                    if ($usuario->usuario_fotografia) {
                        $rutaAnterior = $directorioDestino . $usuario->usuario_fotografia;
                        if (file_exists($rutaAnterior)) {
                            unlink($rutaAnterior);
                        }
                    }
                    $usuario->usuario_fotografia = $nombreFoto;
                }
            }

            $resultado = $usuario->guardar();

            if ($resultado && $resultado['resultado']) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Usuario actualizado correctamente'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al actualizar usuario'
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
     * API para eliminar usuario
     */
    public static function eliminarAPI()
    {
        // Limpiar cualquier output previo
        ob_clean();
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $usuario_id = $input['usuario_id'] ?? $_POST['usuario_id'] ?? null;
            
            if (!$usuario_id) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['codigo' => 0, 'mensaje' => 'ID inválido']);
                exit();
            }

            // Usar query directa para actualizar solo el estado
            $query = "UPDATE usuario SET usuario_situacion = 0 WHERE usuario_id = " . intval($usuario_id);
            $resultado = self::$db->exec($query);

            if ($resultado !== false) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Usuario eliminado correctamente'
                ]);
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al eliminar usuario'
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