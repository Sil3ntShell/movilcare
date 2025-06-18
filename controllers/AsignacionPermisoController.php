<?php

namespace Controllers;

use Exception;
use Model\AsignacionPermiso;
use Model\ActiveRecord;
use MVC\Router;

class AsignacionPermisoController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        isAuth(); // verificar autenticación
        hasPermission(['SEG_ADMIN']); // verificar permisos
        $router->render('asignacion_permiso/index', []);
    }

    public static function guardarAPI()
    {
        // Limpiar output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        getHeadersApi();
        isAuthApi(); // verificar autenticación en API

        $campos = ['asignacion_usuario_id', 'asignacion_permiso_id', 'asignacion_usuario_asigno', 'asignacion_motivo'];

        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                http_response_code(400);
                echo json_encode(['codigo' => 0, 'mensaje' => "Falta el campo $campo"], JSON_UNESCAPED_SLASHES);
                exit;
            }
        }

        // Validar que los IDs sean números válidos
        $usuario_id = filter_var($_POST['asignacion_usuario_id'], FILTER_VALIDATE_INT);
        $permiso_id = filter_var($_POST['asignacion_permiso_id'], FILTER_VALIDATE_INT);
        $usuario_asigno = filter_var($_POST['asignacion_usuario_asigno'], FILTER_VALIDATE_INT);

        if (!$usuario_id || !$permiso_id || !$usuario_asigno) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'IDs inválidos'], JSON_UNESCAPED_SLASHES);
            exit;
        }

        // Verificar que no sea auto-asignación
        if ($usuario_id === $usuario_asigno) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Un usuario no puede asignarse permisos a sí mismo'], JSON_UNESCAPED_SLASHES);
            exit;
        }

        // Verificar que el permiso no esté ya asignado al usuario
        $sqlVerificar = "SELECT COUNT(*) as count FROM asig_permisos 
                         WHERE asignacion_usuario_id = " . intval($usuario_id) . " 
                         AND asignacion_permiso_id = " . intval($permiso_id) . " 
                         AND asignacion_situacion = 1";
        
        $permisoExiste = self::fetchArray($sqlVerificar);

        if ($permisoExiste[0]['count'] > 0) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Este permiso ya está asignado al usuario'], JSON_UNESCAPED_SLASHES);
            exit;
        }

        try {
            // Creaa la asignación sin fecha manual
            $asignacion = new AsignacionPermiso([
                'asignacion_usuario_id' => $usuario_id,
                'asignacion_permiso_id' => $permiso_id,
                'asignacion_usuario_asigno' => $usuario_asigno,
                'asignacion_motivo' => trim($_POST['asignacion_motivo']),
                'asignacion_situacion' => 1
            ]);

            $resultado = $asignacion->crear();
            
            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Permiso asignado correctamente'], JSON_UNESCAPED_SLASHES);
            } else {
                http_response_code(500);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Error al crear la asignación'], JSON_UNESCAPED_SLASHES);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al guardar', 'detalle' => $e->getMessage()], JSON_UNESCAPED_SLASHES);
        }
        exit;
    }

    public static function buscarAPI()
    {
        // Limpiar output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        getHeadersApi();
        isAuthApi(); // verificar autenticación en API

        try {
            $sql = "SELECT ap.*, p.permiso_nombre, p.permiso_tipo, p.permiso_desc, a.app_nombre_corto,
                           u.usuario_nom1, u.usuario_nom2, u.usuario_ape1, u.usuario_ape2,
                           ua.usuario_nom1 as asigno_nom1, ua.usuario_ape1 as asigno_ape1,
                           ap.asignacion_fecha,
                           ap.asignacion_fecha_quito
                    FROM asig_permisos ap
                    INNER JOIN permiso p ON ap.asignacion_permiso_id = p.permiso_id
                    INNER JOIN aplicacion a ON p.app_id = a.app_id
                    LEFT JOIN usuario u ON ap.asignacion_usuario_id = u.usuario_id
                    LEFT JOIN usuario ua ON ap.asignacion_usuario_asigno = ua.usuario_id
                    WHERE ap.asignacion_situacion = 1
                    ORDER BY ap.asignacion_fecha DESC";
            
            $asignaciones = self::fetchArray($sql);
            
            // Procesar los datos en PHP para formatear nombres y fechas

            foreach ($asignaciones as &$asignacion) {
                // Formatear nombre completo del usuario
                $nombreCompleto = trim($asignacion['usuario_nom1'] . ' ' . 
                                     ($asignacion['usuario_nom2'] ?? '') . ' ' . 
                                     $asignacion['usuario_ape1'] . ' ' . 
                                     ($asignacion['usuario_ape2'] ?? ''));
                $asignacion['usuario_nombre_completo'] = $nombreCompleto;
                
                // Formatear nombre del que asignacion

                $asignoCompleto = trim(($asignacion['asigno_nom1'] ?? '') . ' ' . 
                                      ($asignacion['asigno_ape1'] ?? ''));
                $asignacion['asigno_nombre_completo'] = $asignoCompleto;
                
                // Formatear fechas si existen

                if ($asignacion['asignacion_fecha']) {
                    $fecha = date('d/m/Y H:i', strtotime($asignacion['asignacion_fecha']));
                    $asignacion['fecha_formateada'] = $fecha;
                }
                
                if ($asignacion['asignacion_fecha_quito']) {
                    $fechaQuito = date('d/m/Y', strtotime($asignacion['asignacion_fecha_quito']));
                    $asignacion['fecha_quito_formateada'] = $fechaQuito;
                }
            }
            
            echo json_encode(['codigo' => 1, 'mensaje' => 'Éxito', 'data' => $asignaciones], JSON_UNESCAPED_SLASHES);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al obtener los datos', 'detalle' => $e->getMessage()], JSON_UNESCAPED_SLASHES);
        }
        exit;
    }

    public static function modificarAPI()
    {
        // Limpiar output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        getHeadersApi();
        isAuthApi(); //verificar autenticación en API

        $id = $_POST['asignacion_id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no proporcionado'], JSON_UNESCAPED_SLASHES);
            exit;
        }

        try {
            $asignacion = AsignacionPermiso::find($id);
            if (!$asignacion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Asignación no encontrada'], JSON_UNESCAPED_SLASHES);
                exit;
            }

            $asignacion->sincronizar([
                'asignacion_usuario_id' => $_POST['asignacion_usuario_id'],
                'asignacion_permiso_id' => $_POST['asignacion_permiso_id'],
                'asignacion_usuario_asigno' => $_POST['asignacion_usuario_asigno'],
                'asignacion_motivo' => $_POST['asignacion_motivo']
            ]);

            $resultado = $asignacion->actualizar();
            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Asignación actualizada correctamente'], JSON_UNESCAPED_SLASHES);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo actualizar'], JSON_UNESCAPED_SLASHES);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al modificar', 'detalle' => $e->getMessage()], JSON_UNESCAPED_SLASHES);
        }
        exit;
    }

    public static function eliminarAPI()
    {
        // Limpiar output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        getHeadersApi();
        isAuthApi(); // verificar autenticación en API

        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['codigo' => 0, 'mensaje' => 'ID no válido'], JSON_UNESCAPED_SLASHES);
            exit;
        }

        try {
            $asignacion = AsignacionPermiso::find($id);
            if (!$asignacion) {
                http_response_code(404);
                echo json_encode(['codigo' => 0, 'mensaje' => 'Asignación no encontrada'], JSON_UNESCAPED_SLASHES);
                exit;
            }

            $asignacion->sincronizar([
                'asignacion_situacion' => 0,
                'asignacion_fecha_quito' => date('Y-m-d')
            ]);
            $resultado = $asignacion->actualizar();

            if ($resultado['resultado']) {
                echo json_encode(['codigo' => 1, 'mensaje' => 'Permiso retirado correctamente'], JSON_UNESCAPED_SLASHES);
            } else {
                echo json_encode(['codigo' => 0, 'mensaje' => 'No se pudo retirar el permiso'], JSON_UNESCAPED_SLASHES);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['codigo' => 0, 'mensaje' => 'Error al eliminar', 'detalle' => $e->getMessage()], JSON_UNESCAPED_SLASHES);
        }
        exit;
    }
}