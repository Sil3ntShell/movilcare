<?php

namespace Model;

// Modelo Tabla Usuario
class Usuario extends ActiveRecord {
    
    public static $tabla = 'usuario';
    public static $columnasDB = [
        'usuario_nom1',
        'usuario_nom2', 
        'usuario_ape1',
        'usuario_ape2',
        'usuario_tel',
        'usuario_direc',
        'usuario_dpi',
        'usuario_correo',
        'usuario_contra',
        'usuario_token',
        'usuario_fotografia',
        'usuario_situacion',
        'rol_id'
    ];

    public static $idTabla = 'usuario_id';
    
    public $usuario_id;
    public $usuario_nom1;
    public $usuario_nom2;
    public $usuario_ape1;
    public $usuario_ape2;
    public $usuario_tel;
    public $usuario_direc;
    public $usuario_dpi;
    public $usuario_correo;
    public $usuario_contra;
    public $usuario_token;
    public $usuario_fecha_creacion;
    public $usuario_fecha_contra;
    public $usuario_fotografia;
    public $usuario_situacion;
    public $rol_id;
    public $usuario_ultimo_acceso;

    public function __construct($args = []){
        $this->usuario_id = $args['usuario_id'] ?? null;
        $this->usuario_nom1 = $args['usuario_nom1'] ?? '';
        $this->usuario_nom2 = $args['usuario_nom2'] ?? '';
        $this->usuario_ape1 = $args['usuario_ape1'] ?? '';
        $this->usuario_ape2 = $args['usuario_ape2'] ?? '';
        $this->usuario_tel = $args['usuario_tel'] ?? '';
        $this->usuario_direc = $args['usuario_direc'] ?? '';
        $this->usuario_dpi = $args['usuario_dpi'] ?? '';
        $this->usuario_correo = $args['usuario_correo'] ?? '';
        $this->usuario_contra = $args['usuario_contra'] ?? '';
        $this->usuario_token = $args['usuario_token'] ?? '';
        $this->usuario_fotografia = $args['usuario_fotografia'] ?? '';
        $this->usuario_situacion = $args['usuario_situacion'] ?? 1;
        $this->rol_id = $args['rol_id'] ?? 1;
        $this->usuario_ultimo_acceso = $args['usuario_ultimo_acceso'] ?? null;
    }

    /**
     * Verificar si existe usuario con correo o DPI
     * 
     * @param string $correo Correo electrónico a verificar
     * @param string $dpi DPI a verificar
     * @return array Array con 'correo_existe' y 'dpi_existe'
     */
    public static function verificarUsuarioExistente($correo, $dpi)
    {
        try {
            $query1 = "SELECT COUNT(*) as count FROM " . static::$tabla . " WHERE usuario_correo = ?";
            $query2 = "SELECT COUNT(*) as count FROM " . static::$tabla . " WHERE usuario_dpi = ?";
            
            $db = self::$db;
            
            // Verificar correo
            $stmt1 = $db->prepare($query1);
            $stmt1->execute([$correo]);
            $correo_count = $stmt1->fetch(\PDO::FETCH_ASSOC);
            
            // Verificar DPI
            $stmt2 = $db->prepare($query2);
            $stmt2->execute([$dpi]);
            $dpi_count = $stmt2->fetch(\PDO::FETCH_ASSOC);
            
            return [
                'correo_existe' => ($correo_count['count'] ?? 0) > 0,
                'dpi_existe' => ($dpi_count['count'] ?? 0) > 0
            ];
            
        } catch (\Exception $e) {
            error_log("Error en verificarUsuarioExistente: " . $e->getMessage());
            
            // FALLBACK: Si hay error, usar método básico
            try {
                $db = self::$db;
                $correo_escaped = $db->quote($correo);
                $dpi_escaped = $db->quote($dpi);
                
                $query1 = "SELECT COUNT(*) as count FROM " . static::$tabla . " WHERE usuario_correo = $correo_escaped";
                $query2 = "SELECT COUNT(*) as count FROM " . static::$tabla . " WHERE usuario_dpi = $dpi_escaped";
                
                $result1 = $db->query($query1)->fetch(\PDO::FETCH_ASSOC);
                $result2 = $db->query($query2)->fetch(\PDO::FETCH_ASSOC);
                
                return [
                    'correo_existe' => ($result1['count'] ?? 0) > 0,
                    'dpi_existe' => ($result2['count'] ?? 0) > 0
                ];
                
            } catch (\Exception $e2) {
                error_log("Error en fallback verificarUsuarioExistente: " . $e2->getMessage());
                return [
                    'correo_existe' => false,
                    'dpi_existe' => false
                ];
            }
        }
    }

    /**
     * Buscar el primer registro que coincida con la consulta
     * 
     * @param string $query Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return array|null
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
            error_log("Error en fetchFirst: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener usuario por ID - NOMBRE DIFERENTE PARA EVITAR CONFLICTO
     * 
     * @param int $id ID del usuario
     * @return Usuario|null
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
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener usuario activo por ID
     * 
     * @param int $id ID del usuario
     * @return Usuario|null
     */
    public static function obtenerUsuarioActivo($id)
    {
        try {
            $query = "SELECT * FROM " . static::$tabla . " WHERE " . static::$idTabla . " = ? AND usuario_situacion = 1";
            $resultado = self::fetchFirst($query, [$id]);
            
            if ($resultado) {
                return new static($resultado);
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log("Error en obtenerUsuarioActivo: " . $e->getMessage());
            return null;
        }
    }
}