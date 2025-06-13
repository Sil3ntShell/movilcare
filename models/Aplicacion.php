<?php

namespace Model;

class Aplicacion extends ActiveRecord
{
    public static $tabla = 'aplicacion';
    
    public static $columnasDB = [
        'app_nombre_largo',
        'app_nombre_medium',
        'app_nombre_corto',
        'app_situacion'
    ];

    public static $idTabla = 'app_id';

    // Propiedades
    public $app_id;
    public $app_nombre_largo;
    public $app_nombre_medium;
    public $app_nombre_corto;
    public $app_fecha_creacion;
    public $app_situacion;

    public function __construct($args = [])
    {
        $this->app_id = $args['app_id'] ?? null;
        $this->app_nombre_largo = $args['app_nombre_largo'] ?? '';
        $this->app_nombre_medium = $args['app_nombre_medium'] ?? '';
        $this->app_nombre_corto = $args['app_nombre_corto'] ?? '';
        $this->app_fecha_creacion = $args['app_fecha_creacion'] ?? date('Y-m-d');
        $this->app_situacion = $args['app_situacion'] ?? 1;
    }

    public static function verificarAplicacionExistente($nombreCorto, $excluirId = null)
    {
        try {
            $nombreCorto = self::sanitizarCadena($nombreCorto);
            $condicion = "app_nombre_corto = '$nombreCorto' AND app_situacion = 1";

            if ($excluirId) {
                $condicion .= " AND app_id != " . intval($excluirId);
            }

            $sql = "SELECT COUNT(*) AS existe FROM " . self::$tabla . " WHERE $condicion";
            $resultado = self::fetchArray($sql);
            
            return isset($resultado[0]) && $resultado[0]['existe'] > 0;
        } catch (\Exception $e) {
            error_log("Error en verificarAplicacionExistente: " . $e->getMessage());
            return false;
        }
    }

    public static function obtenerAplicacionesActivas()
    {
        $sql = "SELECT * FROM " . self::$tabla . " WHERE app_situacion = 1 ORDER BY app_nombre_corto";
        return self::fetchArray($sql);
    }

    public static function eliminarAplicacion($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET app_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}