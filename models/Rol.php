<?php

namespace Model;

class Rol extends ActiveRecord
{
    // Nombre de la tabla en la BD
    public static $tabla = 'rol';

    // Columnas que se van a mapear a la BD
    public static $columnasDB = [
        'rol_nombre',
        'rol_descripcion',
        'rol_situacion'
    ];

    public static $idTabla = 'rol_id';

    // Propiedades
    public $rol_id;
    public $rol_nombre;
    public $rol_descripcion;
    public $rol_fecha_creacion;
    public $rol_situacion;

    public function __construct($args = [])
    {
        $this->rol_id = $args['rol_id'] ?? null;
        $this->rol_nombre = $args['rol_nombre'] ?? '';
        $this->rol_descripcion = $args['rol_descripcion'] ?? '';
        $this->rol_situacion = $args['rol_situacion'] ?? 1;
    }

    public static function verificarRolExistente($nombre, $excluirId = null)
    {
        $nombre = self::sanitizarCadena($nombre);

        $condNombre = "rol_nombre = '$nombre' AND rol_situacion = 1";

        if ($excluirId) {
            $condNombre .= " AND rol_id != " . intval($excluirId);
        }

        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM rol WHERE $condNombre) AS nombre_existe
        ";

        $resultado = self::fetchArray($sql);
        return $resultado[0] ?? ['nombre_existe' => 0];
    }

    public static function EliminarRol($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET rol_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Obtener todos los roles activos para selects
    public static function obtenerRolesActivos()
    {
        $sql = "SELECT rol_id, rol_nombre FROM rol WHERE rol_situacion = 1 ORDER BY rol_nombre ASC";
        return self::fetchArray($sql);
    }

    // Obtener estadísticas de roles
    public static function obtenerEstadisticasRoles()
    {
        $sql = "
            SELECT 
                r.rol_id,
                r.rol_nombre,
                r.rol_descripcion,
                COUNT(u.usuario_id) as total_usuarios
            FROM rol r
            LEFT JOIN usuario u ON r.rol_id = u.rol_id AND u.usuario_situacion = 1
            WHERE r.rol_situacion = 1
            GROUP BY r.rol_id, r.rol_nombre, r.rol_descripcion
            ORDER BY r.rol_nombre ASC
        ";
        return self::fetchArray($sql);
    }

    //Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}