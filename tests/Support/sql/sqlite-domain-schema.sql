PRAGMA foreign_keys = ON;

CREATE TABLE usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    correo VARCHAR(150) NOT NULL,
    nombre VARCHAR(80),
    apellido VARCHAR(80),
    edad INTEGER,
    telefono VARCHAR(20),
    ciudad VARCHAR(40),
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(40) CHECK (rol IS NULL OR rol IN ('cliente','encargado','mesero','jefe de cocina','cocinero','cajero','encargado de categoria')),
    id_categoria INTEGER REFERENCES categoria(id_categoria) ON UPDATE NO ACTION ON DELETE SET NULL,
    id_encargado INTEGER REFERENCES encargado_plato(id_encargado) ON UPDATE NO ACTION ON DELETE SET NULL,
    estado VARCHAR(10) NOT NULL DEFAULT 'activo'
);
CREATE UNIQUE INDEX uq_usuarios_correo ON usuarios(correo);
CREATE INDEX usuarios_id_encargado_foreign ON usuarios(id_encargado);
CREATE INDEX usuarios_id_categoria_foreign ON usuarios(id_categoria);

CREATE TABLE encargado_plato (
    id_encargado INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    user_id INTEGER NOT NULL DEFAULT 0 REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    edad INTEGER,
    cargo VARCHAR(60),
    id_categoria INTEGER REFERENCES categoria(id_categoria) ON UPDATE NO ACTION ON DELETE SET NULL,
    telefono VARCHAR(20),
    ciudad VARCHAR(40),
    estado VARCHAR(10) NOT NULL DEFAULT 'activo'
);
CREATE INDEX idx_encargado_usuario ON encargado_plato(user_id);
CREATE INDEX encargado_plato_id_categoria_foreign ON encargado_plato(id_categoria);

CREATE TABLE categoria (
    id_categoria INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    descripcion VARCHAR(255),
    id_encargado INTEGER NOT NULL REFERENCES encargado_plato(id_encargado) ON UPDATE CASCADE ON DELETE RESTRICT,
    estado VARCHAR(10) NOT NULL DEFAULT 'activo'
);
CREATE UNIQUE INDEX uq_categoria_nombre ON categoria(nombre);
CREATE INDEX idx_categoria_encargado ON categoria(id_encargado);

CREATE TABLE plato (
    id_plato INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT,
    nivel_dificultad INTEGER NOT NULL DEFAULT 0,
    foto VARCHAR(255),
    precio_final DECIMAL(10,2) NOT NULL,
    id_categoria INTEGER NOT NULL REFERENCES categoria(id_categoria) ON UPDATE CASCADE ON DELETE RESTRICT,
    estado VARCHAR(10) NOT NULL DEFAULT 'activo'
);
CREATE UNIQUE INDEX uq_plato_nombre ON plato(nombre);
CREATE INDEX idx_plato_categoria ON plato(id_categoria);

CREATE TABLE ingrediente (
    id_ingrediente INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    unidad_medida_almacen VARCHAR(30) NOT NULL,
    cantidad_actual_almacen DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    cantidad_minima DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado VARCHAR(10) NOT NULL DEFAULT 'activo'
);
CREATE UNIQUE INDEX uq_ingrediente_nombre ON ingrediente(nombre);

CREATE TABLE receta (
    id_plato INTEGER NOT NULL REFERENCES plato(id_plato) ON UPDATE CASCADE ON DELETE CASCADE,
    id_ingrediente INTEGER NOT NULL REFERENCES ingrediente(id_ingrediente) ON UPDATE CASCADE ON DELETE RESTRICT,
    cantidad_requerida DECIMAL(12,3) NOT NULL,
    unidad_medida VARCHAR(30) NOT NULL,
    pasos TEXT,
    PRIMARY KEY (id_plato, id_ingrediente)
);
CREATE INDEX idx_receta_ingrediente ON receta(id_ingrediente);

CREATE TABLE mesa (
    id_mesa INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    capacidad INTEGER NOT NULL DEFAULT 1,
    estado VARCHAR(30) NOT NULL DEFAULT 'disponible'
);

