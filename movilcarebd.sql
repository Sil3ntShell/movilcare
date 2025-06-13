-- Tabla de roles de usuario
--CREATE TABLE rol(
--    rol_id SERIAL PRIMARY KEY,
--    rol_nombre VARCHAR(50) NOT NULL UNIQUE,
--    rol_descripcion VARCHAR(255),
--    rol_fecha_creacion DATE DEFAULT TODAY,
--    rol_situacion SMALLINT DEFAULT 1
--);

create database studio404




-- Tabla de marcas de celulares
CREATE TABLE marca(
    marca_id SERIAL PRIMARY KEY,
    marca_nombre VARCHAR(50) NOT NULL UNIQUE,
    marca_descripcion VARCHAR(255),
    marca_fecha_creacion DATE DEFAULT TODAY,
    marca_situacion SMALLINT DEFAULT 1
);

-- Tabla de modelos de celulares
CREATE TABLE modelo(
    modelo_id SERIAL PRIMARY KEY,
    marca_id INTEGER NOT NULL,
    modelo_nombre VARCHAR(100) NOT NULL,
    modelo_descripcion LVARCHAR(1024),
    modelo_fecha_creacion DATE DEFAULT TODAY,
    modelo_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (marca_id) REFERENCES marca(marca_id)
);

-- Tabla de inventario de celulares
CREATE TABLE inventario(
    inventario_id SERIAL PRIMARY KEY,
    modelo_id INTEGER NOT NULL,
    inventario_numero_serie VARCHAR(100) UNIQUE,
    inventario_imei VARCHAR(20) UNIQUE,
    inventario_estado VARCHAR(20),
    inventario_precio_compra DECIMAL(10,2),
    inventario_precio_venta DECIMAL(10,2),
    inventario_stock_disponible INTEGER DEFAULT 1,
    inventario_ubicacion VARCHAR(100),
    inventario_fecha_ingreso DATE DEFAULT TODAY,
    inventario_fecha_actualizacion DATE DEFAULT TODAY,
    inventario_observaciones LVARCHAR(1024),
    inventario_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (modelo_id) REFERENCES modelo(modelo_id)
);

-- Tabla de clientes
CREATE TABLE cliente(
    cliente_id SERIAL PRIMARY KEY,
    cliente_nom1 VARCHAR(50) NOT NULL,
    cliente_nom2 VARCHAR(50) NOT NULL,
    cliente_ape1 VARCHAR(50) NOT NULL,
    cliente_ape2 VARCHAR(50) NOT NULL,
    cliente_dpi VARCHAR(13) UNIQUE,
    cliente_nit VARCHAR(20),
    cliente_correo VARCHAR(100),
    cliente_tel INT NOT NULL,
    cliente_direc VARCHAR(150) NOT NULL,
    cliente_fecha_nacimiento DATE,
    cliente_fecha_registro DATE DEFAULT TODAY,
    cliente_observaciones LVARCHAR(1024),
    cliente_situacion SMALLINT DEFAULT 1
);

-- Tabla de empleados/técnicos
CREATE TABLE empleado(
    empleado_id SERIAL PRIMARY KEY,
    empleado_nom1 VARCHAR(50) NOT NULL,
    empleado_nom2 VARCHAR(50) NOT NULL,
    empleado_ape1 VARCHAR(50) NOT NULL,
    empleado_ape2 VARCHAR(50) NOT NULL,
    empleado_dpi VARCHAR(13) UNIQUE,
    empleado_tel INT,
    empleado_correo VARCHAR(100),
    empleado_especialidad VARCHAR(100),
    empleado_fecha_contratacion DATE DEFAULT TODAY,
    empleado_salario DECIMAL(10,2),
    empleado_situacion SMALLINT DEFAULT 1
);

-- Tabla de tipos de servicio
CREATE TABLE tipo_servicio(
    tipo_servicio_id SERIAL PRIMARY KEY,
    tipo_servicio_nombre VARCHAR(100) NOT NULL UNIQUE,
    tipo_servicio_descripcion LVARCHAR(1024),
    tipo_servicio_precio_base DECIMAL(10,2),
    tipo_servicio_tiempo_estimado INTEGER, -- en minutos
    tipo_servicio_situacion SMALLINT DEFAULT 1
);

