<?php

namespace Model;

class HistorialActividad extends ActiveRecord
{
    
    public static $tabla = 'historial_act';
    
    public static $columnasDB = [
        'historial_usuario_id',
        'historial_fecha',
        'historial_ruta',
        'historial_ejecucion',
        'historial_ejecucion_status',
        'historial_situacion'
    ];

    public static $idTabla = 'historial_id';

    // Propiedades adicionales para auditoría
    public $historial_id;
    public $historial_usuario_id;
    public $historial_fecha;
    public $historial_ruta;
    public $historial_ejecucion;
    public $historial_ejecucion_status;
    public $historial_situacion;
    public $historial_tabla;
    public $historial_registro_id;
    public $historial_accion;
    public $historial_datos_anterior;
    public $historial_datos_nuevo;
    public $historial_ip;

    public function __construct($args = [])
    {
        $this->historial_id = $args['historial_id'] ?? null;
        $this->historial_usuario_id = $args['historial_usuario_id'] ?? null;
        $this->historial_fecha = $args['historial_fecha'] ?? date('Y-m-d H:i:s');
        $this->historial_ruta = $args['historial_ruta'] ?? null;
        $this->historial_ejecucion = $args['historial_ejecucion'] ?? '';
        $this->historial_ejecucion_status = $args['historial_ejecucion_status'] ?? 1;
        $this->historial_situacion = $args['historial_situacion'] ?? 1;
        $this->historial_tabla = $args['historial_tabla'] ?? '';
        $this->historial_registro_id = $args['historial_registro_id'] ?? null;
        $this->historial_accion = $args['historial_accion'] ?? '';
        $this->historial_datos_anterior = $args['historial_datos_anterior'] ?? null;
        $this->historial_datos_nuevo = $args['historial_datos_nuevo'] ?? null;
        $this->historial_ip = $args['historial_ip'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public static function registrarActividad($usuarioId, $rutaId, $ejecucion, $status = 1)
    {
        $historial = new self([
            'historial_usuario_id' => $usuarioId,
            'historial_ruta' => $rutaId,
            'historial_ejecucion' => $ejecucion,
            'historial_ejecucion_status' => $status
        ]);
        return $historial->crear();
    }

    public static function registrarCreacion($usuarioId, $rutaId, $tabla, $registroId, $datosNuevos)
    {
        $historial = new self([
            'historial_usuario_id' => $usuarioId,
            'historial_ruta' => $rutaId,
            'historial_tabla' => $tabla,
            'historial_registro_id' => $registroId,
            'historial_accion' => 'CREAR',
            'historial_datos_nuevo' => json_encode($datosNuevos),
            'historial_ejecucion' => "Creó registro en tabla $tabla con ID $registroId",
            'historial_ejecucion_status' => 1
        ]);
        return $historial->crear();
    }

    public static function registrarModificacion($usuarioId, $rutaId, $tabla, $registroId, $datosAnteriores, $datosNuevos)
    {
        $historial = new self([
            'historial_usuario_id' => $usuarioId,
            'historial_ruta' => $rutaId,
            'historial_tabla' => $tabla,
            'historial_registro_id' => $registroId,
            'historial_accion' => 'MODIFICAR',
            'historial_datos_anterior' => json_encode($datosAnteriores),
            'historial_datos_nuevo' => json_encode($datosNuevos),
            'historial_ejecucion' => "Modificó registro en tabla $tabla con ID $registroId",
            'historial_ejecucion_status' => 1
        ]);
        return $historial->crear();
    }

    public static function registrarEliminacion($usuarioId, $rutaId, $tabla, $registroId, $datosAnteriores)
    {
        $historial = new self([
            'historial_usuario_id' => $usuarioId,
            'historial_ruta' => $rutaId,
            'historial_tabla' => $tabla,
            'historial_registro_id' => $registroId,
            'historial_accion' => 'ELIMINAR',
            'historial_datos_anterior' => json_encode($datosAnteriores),
            'historial_ejecucion' => "Eliminó registro en tabla $tabla con ID $registroId",
            'historial_ejecucion_status' => 1
        ]);
        return $historial->crear();
    }

    public static function obtenerHistorialPorUsuario($usuarioId, $limite = 50)
    {
        $sql = "SELECT h.*, r.ruta_nombre, r.ruta_descripcion, a.app_nombre_corto
                FROM " . self::$tabla . " h
                LEFT JOIN rutas r ON h.historial_ruta = r.ruta_id
                LEFT JOIN aplicacion a ON r.ruta_app_id = a.app_id
                WHERE h.historial_usuario_id = " . intval($usuarioId) . "
                AND h.historial_situacion = 1
                ORDER BY h.historial_fecha DESC
                LIMIT " . intval($limite);
        return self::fetchArray($sql);
    }

    public static function obtenerHistorialPorTabla($tabla, $registroId = null, $limite = 100)
    {
        $sql = "SELECT h.*, u.usuario_nom1 || ' ' || u.usuario_ape1 as usuario_nombre
                FROM " . self::$tabla . " h
                LEFT JOIN usuario u ON h.historial_usuario_id = u.usuario_id
                WHERE h.historial_tabla = '$tabla'
                AND h.historial_situacion = 1";
        
        if ($registroId) {
            $sql .= " AND h.historial_registro_id = " . intval($registroId);
        }
        
        $sql .= " ORDER BY h.historial_fecha DESC LIMIT " . intval($limite);
        
        return self::fetchArray($sql);
    }

    public static function obtenerHistorialPorRuta($rutaId, $limite = 100)
    {
        $sql = "SELECT h.*, u.usuario_nom1 || ' ' || u.usuario_ape1 as usuario_nombre
                FROM " . self::$tabla . " h
                LEFT JOIN usuario u ON h.historial_usuario_id = u.usuario_id
                WHERE h.historial_ruta = " . intval($rutaId) . "
                AND h.historial_situacion = 1
                ORDER BY h.historial_fecha DESC
                LIMIT " . intval($limite);
        return self::fetchArray($sql);
    }

    public static function obtenerActividadReciente($limite = 50)
    {
        $sql = "SELECT h.*, u.usuario_nom1 || ' ' || u.usuario_ape1 as usuario_nombre,
                       r.ruta_nombre, a.app_nombre_corto
                FROM " . self::$tabla . " h
                LEFT JOIN usuario u ON h.historial_usuario_id = u.usuario_id
                LEFT JOIN rutas r ON h.historial_ruta = r.ruta_id
                LEFT JOIN aplicacion a ON r.ruta_app_id = a.app_id
                WHERE h.historial_situacion = 1
                ORDER BY h.historial_fecha DESC
                LIMIT " . intval($limite);
        return self::fetchArray($sql);
    }

    public static function obtenerEstadisticasUsuario($usuarioId, $dias = 30)
    {
        $sql = "SELECT 
                    COUNT(*) as total_actividades,
                    COUNT(CASE WHEN historial_ejecucion_status = 1 THEN 1 END) as exitosas,
                    COUNT(CASE WHEN historial_ejecucion_status = 0 THEN 1 END) as fallidas,
                    COUNT(CASE WHEN historial_accion = 'CREAR' THEN 1 END) as creaciones,
                    COUNT(CASE WHEN historial_accion = 'MODIFICAR' THEN 1 END) as modificaciones,
                    COUNT(CASE WHEN historial_accion = 'ELIMINAR' THEN 1 END) as eliminaciones
                FROM " . self::$tabla . "
                WHERE historial_usuario_id = " . intval($usuarioId) . "
                AND historial_fecha >= DATE_SUB(CURRENT, INTERVAL " . intval($dias) . " DAY)
                AND historial_situacion = 1";
        
        $resultado = self::fetchFirst($sql);
        return $resultado ?? [];
    }

    public static function limpiarHistorialAntiguo($diasAntiguedad = 90)
    {
        $sql = "UPDATE " . self::$tabla . " 
                SET historial_situacion = 0 
                WHERE historial_fecha < DATE_SUB(CURRENT, INTERVAL " . intval($diasAntiguedad) . " DAY)";
        return self::$db->exec($sql);
    }
}