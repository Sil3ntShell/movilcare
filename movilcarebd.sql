create database empresa_celulares

-- Tabla de roles de usuario
CREATE TABLE rol(
    rol_id SERIAL PRIMARY KEY,
    rol_nombre VARCHAR(50) NOT NULL UNIQUE,
    rol_descripcion VARCHAR(255),
    rol_fecha_creacion DATE DEFAULT TODAY,
    rol_situacion SMALLINT DEFAULT 1
);

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
    rol_id INTEGER NOT NULL,
    usuario_ultimo_acceso DATETIME YEAR TO SECOND,
    FOREIGN KEY (rol_id) REFERENCES rol(rol_id)
);

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
    usuario_id INTEGER,
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
    empleado_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id)
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

-- Tabla de historial de ventas (resumen para estadísticas)
CREATE TABLE historial_venta(
    historial_id SERIAL PRIMARY KEY,
    venta_id INTEGER NOT NULL,
    historial_fecha_venta DATE NOT NULL,
    historial_mes INTEGER NOT NULL,
    historial_anio INTEGER NOT NULL,
    historial_total_venta DECIMAL(10,2) NOT NULL,
    historial_tipo_venta VARCHAR(50),
    cliente_id INTEGER NOT NULL,
    usuario_id INTEGER NOT NULL,
    historial_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (venta_id) REFERENCES venta(venta_id),
    FOREIGN KEY (cliente_id) REFERENCES cliente(cliente_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(usuario_id)
);

-- Tabla de configuración del sistema
CREATE TABLE configuracion(
    config_id SERIAL PRIMARY KEY,
    config_clave VARCHAR(100) NOT NULL UNIQUE,
    config_valor LVARCHAR(500),
    config_descripcion LVARCHAR(1024),
    config_fecha_actualizacion DATE DEFAULT TODAY,
    config_situacion SMALLINT DEFAULT 1
);

---------------------------------------------------------------------------------------------------------------------------------------------------------------

-- Crear índices para mejorar rendimiento
CREATE INDEX idx_usuario_dpi ON usuario(usuario_dpi);
CREATE INDEX idx_usuario_rol ON usuario(rol_id);
CREATE INDEX idx_usuario_correo ON usuario(usuario_correo);
CREATE INDEX idx_modelo_marca ON modelo(marca_id);
CREATE INDEX idx_inventario_modelo ON inventario(modelo_id);
CREATE INDEX idx_inventario_imei ON inventario(inventario_imei);
CREATE INDEX idx_inventario_serie ON inventario(inventario_numero_serie);
CREATE INDEX idx_cliente_dpi ON cliente(cliente_dpi);
CREATE INDEX idx_cliente_tel ON cliente(cliente_tel);
CREATE INDEX idx_empleado_dpi ON empleado(empleado_dpi);
CREATE INDEX idx_recepcion_cliente ON recepcion(cliente_id);
CREATE INDEX idx_recepcion_fecha ON recepcion(recepcion_fecha);
CREATE INDEX idx_orden_recepcion ON orden_trabajo(recepcion_id);
CREATE INDEX idx_orden_empleado ON orden_trabajo(empleado_id);
CREATE INDEX idx_venta_fecha ON venta(venta_fecha);
CREATE INDEX idx_venta_cliente ON venta(cliente_id);
CREATE INDEX idx_detalle_venta ON detalle_venta(venta_id);
CREATE INDEX idx_historial_fecha ON historial_venta(historial_fecha_venta);
CREATE INDEX idx_historial_mes_anio ON historial_venta(historial_mes, historial_anio);

-- Insertar datos básicos

-- Roles del sistema
INSERT INTO rol (rol_nombre, rol_descripcion) VALUES 
('ADMINISTRADOR', 'Acceso completo al sistema - Administra usuarios, configuración y reportes'),
('VENDEDOR', 'Acceso a ventas, inventario y clientes'),
('TECNICO', 'Acceso a módulo de reparaciones y órdenes de trabajo'),
('RECEPCIONISTA', 'Acceso a recepción de dispositivos y atención al cliente'),
('SUPERVISOR', 'Supervisión de ventas y reparaciones');

-- Marcas de celulares más populares en Guatemala
INSERT INTO marca (marca_nombre, marca_descripcion) VALUES
('SAMSUNG', 'Dispositivos Samsung - Líder mundial en smartphones'),
('APPLE', 'Dispositivos iPhone - Premium smartphones'),
('HUAWEI', 'Dispositivos Huawei - Tecnología china avanzada'),
('XIAOMI', 'Dispositivos Xiaomi - Relación calidad-precio'),
('OPPO', 'Dispositivos OPPO - Diseño y cámara'),
('VIVO', 'Dispositivos VIVO - Innovación y estilo'),
('MOTOROLA', 'Dispositivos Motorola - Durabilidad y rendimiento'),
('LG', 'Dispositivos LG - Tecnología coreana'),
('NOKIA', 'Dispositivos Nokia - Confiabilidad clásica'),
('REALME', 'Dispositivos Realme - Nueva generación'),
('ONEPLUS', 'Dispositivos OnePlus - Alto rendimiento'),
('HONOR', 'Dispositivos Honor - Tecnología joven');

-- Modelos populares por marca (Samsung)
INSERT INTO modelo (marca_id, modelo_nombre, modelo_descripcion) VALUES
(1, 'Galaxy A54 5G', 'Smartphone de gama media con conectividad 5G'),
(1, 'Galaxy S23', 'Flagship con cámara profesional'),
(1, 'Galaxy A34 5G', 'Económico con buenas prestaciones'),
(1, 'Galaxy S23 Ultra', 'Premium con S Pen incorporado'),
(1, 'Galaxy A14', 'Entrada de gama económica'),
(1, 'Galaxy M54 5G', 'Batería de larga duración'),
-- Apple
(2, 'iPhone 14', 'Última generación de iPhone'),
(2, 'iPhone 13', 'Generación anterior con excelente relación calidad-precio'),
(2, 'iPhone 14 Pro', 'Versión profesional con cámara avanzada'),
(2, 'iPhone SE 2022', 'Versión económica con chip A15'),
(2, 'iPhone 12', 'Modelo con 5G a buen precio'),
-- Xiaomi
(4, 'Redmi Note 12', 'Gama media con excelente batería'),
(4, 'POCO X5 Pro', 'Gaming y rendimiento'),
(4, 'Redmi 12C', 'Económico para uso básico'),
(4, 'Mi 13 Lite', 'Diseño premium y cámara'),
-- Motorola
(7, 'Moto G73 5G', 'Conectividad 5G accesible'),
(7, 'Moto E13', 'Básico y económico'),
(7, 'Edge 40', 'Gama alta con pantalla curva');

-- Tipos de servicio de reparación
INSERT INTO tipo_servicio (tipo_servicio_nombre, tipo_servicio_descripcion, tipo_servicio_precio_base, tipo_servicio_tiempo_estimado) VALUES
('CAMBIO DE PANTALLA', 'Reemplazo completo de pantalla táctil y LCD', 200.00, 90),
('REPARACION DE PANTALLA', 'Reparación menor de pantalla sin reemplazo', 80.00, 45),
('CAMBIO DE BATERIA', 'Reemplazo de batería original', 120.00, 60),
('REPARACION DE CARGA', 'Reparación o cambio de puerto de carga', 150.00, 120),
('FORMATEO COMPLETO', 'Formateo y reinstalación del sistema operativo', 75.00, 45),
('DESBLOQUEO DE OPERADORA', 'Liberación de red/operadora', 100.00, 30),
('CAMBIO DE CAMARA TRASERA', 'Reemplazo de cámara principal', 180.00, 75),
('CAMBIO DE CAMARA FRONTAL', 'Reemplazo de cámara selfie', 120.00, 60),
('REPARACION DE AUDIO', 'Reparación de altavoces o auricular', 90.00, 75),
('CAMBIO DE MICROFONO', 'Reemplazo de micrófono principal', 100.00, 60),
('REPARACION DE BOTONES', 'Reparación de botones de volumen/encendido', 80.00, 90),
('LIMPIEZA POR LIQUIDOS', 'Limpieza especializada por daños de agua', 150.00, 180),
('CAMBIO DE VIDRIO TRASERO', 'Reemplazo de tapa trasera', 100.00, 75),
('REPARACION DE WIFI/BLUETOOTH', 'Reparación de conectividad inalámbrica', 120.00, 120),
('DIAGNOSTICO COMPLETO', 'Diagnóstico detallado de fallas múltiples', 50.00, 60),
('RECUPERACION DE DATOS', 'Recuperación de fotos, contactos y archivos', 200.00, 240),
('ACTUALIZACION DE SOFTWARE', 'Actualización a última versión disponible', 60.00, 30),
('CALIBRACION DE PANTALLA', 'Calibración de sensibilidad táctil', 40.00, 30);

-- Configuración inicial del sistema
INSERT INTO configuracion (config_clave, config_valor, config_descripcion) VALUES
('NOMBRE_EMPRESA', 'TechCell Guatemala', 'Nombre de la empresa'),
('DIRECCION_EMPRESA', 'Zona 1, Ciudad de Guatemala', 'Dirección principal'),
('TELEFONO_EMPRESA', '22345678', 'Teléfono de contacto principal'),
('WHATSAPP_EMPRESA', '50212345678', 'WhatsApp Business'),
('EMAIL_EMPRESA', 'contacto@techcell.gt', 'Email principal de contacto'),
('SITIO_WEB', 'www.techcell.gt', 'Sitio web oficial'),
('MONEDA_PRINCIPAL', 'GTQ', 'Moneda utilizada (Quetzales)'),
('MONEDA_SECUNDARIA', 'USD', 'Moneda alternativa (Dólares)'),
('IVA_PORCENTAJE', '12', 'Porcentaje de IVA en Guatemala'),
('GARANTIA_REPARACION_DIAS', '30', 'Días de garantía en reparaciones'),
('GARANTIA_VENTA_DIAS', '15', 'Días de garantía en ventas'),
('HORARIO_ATENCION', '8:00 AM - 6:00 PM', 'Horario de atención al público'),
('DIAS_LABORALES', 'Lunes a Sábado', 'Días de trabajo'),
('BACKUP_AUTOMATICO', 'SI', 'Realizar backup automático'),
('BACKUP_HORA', '23:30', 'Hora del backup diario'),
('MAX_DESCUENTO_VENTA', '15', 'Máximo descuento permitido en ventas (%)'),
('COMISION_VENDEDOR', '3', 'Comisión por venta para vendedores (%)'),
('COMISION_TECNICO', '5', 'Comisión por reparación para técnicos (%)'),
('STOCK_MINIMO_ALERTA', '5', 'Cantidad mínima para alerta de stock'),
('TIEMPO_SESION_MINUTOS', '480', 'Tiempo máximo de sesión (8 horas)');

-- Crear triggers para automatización

-- Trigger para actualizar fecha de modificación en inventario
CREATE TRIGGER tr_inventario_actualizar
    UPDATE OF inventario_precio_compra, inventario_precio_venta, inventario_stock_disponible, 
              inventario_estado, inventario_observaciones ON inventario
    FOR EACH ROW
    (UPDATE inventario SET inventario_fecha_actualizacion = TODAY
     WHERE inventario_id = NEW.inventario_id);

-- Trigger para crear historial automático de ventas
CREATE TRIGGER tr_venta_crear_historial
    INSERT ON venta
    FOR EACH ROW
    (INSERT INTO historial_venta (venta_id, historial_fecha_venta, historial_mes, historial_anio, 
                                 historial_total_venta, historial_tipo_venta, cliente_id, usuario_id)
     VALUES (NEW.venta_id, NEW.venta_fecha::DATE, MONTH(NEW.venta_fecha), YEAR(NEW.venta_fecha), 
             NEW.venta_total, 'PENDIENTE', NEW.cliente_id, NEW.usuario_id));

-- Trigger para actualizar último acceso del usuario
CREATE TRIGGER tr_usuario_ultimo_acceso
    UPDATE OF usuario_situacion ON usuario
    FOR EACH ROW WHEN (NEW.usuario_situacion = 1)
    (UPDATE usuario SET usuario_ultimo_acceso = CURRENT YEAR TO SECOND
     WHERE usuario_id = NEW.usuario_id);

-- Crear vistas útiles para la aplicación

-- Vista completa de inventario con información de marca y modelo
CREATE VIEW v_inventario_detallado AS
SELECT 
    i.inventario_id,
    m.marca_nombre,
    mo.modelo_nombre,
    mo.modelo_descripcion,
    i.inventario_numero_serie,
    i.inventario_imei,
    i.inventario_estado,
    i.inventario_precio_compra,
    i.inventario_precio_venta,
    i.inventario_stock_disponible,
    i.inventario_ubicacion,
    i.inventario_fecha_ingreso,
    i.inventario_observaciones,
    (i.inventario_precio_venta - i.inventario_precio_compra) AS ganancia_estimada
FROM inventario i
JOIN modelo mo ON i.modelo_id = mo.modelo_id
JOIN marca m ON mo.marca_id = m.marca_id
WHERE i.inventario_situacion = 1
ORDER BY m.marca_nombre, mo.modelo_nombre;

-- Vista de clientes con información completa
CREATE VIEW v_clientes_info AS
SELECT 
    c.cliente_id,
    c.cliente_nom1 || ' ' || c.cliente_nom2 || ' ' || c.cliente_ape1 || ' ' || c.cliente_ape2 AS nombre_completo,
    c.cliente_dpi,
    c.cliente_nit,
    c.cliente_correo,
    c.cliente_tel,
    c.cliente_cel,
    c.cliente_direc,
    c.cliente_fecha_registro,
    -- Estadísticas del cliente
    (SELECT COUNT(*) FROM venta v WHERE v.cliente_id = c.cliente_id AND v.venta_situacion = 1) AS total_compras,
    (SELECT SUM(v.venta_total) FROM venta v WHERE v.cliente_id = c.cliente_id AND v.venta_situacion = 1) AS total_gastado,
    (SELECT COUNT(*) FROM recepcion r WHERE r.cliente_id = c.cliente_id AND r.recepcion_situacion = 1) AS total_reparaciones
FROM cliente c
WHERE c.cliente_situacion = 1
ORDER BY nombre_completo;

-- Vista de órdenes de trabajo en progreso
CREATE VIEW v_ordenes_pendientes AS
SELECT 
    ot.orden_id,
    c.cliente_nom1 || ' ' || c.cliente_ape1 AS cliente,
    c.cliente_tel,
    r.recepcion_tipo_celular || ' ' || r.recepcion_marca || ' ' || r.recepcion_modelo AS dispositivo,
    ts.tipo_servicio_nombre,
    CASE 
        WHEN ot.empleado_id IS NOT NULL THEN e.empleado_nom1 || ' ' || e.empleado_ape1
        ELSE 'Sin asignar'
    END AS tecnico,
    ot.orden_fecha_asignacion,
    ot.orden_estado,
    r.recepcion_estado,
    ot.orden_costo_total,
    -- Días transcurridos
    (TODAY - ot.orden_fecha_asignacion::DATE) AS dias_transcurridos
FROM orden_trabajo ot
JOIN recepcion r ON ot.recepcion_id = r.recepcion_id
JOIN cliente c ON r.cliente_id = c.cliente_id
JOIN tipo_servicio ts ON ot.tipo_servicio_id = ts.tipo_servicio_id
LEFT JOIN empleado e ON ot.empleado_id = e.empleado_id
WHERE ot.orden_situacion = 1 
  AND ot.orden_estado IN ('PENDIENTE', 'EN_PROCESO')
ORDER BY ot.orden_fecha_asignacion;

-- Vista de ventas del día actual
CREATE VIEW v_ventas_hoy AS
SELECT 
    v.venta_id,
    c.cliente_nom1 || ' ' || c.cliente_ape1 AS cliente,
    u.usuario_nom1 || ' ' || u.usuario_ape1 AS vendedor,
    v.venta_fecha,
    v.venta_subtotal,
    v.venta_descuento,
    v.venta_total,
    v.venta_forma_pago,
    v.venta_estado
FROM venta v
JOIN cliente c ON v.cliente_id = c.cliente_id
JOIN usuario u ON v.usuario_id = u.usuario_id
WHERE v.venta_fecha::DATE = TODAY
  AND v.venta_situacion = 1
ORDER BY v.venta_fecha DESC;

-- Vista de estadísticas mensuales
CREATE VIEW v_estadisticas_mes AS
SELECT 
    hv.historial_mes,
    hv.historial_anio,
    COUNT(*) AS total_ventas,
    SUM(hv.historial_total_venta) AS ingresos_totales,
    AVG(hv.historial_total_venta) AS venta_promedio,
    -- Ventas por tipo
    SUM(CASE WHEN hv.historial_tipo_venta = 'PRODUCTO' THEN 1 ELSE 0 END) AS ventas_productos,
    SUM(CASE WHEN hv.historial_tipo_venta = 'SERVICIO' THEN 1 ELSE 0 END) AS ventas_servicios,
    -- Top vendedor del mes
    (SELECT u.usuario_nom1 || ' ' || u.usuario_ape1 
     FROM usuario u 
     WHERE u.usuario_id = (
         SELECT hv2.usuario_id 
         FROM historial_venta hv2 
         WHERE hv2.historial_mes = hv.historial_mes 
           AND hv2.historial_anio = hv.historial_anio
         GROUP BY hv2.usuario_id 
         ORDER BY COUNT(*) DESC 
         LIMIT 1
     )) AS top_vendedor
FROM historial_venta hv
WHERE hv.historial_situacion = 1
GROUP BY hv.historial_mes, hv.historial_anio
ORDER BY hv.historial_anio DESC, hv.historial_mes DESC;

-- Procedimientos almacenados útiles

-- Procedimiento para crear usuario administrador inicial
CREATE PROCEDURE sp_crear_usuario_admin()
BEGIN
    DECLARE admin_existe INTEGER DEFAULT 0;
    
    -- Verificar si ya existe un administrador
    SELECT COUNT(*) INTO admin_existe 
    FROM usuario u 
    JOIN rol r ON u.rol_id = r.rol_id 
    WHERE r.rol_nombre = 'ADMINISTRADOR' AND u.usuario_situacion = 1;
    
    -- Si no existe, crear usuario administrador
    IF admin_existe = 0 THEN
        INSERT INTO usuario (usuario_nom1, usuario_nom2, usuario_ape1, usuario_ape2, 
                            usuario_tel, usuario_direc, usuario_dpi, usuario_correo, 
                            usuario_contra, usuario_token, rol_id)
        VALUES ('Admin', 'Sistema', 'Principal', 'TechCell', 
                12345678, 'Oficina Central Guatemala', '1234567890123', 
                'admin@techcell.gt', 'admin123hash', 'token_admin_inicial', 1);
        
        -- Crear empleado asociado
        INSERT INTO empleado (usuario_id, empleado_nom1, empleado_nom2, empleado_ape1, empleado_ape2,
                             empleado_dpi, empleado_especialidad, empleado_tel, empleado_correo)
        VALUES (LAST_INSERT_ID(), 'Admin', 'Sistema', 'Principal', 'TechCell',
                '1234567890123', 'Administración General', 12345678, 'admin@techcell.gt');
    END IF;
END PROCEDURE;

-- Procedimiento para consultar stock disponible de un modelo
CREATE PROCEDURE sp_consultar_stock_modelo(p_modelo_id INTEGER)
RETURNING INTEGER AS total_stock, DECIMAL(10,2) AS precio_promedio;
BEGIN
    SELECT 
        COALESCE(SUM(inventario_stock_disponible), 0),
        COALESCE(AVG(inventario_precio_venta), 0.00)
    INTO total_stock, precio_promedio
    FROM inventario 
    WHERE modelo_id = p_modelo_id 
      AND inventario_situacion = 1 
      AND inventario_estado IN ('NUEVO', 'USADO', 'REPARADO');
      
    RETURN total_stock, precio_promedio;
END PROCEDURE;

-- Procedimiento para actualizar stock después de venta
CREATE PROCEDURE sp_reducir_stock(p_inventario_id INTEGER, p_cantidad INTEGER)
BEGIN
    UPDATE inventario 
    SET inventario_stock_disponible = inventario_stock_disponible - p_cantidad,
        inventario_fecha_actualizacion = TODAY
    WHERE inventario_id = p_inventario_id
      AND inventario_stock_disponible >= p_cantidad;
      
    -- Verificar si la actualización fue exitosa
    IF SQLCODE = 0 THEN
        -- Log de la reducción de stock (opcional)
        NULL;
    END IF;
END PROCEDURE;

-- Función para generar código de recepción
CREATE FUNCTION f_generar_codigo_recepcion()
RETURNING VARCHAR(20);
    DEFINE codigo VARCHAR(20);
    DEFINE numero INTEGER;
    
    SELECT COALESCE(MAX(recepcion_id), 0) + 1 INTO numero FROM recepcion;
    LET codigo = 'REC-' || YEAR(TODAY) || '-' || LPAD(numero, 6, '0');
    
    RETURN codigo;
END FUNCTION;

-- Función para calcular ganancia de una venta
CREATE FUNCTION f_calcular_ganancia_venta(p_venta_id INTEGER)
RETURNING DECIMAL(10,2);
    DEFINE ganancia_total DECIMAL(10,2) DEFAULT 0.00;
    
    SELECT COALESCE(SUM(
        CASE 
            WHEN dv.inventario_id IS NOT NULL THEN 
                (dv.detalle_precio_unitario - i.inventario_precio_compra) * dv.detalle_cantidad
            ELSE 
                dv.detalle_subtotal * 0.7 -- Margen estimado para servicios
        END
    ), 0.00) INTO ganancia_total
    FROM detalle_venta dv
    LEFT JOIN inventario i ON dv.inventario_id = i.inventario_id
    WHERE dv.venta_id = p_venta_id AND dv.detalle_situacion = 1;
    
    RETURN ganancia_total;
END FUNCTION;

-- Ejecutar creación del usuario administrador inicial
EXECUTE PROCEDURE sp_crear_usuario_admin();

-- Crear secuencias para numeración automática
CREATE SEQUENCE seq_numero_venta START WITH 1;
CREATE SEQUENCE seq_numero_orden START WITH 1;
CREATE SEQUENCE seq_numero_recepcion START WITH 1;

-- Insertar algunos datos de prueba para inventario
INSERT INTO inventario (modelo_id, inventario_numero_serie, inventario_imei, inventario_estado, 
                       inventario_precio_compra, inventario_precio_venta, inventario_stock_disponible, 
                       inventario_ubicacion, inventario_observaciones) VALUES
(1, 'SAM-A54-001', '123456789012345', 'NUEVO', 1800.00, 2200.00, 3, 'Vitrina A1', 'Samsung Galaxy A54 5G Azul'),
(1, 'SAM-A54-002', '123456789012346', 'NUEVO', 1800.00, 2200.00, 2, 'Vitrina A1', 'Samsung Galaxy A54 5G Negro'),
(2, 'SAM-S23-001', '123456789012347', 'NUEVO', 4500.00, 5200.00, 1, 'Vitrina Premium', 'Samsung Galaxy S23 Blanco'),
(8, 'IPH-14-001', '123456789012348', 'NUEVO', 5000.00, 6000.00, 2, 'Vitrina Premium', 'iPhone 14 Azul 128GB'),
(9, 'IPH-13-001', '123456789012349', 'NUEVO', 4200.00, 4800.00, 3, 'Vitrina Premium', 'iPhone 13 Rosa 128GB');

COMMIT;