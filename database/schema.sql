-- ============================================================
-- EduAlert JJP - Esquema de Base de Datos (MySQL/MariaDB)
-- Institución Educativa Juan Jiménez Pimentel - Tarapoto
-- Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS edualert_jjp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edualert_jjp;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS rol_permisos;
DROP TABLE IF EXISTS permisos;
DROP TABLE IF EXISTS alertas_motivos;
DROP TABLE IF EXISTS seguimientos;
DROP TABLE IF EXISTS alertas;
DROP TABLE IF EXISTS calificaciones;
DROP TABLE IF EXISTS asistencias;
DROP TABLE IF EXISTS matriculas;
DROP TABLE IF EXISTS apoderados;
DROP TABLE IF EXISTS estudiantes;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS notificaciones;
DROP TABLE IF EXISTS auditoria;
DROP TABLE IF EXISTS parametros;
DROP TABLE IF EXISTS niveles_riesgo;
DROP TABLE IF EXISTS usuarios_cursos;
DROP TABLE IF EXISTS docentes_cursos;
DROP TABLE IF EXISTS cursos;
DROP TABLE IF EXISTS docentes;
DROP TABLE IF EXISTS secciones;
DROP TABLE IF EXISTS grados;
DROP TABLE IF EXISTS periodos;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Roles y permisos
-- ------------------------------------------------------------
CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL UNIQUE,
  slug VARCHAR(60) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE permisos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  descripcion VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rol_permisos (
  rol_id INT UNSIGNED NOT NULL,
  permiso_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (rol_id, permiso_id),
  CONSTRAINT fk_rp_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_permiso FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Usuarios
-- ------------------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rol_id INT UNSIGNED NOT NULL,
  nombres VARCHAR(80) NOT NULL,
  apellidos VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  telefono VARCHAR(20) NULL,
  estado TINYINT NOT NULL DEFAULT 1 COMMENT '1 activo, 0 inactivo',
  remember_token VARCHAR(100) NULL,
  intentos_fallidos INT NOT NULL DEFAULT 0,
  bloqueado_hasta DATETIME NULL,
  ultimo_acceso DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_rol FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(120) NOT NULL,
  token VARCHAR(100) NOT NULL,
  usado TINYINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pr_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Académico: grados, secciones, periodos, cursos, docentes
-- ------------------------------------------------------------
CREATE TABLE grados (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL,        -- Ej: 1° de Secundaria
  nivel VARCHAR(30) NOT NULL DEFAULT 'Secundaria',
  orden INT NOT NULL DEFAULT 0,
  estado TINYINT NOT NULL DEFAULT 1,
  UNIQUE KEY uq_grado_nombre (nombre, nivel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE secciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grado_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(20) NOT NULL,        -- A, B, C, D
  turno VARCHAR(20) NOT NULL DEFAULT 'Mañana',  -- Mañana/Tarde
  estado TINYINT NOT NULL DEFAULT 1,
  UNIQUE KEY uq_seccion (grado_id, nombre, turno),
  CONSTRAINT fk_seccion_grado FOREIGN KEY (grado_id) REFERENCES grados(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE periodos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60) NOT NULL,        -- I Bimestre, II Bimestre...
  anio SMALLINT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  activo TINYINT NOT NULL DEFAULT 0,  -- Solo un periodo activo
  estado TINYINT NOT NULL DEFAULT 1,
  UNIQUE KEY uq_periodo (nombre, anio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cursos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  area VARCHAR(80) NULL,
  descripcion VARCHAR(255) NULL,
  estado TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE docentes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  codigo VARCHAR(20) NULL UNIQUE,
  dni CHAR(8) NOT NULL UNIQUE,
  nombres VARCHAR(80) NOT NULL,
  apellidos VARCHAR(80) NOT NULL,
  email VARCHAR(120) NULL UNIQUE,
  telefono VARCHAR(20) NULL,
  especialidad VARCHAR(100) NULL,
  estado TINYINT NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_docente_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE docentes_cursos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  docente_id INT UNSIGNED NOT NULL,
  curso_id INT UNSIGNED NOT NULL,
  seccion_id INT UNSIGNED NOT NULL,
  periodo_id INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_dc (docente_id, curso_id, seccion_id, periodo_id),
  CONSTRAINT fk_dc_docente FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE CASCADE,
  CONSTRAINT fk_dc_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
  CONSTRAINT fk_dc_seccion FOREIGN KEY (seccion_id) REFERENCES secciones(id) ON DELETE CASCADE,
  CONSTRAINT fk_dc_periodo FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Niveles de riesgo (motor de alertas tempranas)
-- ------------------------------------------------------------
CREATE TABLE niveles_riesgo (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL UNIQUE,   -- Bajo, Medio, Alto
  color VARCHAR(10) NOT NULL DEFAULT 'success',
  min_puntaje INT NOT NULL DEFAULT 0,
  max_puntaje INT NOT NULL DEFAULT 100,
  descripcion VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Estudiantes y apoderados
-- ------------------------------------------------------------
CREATE TABLE estudiantes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL UNIQUE,       -- Ej: 2025-0001
  dni CHAR(8) NOT NULL UNIQUE,
  nombres VARCHAR(80) NOT NULL,
  apellidos VARCHAR(80) NOT NULL,
  fecha_nacimiento DATE NULL,
  sexo ENUM('M','F') NOT NULL DEFAULT 'M',
  direccion VARCHAR(180) NULL,
  telefono VARCHAR(20) NULL,
  email VARCHAR(120) NULL,
  estado TINYINT NOT NULL DEFAULT 1 COMMENT '1 activo, 0 inactivo',
  nivel_riesgo_id INT UNSIGNED NULL,
  puntaje_riesgo INT NULL,
  ultimo_promedio DECIMAL(5,2) NULL,
  ultimo_asistencia_pct DECIMAL(5,2) NULL,
  ultimo_desaprobados INT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_est_apellidos (apellidos, nombres),
  INDEX idx_est_dni (dni),
  INDEX idx_est_riesgo (nivel_riesgo_id),
  CONSTRAINT fk_est_nivel FOREIGN KEY (nivel_riesgo_id) REFERENCES niveles_riesgo(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE apoderados (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estudiante_id INT UNSIGNED NOT NULL,
  nombres VARCHAR(80) NOT NULL,
  apellidos VARCHAR(80) NOT NULL,
  dni CHAR(8) NULL,
  telefono VARCHAR(20) NULL,
  parentesco VARCHAR(40) NOT NULL DEFAULT 'Padre',
  ocupacion VARCHAR(80) NULL,
  UNIQUE KEY uq_apoderado (estudiante_id, dni),
  CONSTRAINT fk_apo_est FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Matrículas, calificaciones, asistencias
-- ------------------------------------------------------------
CREATE TABLE matriculas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estudiante_id INT UNSIGNED NOT NULL,
  periodo_id INT UNSIGNED NOT NULL,
  grado_id INT UNSIGNED NOT NULL,
  seccion_id INT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  estado TINYINT NOT NULL DEFAULT 1 COMMENT '1 activa, 0 retirado',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_matricula (estudiante_id, periodo_id),
  CONSTRAINT fk_mat_est FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
  CONSTRAINT fk_mat_periodo FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE CASCADE,
  CONSTRAINT fk_mat_grado FOREIGN KEY (grado_id) REFERENCES grados(id),
  CONSTRAINT fk_mat_seccion FOREIGN KEY (seccion_id) REFERENCES secciones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calificaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  matricula_id INT UNSIGNED NOT NULL,
  curso_id INT UNSIGNED NOT NULL,
  docente_id INT UNSIGNED NULL,
  periodo_id INT UNSIGNED NOT NULL,
  competencia VARCHAR(120) NOT NULL DEFAULT 'Logros de aprendizaje',
  nota DECIMAL(5,2) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_calificacion (matricula_id, curso_id, periodo_id),
  CONSTRAINT fk_cal_mat FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE,
  CONSTRAINT fk_cal_curso FOREIGN KEY (curso_id) REFERENCES cursos(id),
  CONSTRAINT fk_cal_docente FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE SET NULL,
  CONSTRAINT fk_cal_periodo FOREIGN KEY (periodo_id) REFERENCES periodos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE asistencias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  matricula_id INT UNSIGNED NOT NULL,
  estudiante_id INT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  estado ENUM('Presente','Falta','Tardanza','Justificado') NOT NULL DEFAULT 'Presente',
  observacion VARCHAR(255) NULL,
  registrado_por INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_asistencia (matricula_id, fecha),
  CONSTRAINT fk_asis_mat FOREIGN KEY (matricula_id) REFERENCES matriculas(id) ON DELETE CASCADE,
  CONSTRAINT fk_asis_est FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
  CONSTRAINT fk_asis_user FOREIGN KEY (registrado_por) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Riesgo y alertas
-- ------------------------------------------------------------
CREATE TABLE alertas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  estudiante_id INT UNSIGNED NOT NULL,
  periodo_id INT UNSIGNED NOT NULL,
  nivel_riesgo_id INT UNSIGNED NOT NULL,
  puntaje INT NOT NULL,
  descripcion TEXT NULL,
  estado ENUM('Nueva','En revisión','En seguimiento','Atendida','Cerrada') NOT NULL DEFAULT 'Nueva',
  responsable_id INT UNSIGNED NULL,
  fecha_generacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_alertas_estado (estado),
  INDEX idx_alertas_est_periodo (estudiante_id, periodo_id),
  CONSTRAINT fk_alert_est FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
  CONSTRAINT fk_alert_periodo FOREIGN KEY (periodo_id) REFERENCES periodos(id),
  CONSTRAINT fk_alert_nivel FOREIGN KEY (nivel_riesgo_id) REFERENCES niveles_riesgo(id),
  CONSTRAINT fk_alert_responsable FOREIGN KEY (responsable_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE alertas_motivos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  alerta_id INT UNSIGNED NOT NULL,
  tipo VARCHAR(50) NOT NULL,            -- promedio_bajo, inasistencias, ...
  detalle VARCHAR(255) NOT NULL,        -- Texto legible del motivo
  CONSTRAINT fk_motivo_alerta FOREIGN KEY (alerta_id) REFERENCES alertas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE seguimientos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  alerta_id INT UNSIGNED NOT NULL,
  fecha DATE NOT NULL,
  responsable_id INT UNSIGNED NULL,
  tipo_intervencion VARCHAR(60) NOT NULL,
  descripcion TEXT NULL,
  acuerdo TEXT NULL,
  compromiso TEXT NULL,
  proxima_revision DATE NULL,
  estado VARCHAR(40) NOT NULL DEFAULT 'En seguimiento',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_seg_alerta FOREIGN KEY (alerta_id) REFERENCES alertas(id) ON DELETE CASCADE,
  CONSTRAINT fk_seg_user FOREIGN KEY (responsable_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Notificaciones, auditoría, parámetros
-- ------------------------------------------------------------
CREATE TABLE notificaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  tipo VARCHAR(30) NOT NULL DEFAULT 'sistema',   -- alerta, sistema, seguimiento
  titulo VARCHAR(120) NOT NULL,
  mensaje TEXT NULL,
  url VARCHAR(255) NULL,
  leido TINYINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notif_user (user_id, leido),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE auditoria (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  modulo VARCHAR(50) NOT NULL,
  accion VARCHAR(60) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_fecha (created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE parametros (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(60) NOT NULL UNIQUE,
  valor VARCHAR(255) NOT NULL,
  descripcion VARCHAR(255) NULL,
  grupo VARCHAR(50) NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;