-- Tabla de recepción de dispositivos para reparación
CREATE TABLE recepcion(
    recepcion_id SERIAL PRIMARY KEY,
    cliente_id INTEGER NOT NULL,
    empleado_id INTEGER NOT NULL,
    recepcion_fecha DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    recepcion_tipo_celular VARCHAR(100) NOT NULL,
    recepcion_marca VARCHAR(50) NOT NULL,
    recepcion_modelo VARCHAR(100),
    recepcion_imei VARCHAR(20),
    recepcion_numero_serie VARCHAR(100),
    recepcion_motivo_ingreso LVARCHAR(2056) NOT NULL,
    recepcion_estado_dispositivo LVARCHAR(1024),
    recepcion_accesorios LVARCHAR(1024),
    recepcion_observaciones_cliente LVARCHAR(1024),
    recepcion_costo_estimado DECIMAL(10,2),
    recepcion_tiempo_estimado INTEGER, -- en días
    recepcion_estado VARCHAR(50),
    recepcion_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (cliente_id) REFERENCES cliente(cliente_id),
    FOREIGN KEY (empleado_id) REFERENCES empleado(empleado_id)
);

-- Tabla de órdenes de trabajo (reparaciones)
CREATE TABLE orden_trabajo(
    orden_id SERIAL PRIMARY KEY,
    recepcion_id INTEGER NOT NULL,
    empleado_id INTEGER,
    tipo_servicio_id INTEGER NOT NULL,
    orden_fecha_asignacion DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    orden_fecha_inicio DATETIME YEAR TO SECOND,
    orden_fecha_finalizacion DATETIME YEAR TO SECOND,
    orden_diagnostico LVARCHAR(2056),
    orden_trabajo_realizado LVARCHAR(2056),
    orden_repuestos_utilizados LVARCHAR(1024),
    orden_costo_repuestos DECIMAL(10,2) DEFAULT 0,
    orden_costo_mano_obra DECIMAL(10,2) DEFAULT 0,
    orden_costo_total DECIMAL(10,2) DEFAULT 0,
    orden_estado VARCHAR(50),
    orden_observaciones LVARCHAR(1024),
    orden_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (recepcion_id) REFERENCES recepcion(recepcion_id),
    FOREIGN KEY (empleado_id) REFERENCES empleado(empleado_id),
    FOREIGN KEY (tipo_servicio_id) REFERENCES tipo_servicio(tipo_servicio_id)
);

-- Tabla de ventas
CREATE TABLE venta(
    venta_id SERIAL PRIMARY KEY,
    cliente_id INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL,
    venta_fecha DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    venta_subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    venta_descuento DECIMAL(10,2) DEFAULT 0,
    venta_impuestos DECIMAL(10,2) DEFAULT 0,
    venta_total DECIMAL(10,2) NOT NULL DEFAULT 0,
    venta_forma_pago VARCHAR(50),
    venta_estado VARCHAR(50),
    venta_observaciones LVARCHAR(1024),
    venta_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (cliente_id) REFERENCES cliente(cliente_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id)
);

-- Tabla de detalle de ventas
CREATE TABLE detalle_venta(
    detalle_id SERIAL PRIMARY KEY,
    venta_id INTEGER NOT NULL,
    inventario_id INTEGER,
    orden_id INTEGER, -- Para servicios de reparación
    detalle_tipo_item VARCHAR(50),
    detalle_descripcion VARCHAR(255) NOT NULL,
    detalle_cantidad INTEGER NOT NULL DEFAULT 1,
    detalle_precio_unitario DECIMAL(10,2) NOT NULL,
    detalle_subtotal DECIMAL(10,2) NOT NULL,
    detalle_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (venta_id) REFERENCES venta(venta_id),
    FOREIGN KEY (inventario_id) REFERENCES inventario(inventario_id),
    FOREIGN KEY (orden_id) REFERENCES orden_trabajo(orden_id)
);

