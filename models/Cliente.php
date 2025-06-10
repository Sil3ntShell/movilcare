<?php

namespace Model;

// Modelo Tabla Cliente
class Cliente extends ActiveRecord {
    
    public static $tabla = 'cliente';
    public static $columnasDB = [
        'cliente_nom1',
        'cliente_nom2',
        'cliente_ape1',
        'cliente_ape2',
        'cliente_dpi',
        'cliente_nit',
        'cliente_correo',
        'cliente_tel',
        'cliente_cel',
        'cliente_direc',
        'cliente_fecha_nacimiento',
        'cliente_observaciones',
        'cliente_situacion'
    ];
    
    public static $idTabla = 'cliente_id';
    
    public $cliente_id;
    public $cliente_nom1;
    public $cliente_nom2;
    public $cliente_ape1;
    public $cliente_ape2;
    public $cliente_dpi;
    public $cliente_nit;
    public $cliente_correo;
    public $cliente_tel;
    public $cliente_cel;
    public $cliente_direc;
    public $cliente_fecha_nacimiento;
    public $cliente_fecha_registro;
    public $cliente_observaciones;
    public $cliente_situacion;

    public function __construct($args = []){
        $this->cliente_id = $args['cliente_id'] ?? null;
        $this->cliente_nom1 = $args['cliente_nom1'] ?? '';
        $this->cliente_nom2 = $args['cliente_nom2'] ?? '';
        $this->cliente_ape1 = $args['cliente_ape1'] ?? '';
        $this->cliente_ape2 = $args['cliente_ape2'] ?? '';
        $this->cliente_dpi = $args['cliente_dpi'] ?? '';
        $this->cliente_nit = $args['cliente_nit'] ?? '';
        $this->cliente_correo = $args['cliente_correo'] ?? '';
        $this->cliente_tel = $args['cliente_tel'] ?? '';
        $this->cliente_cel = $args['cliente_cel'] ?? '';
        $this->cliente_direc = $args['cliente_direc'] ?? '';
        $this->cliente_fecha_nacimiento = $args['cliente_fecha_nacimiento'] ?? null;
        // $this->cliente_fecha_registro = $args['cliente_fecha_registro'] ?? null;
        $this->cliente_observaciones = $args['cliente_observaciones'] ?? '';
        $this->cliente_situacion = $args['cliente_situacion'] ?? 1;
    }
}