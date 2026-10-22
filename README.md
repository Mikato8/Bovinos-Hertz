# Hertz Bovinos — Repositorio de evidencia científica

Base de datos web (PHP + MySQL/PostgreSQL) que centraliza evidencia sobre el uso de frecuencias (Hz) en bovinos: vibración mecánica, sonido audible y campos electromagnéticos.

## Advertencia científica
- No promete aumentos de producción: la evidencia no los respalda de forma general.
- Marca 6.000–8.000 Hz como zona de riesgo auditivo bovino.
- Cada registro incluye un `evidence_level`.

## Stack
- PHP 8.1+ (sin Composer)
- MySQL 5.7+ / MariaDB 10.3+ / PostgreSQL 12+
- Chart.js (CDN o local)

## Estructura
```
public/       # document root (→ public_html/ en Hostinger)
src/          # lógica PHP (fuera del docroot)
templates/    # vistas
database/     # esquemas y semilla
assets/       # css/js estáticos
```

## Instalación local rápida
1. Copia `.env.example` a `.env` y rellena credenciales.
2. Genera el hash de admin: `php -r "echo password_hash('TuClave', PASSWORD_DEFAULT);"`
3. Importa `database/schema_mysql.sql`, `seed.sql`, `migration_submissions_mysql.sql`.
4. Sirve `public/` con `php -S localhost:8000 -t public`.

## Despliegue en Hostinger (MySQL)
1. Crea la base de datos MySQL en hPanel y anota host (`localhost`), nombre, usuario, contraseña.
2. Sube `public/` → `public_html/`, y `src/`, `templates/`, `database/`, `.env` → raíz de la cuenta.
3. Rellena `.env` con las credenciales reales.
4. Ejecuta `_install.php` una vez (crea esquema + semilla) y bórralo.
5. Accede a `/admin/login.php`.

## Migración a Neon (PostgreSQL)
- Cambia `DB_DRIVER=pgsql` en `.env`.
- Usa la cadena de conexión de Neon como `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` y `DB_PORT=5432`.
- Ejecuta `schema_postgres.sql`, `seed.sql` y `migration_submissions_postgres.sql` en el SQL Editor de Neon.
- Ejemplo de `.env` Neon:
```
DB_DRIVER=pgsql
DB_HOST=ep-wandering-union-b5mid66s-pooler.c-7.us-east-2.aws.neon.tech
DB_PORT=5432
DB_NAME=neondb
DB_USER=neondb_owner
DB_PASS=<contraseña>
```

## API
`GET /api/studies.php` — filtros `min`, `max`, `context`, `species`, `evidence`, `q`, `limit`, `offset`. Licencia CC-BY-4.0.

## Licencia
Contenido bajo CC-BY-4.0. No constituye recomendación veterinaria.