-----------------------------------------------------------------------
-- Tabla de usuarios
CREATE TABLE usuario(
    usuario_id SERIAL PRIMARY KEY,
    usuario_nom1 VARCHAR(50) NOT NULL,
    usuario_nom2 VARCHAR(50) NOT NULL,
    usuario_ape1 VARCHAR(50) NOT NULL,
    usuario_ape2 VARCHAR(50) NOT NULL,
    usuario_tel INT NOT NULL,
    usuario_direc VARCHAR(150) NOT NULL,
    usuario_dpi VARCHAR(13) NOT NULL,
    usuario_correo VARCHAR(100) NOT NULL,
    usuario_contra LVARCHAR(1056) NOT NULL,
    usuario_token LVARCHAR(1056) NOT NULL,
    usuario_fecha_creacion DATE DEFAULT TODAY,
    usuario_fecha_contra DATE DEFAULT TODAY,
    usuario_fotografia LVARCHAR(2056),
    usuario_situacion SMALLINT DEFAULT 1,
    usuario_ultimo_acceso DATETIME YEAR TO SECOND
);


-- Tabla de aplicaciones (módulos o áreas del sistema)
CREATE TABLE aplicacion (
    app_id SERIAL PRIMARY KEY,
    app_nombre_largo VARCHAR(250) NOT NULL,
    app_nombre_medium VARCHAR(150) NOT NULL,
    app_nombre_corto VARCHAR(50) NOT NULL,
    app_fecha_creacion DATE DEFAULT TODAY,
    app_situacion SMALLINT DEFAULT 1
);

-- Tabla de rutas (acciones o endpoints de las aplicaciones)
CREATE TABLE rutas (
    ruta_id SERIAL PRIMARY KEY,
    ruta_app_id INT NOT NULL,
    ruta_nombre LVARCHAR(1056) NOT NULL,
    ruta_descripcion VARCHAR(250) NOT NULL,
    ruta_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (ruta_app_id) REFERENCES aplicacion(app_id)
);

-- Tabla de permisos (definición de permisos funcionales)
CREATE TABLE permiso (
    permiso_id SERIAL PRIMARY KEY,
    usuario_id INTEGER NOT NULL,
    app_id INTEGER NOT NULL,
    permiso_nombre VARCHAR(150) NOT NULL,
    permiso_clave VARCHAR(250) NOT NULL,
    permiso_desc VARCHAR(250) NOT NULL,
    permiso_tipo VARCHAR(50) DEFAULT 'FUNCIONAL',  
    permiso_fecha DATE DEFAULT TODAY,
    permiso_usuario_asigno INTEGER NOT NULL,   
    permiso_motivo VARCHAR(250),
    permiso_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id),
    FOREIGN KEY (app_id) REFERENCES aplicacion(app_id),
    FOREIGN KEY (permiso_usuario_asigno) REFERENCES usuario(usuario_id)
);

-- Tabla de asignación de permisos a usuarios
CREATE TABLE asig_permisos (
    asignacion_id SERIAL PRIMARY KEY,
    asignacion_usuario_id INT NOT NULL,
    asignacion_permiso_id INT NOT NULL,
    asignacion_fecha DATE DEFAULT TODAY,
    asignacion_fecha_quito DATE DEFAULT TODAY, //Fecha cuando se quito
    asignacion_usuario_asigno INT NOT NULL,
    asignacion_motivo VARCHAR(250) NOT NULL,
    asignacion_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (asignacion_usuario_id) REFERENCES usuario(usuario_id),
    FOREIGN KEY (asignacion_permiso_id) REFERENCES permiso(permiso_id)
);

-- Tabla de historial de actividad del usuario
CREATE TABLE historial_act (
    historial_id SERIAL PRIMARY KEY,
    historial_usuario_id INT NOT NULL,
    historial_fecha DATETIME YEAR TO MINUTE,
    historial_ruta INT NOT NULL,
    historial_ejecucion LVARCHAR(1056) NOT NULL,
    historial_ejecucion_status SMALLINT,
    historial_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (historial_usuario_id) REFERENCES usuario(usuario_id),
    FOREIGN KEY (historial_ruta) REFERENCES rutas(ruta_id)
);