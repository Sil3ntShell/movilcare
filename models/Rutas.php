<?php

namespace Model;

class Rutas extends ActiveRecord
{
    public static $tabla = 'rutas';
    
    public static $columnasDB = [
        'ruta_app_id',
        'ruta_nombre',
        'ruta_descripcion',
        'ruta_situacion'
    ];

    public static $idTabla = 'ruta_id';

    // Propiedades
    public $ruta_id;
    public $ruta_app_id;
    public $ruta_nombre;
    public $ruta_descripcion;
    public $ruta_situacion;

    public function __construct($args = [])
    {
        $this->ruta_id = $args['ruta_id'] ?? null;
        $this->ruta_app_id = $args['ruta_app_id'] ?? null;
        $this->ruta_nombre = $args['ruta_nombre'] ?? '';
        $this->ruta_descripcion = $args['ruta_descripcion'] ?? '';
        $this->ruta_situacion = $args['ruta_situacion'] ?? 1;
    }

    public function validar()
    {
        $errores = [];

        if (!$this->ruta_nombre || strlen(trim($this->ruta_nombre)) < 3) {
            $errores[] = 'El nombre de la ruta debe tener al menos 3 caracteres';
        }
        
        if (!$this->ruta_app_id) {
            $errores[] = 'La aplicación es obligatoria';
        }

        if (strlen($this->ruta_descripcion) > 250) {
            $errores[] = 'La descripción no puede exceder 250 caracteres';
        }

        return $errores;
    }

    public static function verificarRutaExistente($nombre, $appId, $excluirId = null)
    {
        try {
            $nombre = self::sanitizarCadena($nombre);
            $condicion = "ruta_nombre = '$nombre' AND ruta_app_id = " . intval($appId) . " AND ruta_situacion = 1";

            if ($excluirId) {
                $condicion .= " AND ruta_id != " . intval($excluirId);
            }

            $sql = "SELECT COUNT(*) AS existe FROM " . self::$tabla . " WHERE $condicion";
            $resultado = self::fetchArray($sql);
            
            return isset($resultado[0]) && $resultado[0]['existe'] > 0;
        } catch (\Exception $e) {
            error_log("Error en verificarRutaExistente: " . $e->getMessage());
            return false;
        }
    }

    public static function obtenerRutasPorAplicacion($appId)
    {
        $sql = "SELECT r.*, a.app_nombre_corto 
                FROM " . self::$tabla . " r 
                INNER JOIN aplicacion a ON r.ruta_app_id = a.app_id 
                WHERE r.ruta_app_id = " . intval($appId) . " AND r.ruta_situacion = 1 
                ORDER BY r.ruta_nombre";
        return self::fetchArray($sql);
    }

    public static function obtenerTodasLasRutas()
    {
        $sql = "SELECT r.*, a.app_nombre_corto, a.app_nombre_medium
                FROM " . self::$tabla . " r 
                INNER JOIN aplicacion a ON r.ruta_app_id = a.app_id 
                WHERE r.ruta_situacion = 1 
                ORDER BY a.app_nombre_corto, r.ruta_nombre";
        return self::fetchArray($sql);
    }

    public static function contarRutasPorAplicacion($appId)
    {
        $sql = "SELECT COUNT(*) as total FROM " . self::$tabla . " 
                WHERE ruta_app_id = " . intval($appId) . " AND ruta_situacion = 1";
        $resultado = self::fetchArray($sql);
        return $resultado[0]['total'] ?? 0;
    }

    public static function eliminarRuta($id)
    {
        try {
            $sql = "UPDATE " . self::$tabla . " SET ruta_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
            $resultado = self::$db->exec($sql);
            
            return [
                'resultado' => $resultado > 0,
                'mensaje' => $resultado > 0 ? 'Ruta eliminada correctamente' : 'No se pudo eliminar la ruta'
            ];
        } catch (\Exception $e) {
            return [
                'resultado' => false,
                'mensaje' => 'Error al eliminar: ' . $e->getMessage()
            ];
        }
    }

    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}