<?php

namespace Model;

class Cliente extends ActiveRecord
{
    // Nombre de la tabla en la BD
    public static $tabla = 'cliente';

    // Columnas que se van a mapear a la BD
    public static $columnasDB = [
        'cliente_nom1',
        'cliente_nom2',
        'cliente_ape1',
        'cliente_ape2',
        'cliente_dpi',
        'cliente_nit',
        'cliente_correo',
        'cliente_tel',
        'cliente_direc',
        'cliente_observaciones',
        'cliente_situacion'
    ];

    public static $idTabla = 'cliente_id';

    // Propiedades
    public $cliente_id;
    public $cliente_nom1;
    public $cliente_nom2;
    public $cliente_ape1;
    public $cliente_ape2;
    public $cliente_dpi;
    public $cliente_nit;
    public $cliente_correo;
    public $cliente_tel;
    public $cliente_direc;
    public $cliente_observaciones;
    public $cliente_situacion;

    public function __construct($args = [])
    {
        $this->cliente_id = $args['cliente_id'] ?? null;
        $this->cliente_nom1 = $args['cliente_nom1'] ?? '';
        $this->cliente_nom2 = $args['cliente_nom2'] ?? '';
        $this->cliente_ape1 = $args['cliente_ape1'] ?? '';
        $this->cliente_ape2 = $args['cliente_ape2'] ?? '';
        $this->cliente_dpi = $args['cliente_dpi'] ?? '';
        $this->cliente_nit = $args['cliente_nit'] ?? '';
        $this->cliente_correo = $args['cliente_correo'] ?? '';
        $this->cliente_tel = $args['cliente_tel'] ?? '';
        $this->cliente_direc = $args['cliente_direc'] ?? '';
        $this->cliente_observaciones = $args['cliente_observaciones'] ?? '';
        $this->cliente_situacion = $args['cliente_situacion'] ?? 1;
    }

    public static function verificarClienteExistente($correo, $nit, $excluirId = null)
    {
        $correo = self::sanitizarCadena($correo);
        $nit = self::sanitizarCadena($nit);

        $condCorreo = "cliente_correo = '$correo' AND cliente_situacion = 1";
        $condNit = "cliente_nit = '$nit' AND cliente_situacion = 1";

        if ($excluirId) {
            $condCorreo .= " AND cliente_id != " . intval($excluirId);
            $condNit .= " AND cliente_id != " . intval($excluirId);
        }

        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM cliente WHERE $condCorreo) AS email_existe,
                (SELECT COUNT(*) FROM cliente WHERE $condNit) AS nit_existe
        ";

        $resultado = self::fetchArray($sql);
        return $resultado[0] ?? ['email_existe' => 0, 'nit_existe' => 0];
    }

    

    public static function EliminarCliente($id)
    {
        $sql = "UPDATE " . self::$tabla . " SET cliente_situacion = 0 WHERE " . self::$idTabla . " = " . intval($id);
        return self::$db->exec($sql);
    }


    //Sanear cadena de entrada
    private static function sanitizarCadena($valor)
    {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}