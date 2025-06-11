<?php

namespace Model;

class Usuario extends ActiveRecord 
{
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
    
    // Propiedades del modelo
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

    public function __construct($args = [])
    {
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
        $this->usuario_token = $args['usuario_token'] ?? bin2hex(random_bytes(32));
        $this->usuario_fotografia = $args['usuario_fotografia'] ?? '';
        $this->usuario_situacion = $args['usuario_situacion'] ?? 1;
        $this->rol_id = $args['rol_id'] ?? 1;
        $this->usuario_ultimo_acceso = $args['usuario_ultimo_acceso'] ?? null;
    }

    /**
     * Validaciones antes de guardar
     */
    public function validar()
    {
        $errores = [];

        if (!$this->usuario_nom1) {
            $errores[] = 'El primer nombre es obligatorio';
        }

        if (!$this->usuario_ape1) {
            $errores[] = 'El primer apellido es obligatorio';
        }

        if (!$this->usuario_tel) {
            $errores[] = 'El teléfono es obligatorio';
        }

        if (!$this->usuario_dpi) {
            $errores[] = 'El DPI es obligatorio';
        }

        if (!$this->usuario_correo) {
            $errores[] = 'El correo es obligatorio';
        }

        if (!$this->usuario_direc) {
            $errores[] = 'La dirección es obligatoria';
        }

        if (!$this->rol_id) {
            $errores[] = 'El rol es obligatorio';
        }

        return $errores;
    }

    /**
     * Verificar si existe usuario con correo o DPI
     */
    public static function verificarUsuarioExistente($correo, $dpi, $excluirId = null)
    {
        try {
            $correo = self::sanitizarCadena($correo);
            $dpi = self::sanitizarCadena($dpi);

            $condCorreo = "usuario_correo = '$correo' AND usuario_situacion = 1";
            $condDpi = "usuario_dpi = '$dpi' AND usuario_situacion = 1";

            if ($excluirId) {
                $condCorreo .= " AND usuario_id != " . intval($excluirId);
                $condDpi .= " AND usuario_id != " . intval($excluirId);
            }

            $sql = "
                SELECT 
                    (SELECT COUNT(*) FROM usuario WHERE $condCorreo) AS correo_existe,
                    (SELECT COUNT(*) FROM usuario WHERE $condDpi) AS dpi_existe
            ";

            $resultado = self::fetchArray($sql);
            return $resultado[0] ?? ['correo_existe' => 0, 'dpi_existe' => 0];

        } catch (\Exception $e) {
            // Fallback en caso de error
            return ['correo_existe' => false, 'dpi_existe' => false];
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
     * Obtener usuario por ID
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
     * Obtener usuario activo por ID
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
            return null;
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