<?php

namespace Model;

class Permiso extends ActiveRecord
{
    public static $tabla = 'permiso';
    
    public static $columnasDB = [
        'usuario_id',
        'app_id',
        'permiso_nombre',
        'permiso_clave',
        'permiso_desc',
        'permiso_tipo',
        'permiso_fecha',
        'permiso_usuario_asigno',
        'permiso_motivo',
        'permiso_situacion'
    ];

    public static $idTabla = 'permiso_id';

    // Propiedades
    public $permiso_id;
    public $usuario_id;
    public $app_id;
    public $permiso_nombre;
    public $permiso_clave;
    public $permiso_desc;
    public $permiso_tipo;
    public $permiso_usuario_asigno;
    public $permiso_motivo;
    public $permiso_situacion;

    public function __construct($args = [])
    {
        $this->permiso_id = $args['permiso_id'] ?? null;
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->app_id = $args['app_id'] ?? null;
        $this->permiso_nombre = $args['permiso_nombre'] ?? '';
        $this->permiso_clave = $args['permiso_clave'] ?? '';
        $this->permiso_desc = $args['permiso_desc'] ?? '';
        $this->permiso_tipo = $args['permiso_tipo'] ?? 'FUNCIONAL';
        $this->permiso_usuario_asigno = $args['permiso_usuario_asigno'] ?? null;
        $this->permiso_motivo = $args['permiso_motivo'] ?? '';
        $this->permiso_situacion = $args['permiso_situacion'] ?? 1;
    }

    public static function verificarPermisoExistente($usuarioId, $appId, $clave, $excluirId = null)
    {
        try {
            $clave = self::sanitizarCadena($clave);
            $condicion = "app_id = " . intval($appId) . " 
                         AND permiso_clave = '$clave' 
                         AND permiso_situacion = 1";

            if ($usuarioId) {
                $condicion .= " AND usuario_id = " . intval($usuarioId);
            }

            if ($excluirId) {
                $condicion .= " AND permiso_id != " . intval($excluirId);
            }

            $sql = "SELECT COUNT(*) as existe FROM " . self::$tabla . " WHERE $condicion";
            $resultado = self::fetchArray($sql);
            
            return isset($resultado[0]) && $resultado[0]['existe'] > 0;
        } catch (\Exception $e) {
            error_log("Error en verificarPermisoExistente: " . $e->getMessage());
            return false;
        }
    }

    public static function obtenerPermisosPorUsuario($usuarioId)
    {
        $sql = "SELECT p.*, a.app_nombre_corto 
                FROM " . self::$tabla . " p 
                INNER JOIN aplicacion a ON p.app_id = a.app_id 
                WHERE p.usuario_id = " . intval($usuarioId) . " AND p.permiso_situacion = 1 
                ORDER BY a.app_nombre_corto, p.permiso_nombre";
        return self::fetchArray($sql);
    }

    public static function obtenerPermisosPorAplicacion($appId)
    {
        $sql = "SELECT p.*, u.usuario_nom1, u.usuario_ape1
                FROM " . self::$tabla . " p 
                LEFT JOIN usuario u ON p.usuario_id = u.usuario_id
                WHERE p.app_id = " . intval($appId) . " AND p.permiso_situacion = 1 
                ORDER BY p.permiso_tipo, p.permiso_nombre";
        return self::fetchArray($sql);
    }

    public static function eliminarPermiso($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET permiso_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}