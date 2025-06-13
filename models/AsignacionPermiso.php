<?php

namespace Model;

class AsignacionPermiso extends ActiveRecord
{
    public static $tabla = 'asig_permisos';
    
    public static $columnasDB = [
        'asignacion_usuario_id',
        'asignacion_permiso_id',
        'asignacion_fecha',
        'asignacion_fecha_quito',
        'asignacion_usuario_asigno',
        'asignacion_motivo',
        'asignacion_situacion'
    ];

    public static $idTabla = 'asignacion_id';

    // Propiedades
    public $asignacion_id;
    public $asignacion_usuario_id;
    public $asignacion_permiso_id;
    public $asignacion_fecha;
    public $asignacion_fecha_quito;
    public $asignacion_usuario_asigno;
    public $asignacion_motivo;
    public $asignacion_situacion;

    public function __construct($args = [])
    {
        $this->asignacion_id = $args['asignacion_id'] ?? null;
        $this->asignacion_usuario_id = $args['asignacion_usuario_id'] ?? null;
        $this->asignacion_permiso_id = $args['asignacion_permiso_id'] ?? null;
        $this->asignacion_fecha = $args['asignacion_fecha'] ?? null; // Cambiado para no asignar fecha automáticamente
        $this->asignacion_fecha_quito = $args['asignacion_fecha_quito'] ?? null;
        $this->asignacion_usuario_asigno = $args['asignacion_usuario_asigno'] ?? null;
        $this->asignacion_motivo = $args['asignacion_motivo'] ?? '';
        $this->asignacion_situacion = $args['asignacion_situacion'] ?? 1;
    }

    // Método para verificar si el usuario ya tiene un permiso asignado
    public static function verificarPermisoExistente($usuario_id, $permiso_id, $excluir_id = null)
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::$tabla . " 
                WHERE asignacion_usuario_id = ? AND asignacion_permiso_id = ? AND asignacion_situacion = 1";
        $params = [$usuario_id, $permiso_id];
        
        if ($excluir_id) {
            $sql .= " AND asignacion_id != ?";
            $params[] = $excluir_id;
        }
        
        $resultado = self::fetchArray($sql, $params);
        return $resultado[0]['count'] > 0;
    }

    // Método para obtener permisos de un usuario
    public static function obtenerPermisosPorUsuario($usuario_id)
    {
        $sql = "SELECT ap.*, p.permiso_nombre, p.permiso_clave, a.app_nombre_corto
                FROM " . self::$tabla . " ap
                INNER JOIN permiso p ON ap.asignacion_permiso_id = p.permiso_id
                INNER JOIN aplicacion a ON p.app_id = a.app_id
                WHERE ap.asignacion_usuario_id = ? AND ap.asignacion_situacion = 1
                ORDER BY a.app_nombre_corto, p.permiso_nombre";
        
        return self::fetchArray($sql, [$usuario_id]);
    }
}