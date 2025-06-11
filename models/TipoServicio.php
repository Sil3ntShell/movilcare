<?php

namespace Model;

class TipoServicio extends ActiveRecord 
{
    public static $tabla = 'tipo_servicio';
    public static $columnasDB = [
        'tipo_servicio_nombre',
        'tipo_servicio_descripcion',
        'tipo_servicio_precio_base',
        'tipo_servicio_tiempo_estimado',
        'tipo_servicio_situacion'
    ];

    public static $idTabla = 'tipo_servicio_id';
    
    // Propiedades del modelo
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

    /**
     * Validaciones antes de guardar
     */
    public function validar()
    {
        $errores = [];

        if (!$this->tipo_servicio_nombre) {
            $errores[] = 'El nombre del servicio es obligatorio';
        }

        if (!$this->tipo_servicio_precio_base || $this->tipo_servicio_precio_base <= 0) {
            $errores[] = 'El precio base debe ser mayor a 0';
        }

        if (!$this->tipo_servicio_tiempo_estimado || $this->tipo_servicio_tiempo_estimado <= 0) {
            $errores[] = 'El tiempo estimado debe ser mayor a 0 minutos';
        }

        return $errores;
    }

    /**
     * Verificar si existe tipo de servicio con el mismo nombre
     */
    public static function verificarNombreExistente($nombre, $excluirId = null)
    {
        try {
            $nombre = self::sanitizarCadena($nombre);
            $condicion = "tipo_servicio_nombre = '$nombre' AND tipo_servicio_situacion = 1";

            if ($excluirId) {
                $condicion .= " AND tipo_servicio_id != " . intval($excluirId);
            }

            $sql = "SELECT COUNT(*) as count FROM tipo_servicio WHERE $condicion";
            $resultado = self::fetchArray($sql);
            
            return ($resultado[0]['count'] ?? 0) > 0;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener tipo de servicio por ID
     */
    public static function obtenerPorId($id)
    {
        try {
            $query = "SELECT * FROM " . static::$tabla . " WHERE " . static::$idTabla . " = ?";
            $resultado = self::fetchFirst($query, [$id]);
            
            if ($resultado) {
                return new static($resultado);
            }
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtener tipo de servicio activo por ID
     */
    public static function obtenerActivoPorId($id)
    {
        try {
            $query = "SELECT * FROM " . static::$tabla . " WHERE " . static::$idTabla . " = ? AND tipo_servicio_situacion = 1";
            $resultado = self::fetchFirst($query, [$id]);
            
            if ($resultado) {
                return new static($resultado);
            }
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Buscar el primer registro que coincida con la consulta
     */
    public static function fetchFirst($query, $params = [])
    {
        try {
            $db = self::$db;
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result ?: null;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convertir tiempo de minutos a formato legible
     */
    public function getTiempoFormateado()
    {
        $minutos = $this->tipo_servicio_tiempo_estimado;
        
        if ($minutos < 60) {
            return $minutos . ' minutos';
        } else if ($minutos < 1440) { // menos de 24 horas
            $horas = floor($minutos / 60);
            $mins = $minutos % 60;
            return $horas . 'h' . ($mins > 0 ? ' ' . $mins . 'm' : '');
        } else { // días
            $dias = floor($minutos / 1440);
            $horasRestantes = floor(($minutos % 1440) / 60);
            return $dias . ' días' . ($horasRestantes > 0 ? ' ' . $horasRestantes . 'h' : '');
        }
    }

    /**
     * Sanear cadena de entrada
     */
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}