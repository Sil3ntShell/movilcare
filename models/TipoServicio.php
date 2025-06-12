<?php

namespace Model;

class TipoServicio extends ActiveRecord 
{
    // Nombre de la tabla en la BD
    public static $tabla = 'tipo_servicio';

    // Columnas que se van a mapear a la BD
    public static $columnasDB = [
        'tipo_servicio_nombre',
        'tipo_servicio_descripcion',
        'tipo_servicio_precio_base',
        'tipo_servicio_tiempo_estimado',
        'tipo_servicio_situacion'
    ];

    public static $idTabla = 'tipo_servicio_id';
    
    // Propiedades
    public $tipo_servicio_id;
    public $tipo_servicio_nombre;
    public $tipo_servicio_descripcion;
    public $tipo_servicio_precio_base;
    public $tipo_servicio_tiempo_estimado;
    public $tipo_servicio_situacion;

    public function __construct($args = [])
    {
        $this->tipo_servicio_id = $args['tipo_servicio_id'] ?? null;
        $this->tipo_servicio_nombre = $args['tipo_servicio_nombre'] ?? '';
        $this->tipo_servicio_descripcion = $args['tipo_servicio_descripcion'] ?? '';
        $this->tipo_servicio_precio_base = $args['tipo_servicio_precio_base'] ?? 0;
        $this->tipo_servicio_tiempo_estimado = $args['tipo_servicio_tiempo_estimado'] ?? 0;
        $this->tipo_servicio_situacion = $args['tipo_servicio_situacion'] ?? 1;
    }

    public static function verificarNombreExistente($nombre, $excluirId = null)
    {
        $nombre = self::sanitizarCadena($nombre);

        $condNombre = "tipo_servicio_nombre = '$nombre' AND tipo_servicio_situacion = 1";

        if ($excluirId) {
            $condNombre .= " AND tipo_servicio_id != " . intval($excluirId);
        }
        // DEBUG: Agrega esta línea temporalmente
    error_log("SQL query: SELECT COUNT(*) as count FROM tipo_servicio WHERE $condNombre");
    
        $sql = "SELECT COUNT(*) as count FROM tipo_servicio WHERE $condNombre";
        $resultado = self::fetchArray($sql);
        
        return ($resultado[0]['count'] ?? 0) > 0;
    }

    public static function EliminarTipoServicio($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET tipo_servicio_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Obtener todos los tipos de servicio activos para selects
    public static function obtenerTiposServicioActivos()
    {
        $sql = "SELECT tipo_servicio_id, tipo_servicio_nombre FROM tipo_servicio WHERE tipo_servicio_situacion = 1 ORDER BY tipo_servicio_nombre ASC";
        return self::fetchArray($sql);
    }

    //Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}