CREATE TABLE reserva (
    id_reserva INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    id_mesa INTEGER NOT NULL REFERENCES mesa(id_mesa) ON UPDATE NO ACTION ON DELETE CASCADE,
    nombre_cliente VARCHAR(120) NOT NULL,
    telefono VARCHAR(20),
    fecha_reserva DATE NOT NULL,
    hora_reserva TIME NOT NULL,
    cantidad_personas INTEGER NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'activa',
    origen VARCHAR(20) NOT NULL DEFAULT 'manual'
);
CREATE INDEX reserva_id_mesa_index ON reserva(id_mesa);

CREATE TABLE pedido (
    id_pedido INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    tipo_servicio VARCHAR(30) NOT NULL,
    total_pagar DECIMAL(10,2) NOT NULL DEFAULT 0.00
);

CREATE TABLE detalle_pedido (
    id_detalle INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    id_pedido INTEGER NOT NULL REFERENCES pedido(id_pedido) ON UPDATE CASCADE ON DELETE CASCADE,
    id_plato INTEGER NOT NULL REFERENCES plato(id_plato) ON UPDATE CASCADE ON DELETE RESTRICT,
    cantidad INTEGER NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    observaciones TEXT
);
CREATE INDEX idx_detalle_pedido ON detalle_pedido(id_pedido);
CREATE INDEX idx_detalle_plato ON detalle_pedido(id_plato);

CREATE TABLE pago (
    id_pago INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    id_pedido INTEGER NOT NULL REFERENCES pedido(id_pedido) ON UPDATE CASCADE ON DELETE CASCADE,
    metodo_pago VARCHAR(40) NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente'
);
CREATE INDEX idx_pago_pedido ON pago(id_pedido);

CREATE TABLE cliente (
    id_cliente INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    user_id INTEGER NOT NULL REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_cliente_usuario ON cliente(user_id);

CREATE TABLE permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at DATETIME,
    updated_at DATETIME
);
CREATE UNIQUE INDEX permissions_name_guard_name_unique ON permissions(name, guard_name);

CREATE TABLE roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at DATETIME,
    updated_at DATETIME
);
CREATE UNIQUE INDEX roles_name_guard_name_unique ON roles(name, guard_name);

CREATE TABLE model_has_permissions (
    permission_id INTEGER NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id INTEGER NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type)
);
CREATE INDEX model_has_permissions_model_id_model_type_index ON model_has_permissions(model_id, model_type);

CREATE TABLE model_has_roles (
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id INTEGER NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type)
);
CREATE INDEX model_has_roles_model_id_model_type_index ON model_has_roles(model_id, model_type);

CREATE TABLE role_has_permissions (
    permission_id INTEGER NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, role_id)
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at DATETIME,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    created_at DATETIME,
    updated_at DATETIME
);
CREATE UNIQUE INDEX users_email_unique ON users(email);

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at DATETIME
);

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INTEGER,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
CREATE INDEX sessions_user_id_index ON sessions(user_id);
CREATE INDEX sessions_last_activity_index ON sessions(last_activity);

CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);
CREATE INDEX cache_expiration_index ON cache(expiration);

CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);
CREATE INDEX cache_locks_expiration_index ON cache_locks(expiration);

CREATE TABLE jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts INTEGER NOT NULL,
    reserved_at INTEGER,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);
CREATE INDEX jobs_queue_index ON jobs(queue);

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT,
    cancelled_at INTEGER,
    created_at INTEGER NOT NULL,
    finished_at INTEGER
);

CREATE TABLE failed_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    uuid VARCHAR(255) NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX failed_jobs_uuid_unique ON failed_jobs(uuid);
CREATE INDEX failed_jobs_connection_queue_failed_at_index ON failed_jobs(connection, queue, failed_at);

CREATE TABLE migrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

CREATE TABLE alumnos (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    created_at DATETIME,
    updated_at DATETIME
);

CREATE TABLE clientes (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    nombre VARCHAR(20) NOT NULL,
    apellido VARCHAR(20) NOT NULL,
    edad INTEGER NOT NULL,
    created_at DATETIME,
    updated_at DATETIME
);

CREATE TABLE empleados (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    nombre VARCHAR(20) NOT NULL,
    apellido VARCHAR(20) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    ciudad VARCHAR(40) NOT NULL,
    edad INTEGER NOT NULL,
    estado VARCHAR(10) NOT NULL DEFAULT 'activo',
    created_at DATETIME,
    updated_at DATETIME
);
