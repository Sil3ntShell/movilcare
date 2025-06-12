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

    // MÉTODO CORREGIDO - Este era el problema principal
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
                    (SELECT COUNT(*) FROM usuario WHERE $condCorreo) AS email_existe,
                    (SELECT COUNT(*) FROM usuario WHERE $condDpi) AS dpi_existe
            ";

            $resultado = self::fetchArray($sql);
            
            // ASEGURAR que siempre retorne la estructura correcta
            if (!empty($resultado) && isset($resultado[0])) {
                return [
                    'email_existe' => intval($resultado[0]['email_existe']),
                    'dpi_existe' => intval($resultado[0]['dpi_existe'])
                ];
            } else {
                return ['email_existe' => 0, 'dpi_existe' => 0];
            }

        } catch (\Exception $e) {
            error_log("Error en verificarUsuarioExistente: " . $e->getMessage());
            // Fallback seguro en caso de error
            return ['email_existe' => 0, 'dpi_existe' => 0];
        }
    }

    public static function EliminarUsuario($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET usuario_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }

    // Obtener todos los usuarios activos para selects
    public static function obtenerUsuarioActivos()
    {
        $sql = "SELECT 
                usuario_id,
                CONCAT(usuario_nom1, ' ', usuario_ape1) AS usuario_nombre_completo
                FROM usuario
                WHERE usuario_situacion = 1
                ORDER BY usuario_nom1 ASC";
        
        return self::fetchArray($sql);
    }

    // Método para obtener usuarios con información de rol - NUEVO
    public static function obtenerUsuariosConRol()
    {
        $sql = "SELECT 
                u.usuario_id,
                u.usuario_nom1,
                u.usuario_nom2,
                u.usuario_ape1,
                u.usuario_ape2,
                u.usuario_tel,
                u.usuario_direc,
                u.usuario_dpi,
                u.usuario_correo,
                u.usuario_fotografia,
                u.usuario_situacion,
                u.rol_id,
                r.rol_nombre
              FROM usuario u
              LEFT JOIN rol r ON u.rol_id = r.rol_id
              WHERE u.usuario_situacion = 1
              ORDER BY u.usuario_id DESC";
        
        return self::fetchArray($sql);
    }

    // Sanitizar cadenas de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}