# Sistema de Control de Ambientes - SENA

Sistema web desarrollado en Laravel para la gestión y control de ambientes de aprendizaje, fichas de formación, reservas de espacios y inventario de equipos del SENA.

## 📋 Tabla de Contenidos

- [Descripción](#descripción)
- [Características](#características)
- [Requisitos del Sistema](#requisitos-del-sistema)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Base de Datos](#base-de-datos)
- [Funcionalidades](#funcionalidades)
- [Uso de la Aplicación](#uso-de-la-aplicación)
- [Seguridad](#seguridad)
- [Desarrollo](#desarrollo)
- [Despliegue](#despliegue)
- [Troubleshooting](#troubleshooting)
- [Contribución](#contribución)
- [Licencia](#licencia)

---

## 📖 Descripción

Sistema de gestión integral diseñado para el control y administración de ambientes de aprendizaje en el SENA. Permite gestionar fichas de formación, asignar ambientes a fichas mediante reservas, mantener un inventario detallado de equipos y administrar usuarios del sistema.

### Objetivos del Sistema

- Gestionar fichas de formación con sus programas asociados
- Controlar la asignación de ambientes a fichas mediante reservas
- Mantener un inventario actualizado de equipos por ambiente
- Administrar usuarios con diferentes roles (Administrador y Usuario)
- Proporcionar un dashboard con estadísticas en tiempo real
- Exportar datos en formato CSV para análisis externos

---

## ✨ Características

### Módulos Principales

1. **Dashboard**
   - Estadísticas en tiempo real de ambientes, fichas y usuarios
   - Visualización de ambientes disponibles, ocupados y en mantenimiento
   - Contador de fichas activas e inactivas

2. **Gestión de Fichas**
   - Crear, editar y eliminar fichas de formación
   - Asociar fichas a programas de formación
   - Gestionar cantidad de aprendices (máximo 44 por ficha)
   - Controlar fechas de inicio, fin y fecha productiva
   - Validación de número de ficha (máximo 9 dígitos)
   - Exportación a CSV

3. **Gestión de Ambientes y Reservas**
   - Visualizar todas las reservas de ambientes
   - Crear nuevas reservas asignando ambientes a fichas
   - Editar reservas existentes
   - Eliminar reservas
   - Validación de solapamiento de horarios
   - Validación de capacidad máxima del ambiente
   - Control automático de estados de ambientes (Disponible/Ocupado)
   - Búsqueda por número de ambiente
   - Exportación a CSV

4. **Gestión de Inventario**
   - Registrar inventario por ambiente
   - Gestionar equipos: computadores, sillas, mesas, aire acondicionado, tablero, televisor, ventiladores, videobeam y herramientas
   - Editar y eliminar registros de inventario
   - Búsqueda avanzada

5. **Gestión de Usuarios** (Solo Administradores)
   - Crear, editar y eliminar usuarios
   - Asignar roles (Administrador/Usuario)
   - Búsqueda por nombre, apellido, correo o usuario

6. **Ajustes de Perfil**
   - Actualizar información personal (nombre, apellido, correo, teléfono)
   - Cambiar nombre de usuario
   - Cambiar contraseña
   - Validación de contraseña actual para todos los cambios

### Características Técnicas

- **Framework**: Laravel 12.x
- **Frontend**: Blade Templates + Tailwind CSS
- **Base de Datos**: SQLite (configurable para MySQL/MariaDB)
- **Autenticación**: Sistema personalizado con roles
- **Seguridad**: CSRF protection, rate limiting, headers de seguridad
- **Validaciones**: Form Requests para validación robusta
- **Responsive**: Diseño adaptable a dispositivos móviles y tablets

---

## 🔧 Requisitos del Sistema

### Requisitos Mínimos

- **PHP**: 8.2 o superior
- **Composer**: 2.x o superior
- **Node.js**: 18.x o superior (para compilar assets)
- **npm**: 9.x o superior
- **Base de Datos**: SQLite (incluido) o MySQL 5.7+ / MariaDB 10.3+

### Extensiones PHP Requeridas

- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PCRE
- PDO
- Tokenizer
- XML

### Servidor Web

- Apache con mod_rewrite habilitado
- O Nginx con configuración PHP-FPM
- O servidor de desarrollo PHP incorporado (`php artisan serve`)

---

## 🚀 Instalación

### Paso 1: Clonar o Descargar el Proyecto

```bash
# Si tienes Git
git clone <url-del-repositorio> control_ambientes
cd control_ambientes

# O simplemente descarga y extrae el proyecto en una carpeta
```

### Paso 2: Instalar Dependencias PHP

```bash
composer install
```

### Paso 3: Configurar Variables de Entorno

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Generar la clave de aplicación
php artisan key:generate
```

### Paso 4: Configurar Base de Datos

#### Opción A: SQLite (Recomendado para desarrollo)

El proyecto viene configurado por defecto para usar SQLite. Solo necesitas crear el archivo de base de datos:

```bash
touch database/database.sqlite
```

#### Opción B: MySQL/MariaDB

Edita el archivo `.env` y configura:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=control_ambientes
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

Luego crea la base de datos:

```sql
CREATE DATABASE control_ambientes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Paso 5: Ejecutar Migraciones

```bash
php artisan migrate
```

**Nota**: Si tu base de datos ya tiene tablas existentes, puedes ejecutar:

```bash
php artisan migrate --force
```

### Paso 6: Instalar Dependencias JavaScript (Opcional)

Si necesitas compilar assets frontend:

```bash
npm install
npm run build
```

### Paso 7: Configurar Permisos (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Paso 8: Iniciar el Servidor de Desarrollo

```bash
php artisan serve
```

El sistema estará disponible en: `http://localhost:8000`

---

## ⚙️ Configuración

### Archivo .env

El archivo `.env` contiene todas las configuraciones del sistema. Las más importantes son:

```env
# Aplicación
APP_NAME="Control de Ambientes"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de Datos
DB_CONNECTION=sqlite
DB_DATABASE=/ruta/completa/a/database/database.sqlite

# Para MySQL/MariaDB:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=control_ambientes
# DB_USERNAME=root
# DB_PASSWORD=

# Sesiones
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Caché
CACHE_STORE=database
```

### Configuración de Roles

El sistema utiliza dos roles principales:

- **Rol 1**: Administrador (acceso completo)
- **Rol 2**: Usuario (acceso limitado, sin gestión de usuarios)

Los roles se asignan en la tabla `users` mediante el campo `id_rol`.

### Estados de Ambientes

- **1**: Disponible
- **2**: En Mantenimiento
- **3**: Ocupado

### Estados de Reservas

- **1**: Activa
- **2**: Cancelada
- **3**: Finalizada

---

## 📁 Estructura del Proyecto

```
control_ambientes/
├── app/
│   ├── Helpers/
│   │   └── SearchHelper.php          # Helper para búsquedas seguras
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AmbientesController.php
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── FichasController.php
│   │   │   ├── InventarioController.php
│   │   │   ├── ReservasController.php
│   │   │   ├── UsersController.php
│   │   │   └── AjustesController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php   # Control de acceso admin
│   │   │   ├── ForceHttps.php        # Forzar HTTPS en producción
│   │   │   └── SecurityHeaders.php   # Headers de seguridad
│   │   └── Requests/
│   │       ├── StoreReservaRequest.php
│   │       └── UpdateReservaRequest.php
│   └── Models/
│       ├── Ambiente.php
│       ├── Ficha.php
│       ├── Inventario.php
│       ├── Programa.php
│       ├── Reserva.php
│       └── User.php
├── bootstrap/
│   └── cache/                         # Caché de bootstrap
├── config/                           # Archivos de configuración
├── database/
│   ├── migrations/                   # Migraciones de base de datos
│   └── database.sqlite               # Base de datos SQLite (si aplica)
├── public/
│   ├── images/                       # Imágenes estáticas
│   └── index.php                     # Punto de entrada público
├── resources/
│   ├── js/                           # Archivos JavaScript
│   │   ├── ambientes.js
│   │   ├── app.js
│   │   ├── fichas.js
│   │   ├── forms.js
│   │   ├── inventario.js
│   │   ├── modals.js
│   │   ├── navigation.js
│   │   ├── reservas.js
│   │   ├── search.js
│   │   ├── select-search.js
│   │   └── users.js
│   └── views/                        # Vistas Blade
│       ├── ajustes/
│       ├── ambientes/
│       ├── auth/
│       ├── components/
│       ├── dashboard.blade.php
│       ├── fichas/
│       ├── inventario/
│       ├── layouts/
│       ├── reservas/
│       ├── users/
│       └── welcome.blade.php
├── routes/
│   └── web.php                       # Rutas de la aplicación
├── storage/
│   ├── app/                          # Archivos de la aplicación
│   ├── framework/                    # Caché de framework
│   └── logs/                         # Logs de la aplicación
├── tests/                            # Tests automatizados
├── .env                              # Variables de entorno (NO versionar)
├── .env.example                      # Ejemplo de variables de entorno
├── composer.json                     # Dependencias PHP
├── package.json                      # Dependencias JavaScript
└── README.md                         # Este archivo
```

---

## 🗄️ Base de Datos

### Esquema de Tablas

#### Tabla: `users`
Usuarios del sistema.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_cedula | VARCHAR | Cédula (PK) |
| nombre | VARCHAR | Nombre del usuario |
| apellido | VARCHAR | Apellido del usuario |
| correo | VARCHAR | Correo electrónico (único) |
| telefono | VARCHAR | Teléfono (opcional) |
| user | VARCHAR | Nombre de usuario (único) |
| contraseña | VARCHAR | Contraseña hasheada |
| id_rol | INTEGER | Rol: 1=Admin, 2=Usuario |

#### Tabla: `ficha`
Fichas de formación.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_ficha | INTEGER | ID único (PK) |
| num_ficha | INTEGER | Número de ficha (máx. 9 dígitos) |
| cant_aprendices | INTEGER | Cantidad de aprendices (máx. 44) |
| id_programa | INTEGER | ID del programa (FK) |
| fecha_inicio | DATE | Fecha de inicio |
| fecha_fin | DATE | Fecha de fin |
| fecha_productiva | DATE | Fecha productiva (opcional) |

#### Tabla: `programa`
Programas de formación.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_programa | INTEGER | ID único (PK) |
| nombre_programa | VARCHAR | Nombre del programa |

#### Tabla: `ambientes`
Ambientes de aprendizaje.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_ambiente | INTEGER | ID único (PK) |
| num_ambiente | VARCHAR | Número del ambiente |
| id_estado | INTEGER | Estado: 1=Disponible, 2=Mantenimiento, 3=Ocupado |
| capacidad_max | INTEGER | Capacidad máxima (default: 35) |

#### Tabla: `reservas`
Reservas de ambientes para fichas.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_reserva | INTEGER | ID único (PK) |
| id_ambiente | INTEGER | ID del ambiente (FK) |
| id_ficha | INTEGER | ID de la ficha (FK) |
| dia_semana | VARCHAR | Día: "lunes" o "sabado" |
| hora_inicio | TIME | Hora de inicio |
| hora_fin | TIME | Hora de fin |
| fecha_inicio | DATE | Fecha de inicio del período |
| fecha_fin | DATE | Fecha de fin del período |
| id_estado_reserva | INTEGER | Estado: 1=Activa, 2=Cancelada, 3=Finalizada |
| observaciones | TEXT | Observaciones (opcional) |
| created_at | TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | Fecha de actualización |

#### Tabla: `inventario`
Inventario de equipos por ambiente.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_Inventario | INTEGER | ID único (PK) |
| id_ambiente | INTEGER | ID del ambiente (FK) |
| computadores | INTEGER | Cantidad de computadores |
| sillas | INTEGER | Cantidad de sillas |
| mesas | INTEGER | Cantidad de mesas |
| aire_acondicionado | INTEGER | Cantidad (0 o 1) |
| tablero | INTEGER | Cantidad (0 o 1) |
| televisor | INTEGER | Cantidad (0 o 1) |
| ventiladores | INTEGER | Cantidad |
| vidiovid | INTEGER | Cantidad (videobeam) |
| herramientas | INTEGER | Cantidad |

#### Tabla: `estado_reserva`
Estados de reserva (catálogo).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_estado_reserva | INTEGER | ID único (PK) |
| nombre_estado | VARCHAR | Nombre: "Activa", "Cancelada", "Finalizada" |

### Relaciones

- `Ficha` pertenece a `Programa` (belongsTo)
- `Reserva` pertenece a `Ambiente` (belongsTo)
- `Reserva` pertenece a `Ficha` (belongsTo)
- `Inventario` pertenece a `Ambiente` (relación implícita)

---

## 🎯 Funcionalidades

### 1. Autenticación y Autorización

#### Login
- Sistema de autenticación personalizado
- Rate limiting: máximo 5 intentos de login por minuto
- Bloqueo temporal después de 3 intentos fallidos (2 minutos)
- Mensajes de error genéricos (no revelan si el usuario existe)
- Validación de credenciales con hash seguro

#### Roles y Permisos
- **Administrador (id_rol = 1)**: Acceso completo a todas las funcionalidades
- **Usuario (id_rol = 2)**: Acceso limitado, sin gestión de usuarios
- Middleware `AdminMiddleware` protege rutas administrativas

### 2. Dashboard

Muestra estadísticas en tiempo real:

- **Ambientes**:
  - Total de ambientes
  - Ambientes disponibles
  - Ambientes ocupados
  - Ambientes en mantenimiento

- **Fichas**:
  - Total de fichas
  - Fichas activas (fecha_fin >= 2026-01-01 o NULL)
  - Fichas inactivas (fecha_fin < 2026-01-01)

- **Usuarios**:
  - Total de usuarios activos

### 3. Gestión de Fichas

#### Crear Ficha
- Número de ficha (máximo 9 dígitos, entero positivo)
- Cantidad de aprendices (1-44)
- Programa de formación (select)
- Fecha de inicio
- Fecha de fin (debe ser >= fecha inicio)
- Fecha productiva (opcional, entre fecha inicio y fin)

#### Validaciones
- Número de ficha: requerido, entero, mínimo 1, máximo 999999999
- Cantidad de aprendices: requerido, entero, mínimo 1, máximo 44
- Programa: debe existir en la base de datos
- Fechas: formato válido y lógica de negocio

#### Editar/Eliminar
- Edición con validaciones completas
- Eliminación con confirmación modal
- Exportación a CSV con todos los datos

### 4. Gestión de Ambientes y Reservas

#### Visualizar Reservas
- Listado paginado (10 por página)
- Búsqueda por número de ambiente
- Información completa: ambiente, ficha, día, horario, fechas, estado
- Estados visuales con badges de colores

#### Crear Reserva
- Selección de ambiente (ordenado numéricamente)
- Selección de ficha
- Día de la semana: Lunes a Viernes o Sábados
- Horario: hora inicio y hora fin
- Período: fecha inicio y fecha fin
- Observaciones (opcional, máx. 500 caracteres)

#### Validaciones de Negocio
- **Capacidad**: La cantidad de aprendices de la ficha no puede exceder la capacidad máxima del ambiente
- **Solapamiento**: No puede haber dos reservas activas en el mismo ambiente, mismo día y horario solapado
- **Horarios**: La hora de fin debe ser posterior a la hora de inicio
- **Fechas**: La fecha de fin debe ser >= fecha de inicio

#### Actualización Automática de Estados
- Al crear una reserva activa, el ambiente se marca como "Ocupado"
- Al eliminar o finalizar todas las reservas activas, el ambiente vuelve a "Disponible"

#### Editar/Eliminar Reservas
- Edición con todas las validaciones
- Cambio de estado de reserva (Activa/Cancelada/Finalizada)
- Eliminación con confirmación modal

### 5. Gestión de Inventario

#### Crear Registro de Inventario
- Selección de ambiente
- Registro de equipos:
  - Computadores (cantidad)
  - Sillas (cantidad)
  - Mesas (cantidad)
  - Aire acondicionado (Sí/No)
  - Tablero (Sí/No)
  - Televisor (Sí/No)
  - Ventiladores (cantidad)
  - Videobeam (cantidad)
  - Herramientas (cantidad)

#### Búsqueda
- Por número de ambiente
- Por cantidad de equipos específicos

### 6. Gestión de Usuarios (Solo Administradores)

#### Crear Usuario
- Cédula (única, string)
- Nombre y apellido
- Correo electrónico (único)
- Teléfono (opcional)
- Nombre de usuario (único)
- Contraseña
- Rol (Administrador/Usuario)

#### Validaciones
- Cédula: requerida, única
- Nombre/Apellido: solo letras y espacios, máximo 25 caracteres
- Correo: formato válido, único
- Usuario: único, alfanumérico
- Contraseña: mínimo 8 caracteres

### 7. Ajustes de Perfil

Todos los cambios requieren confirmación de contraseña actual.

#### Actualizar Información Personal
- Nombre
- Apellido
- Correo electrónico
- Teléfono

#### Cambiar Credenciales
- Nombre de usuario
- Contraseña (con confirmación)

---

## 💻 Uso de la Aplicación

### Primer Acceso

1. Accede a la URL del sistema (ej: `http://localhost:8000`)
2. Serás redirigido al login
3. Ingresa tus credenciales (deben estar creadas previamente en la base de datos)
4. Si eres administrador, tendrás acceso completo; si eres usuario, tendrás acceso limitado

### Navegación

- **Dashboard**: Vista principal con estadísticas
- **Fichas**: Gestión de fichas de formación
- **Ambientes**: Visualización y gestión de reservas
- **Inventario**: Gestión de equipos por ambiente
- **Usuarios**: Solo administradores
- **Ajustes**: Configuración de perfil personal

### Flujo de Trabajo Típico

1. **Crear Fichas**: Ir a "Fichas" → "Crear Nueva Ficha"
2. **Asignar Ambiente**: Ir a "Ambientes" → "Asignar Ambiente" → Seleccionar ficha y ambiente
3. **Registrar Inventario**: Ir a "Inventario" → "Crear Registro" → Seleccionar ambiente y equipos
4. **Consultar Dashboard**: Ver estadísticas en tiempo real

### Exportación de Datos

- **Fichas**: Botón "Exportar" en la página de fichas
- **Reservas**: Botón "Exportar" en la página de ambientes
- Los archivos CSV se descargan automáticamente con formato: `nombre_modulo_YYYY-MM-DD_HHMMSS.csv`

---

## 🔒 Seguridad

### Medidas Implementadas

1. **Protección CSRF**
   - Todos los formularios incluyen tokens CSRF
   - Validación automática por Laravel

2. **Rate Limiting**
   - Login: 5 intentos por minuto
   - Creación/Edición: 20 requests por minuto
   - Eliminación: 10 requests por minuto
   - Gestión de usuarios: 10 requests por minuto (crear/editar), 5 (eliminar)

3. **Headers de Seguridad**
   - `X-Content-Type-Options: nosniff`
   - `X-Frame-Options: SAMEORIGIN`
   - `X-XSS-Protection: 1; mode=block`
   - `Referrer-Policy: strict-origin-when-cross-origin`
   - `Permissions-Policy`: Restricción de geolocalización, micrófono y cámara

4. **Protección contra SQL Injection**
   - Uso exclusivo de Eloquent ORM y Query Builder
   - Escapado de caracteres especiales en búsquedas LIKE (`SearchHelper`)
   - Validación de parámetros en todas las consultas

5. **Autenticación Segura**
   - Contraseñas hasheadas con bcrypt (12 rounds)
   - Bloqueo temporal después de intentos fallidos
   - Sesiones seguras con cookies HTTP-only

6. **HTTPS Forzado** (en producción)
   - Middleware `ForceHttps` redirige HTTP a HTTPS

7. **Validación de Entrada**
   - Form Requests para validación robusta
   - Sanitización de inputs
   - Validación de tipos de datos

### Recomendaciones de Seguridad

- **Producción**: Cambiar `APP_DEBUG=false` en `.env`
- **Base de Datos**: Usar credenciales fuertes
- **Sesiones**: Configurar `SESSION_SECURE_COOKIE=true` en producción
- **Backups**: Realizar backups regulares de la base de datos
- **Actualizaciones**: Mantener Laravel y dependencias actualizadas

---

## 🛠️ Desarrollo

### Estructura de Código

El proyecto sigue las convenciones de Laravel:

- **Controllers**: Lógica de negocio y coordinación
- **Models**: Representación de datos y relaciones
- **Requests**: Validación de formularios
- **Middleware**: Lógica transversal (autenticación, seguridad)
- **Views**: Presentación con Blade templates
- **Helpers**: Funciones auxiliares reutilizables

### Agregar Nuevas Funcionalidades

1. **Crear Modelo** (si es necesario):
   ```bash
   php artisan make:model NombreModelo
   ```

2. **Crear Migración**:
   ```bash
   php artisan make:migration create_nombre_tabla_table
   ```

3. **Crear Controller**:
   ```bash
   php artisan make:controller NombreController
   ```

4. **Crear Form Request** (si es necesario):
   ```bash
   php artisan make:request StoreNombreRequest
   ```

5. **Definir Rutas** en `routes/web.php`

6. **Crear Vistas** en `resources/views/`

### Compilar Assets Frontend

```bash
# Desarrollo (con hot reload)
npm run dev

# Producción (minificado)
npm run build
```

### Comandos Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ver rutas
php artisan route:list

# Tinker (consola interactiva)
php artisan tinker
```

### Logs

Los logs se encuentran en `storage/logs/laravel.log`. Para verlos en tiempo real:

```bash
tail -f storage/logs/laravel.log
```

---

## 🚀 Despliegue

### Requisitos de Producción

- PHP 8.2+ con extensiones requeridas
- Composer instalado globalmente
- Base de datos configurada (MySQL/MariaDB recomendado)
- Servidor web (Apache/Nginx)
- SSL/TLS certificado (recomendado)

### Pasos para Despliegue

1. **Clonar el proyecto en el servidor**

2. **Instalar dependencias**:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Configurar `.env`**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tu-dominio.com
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=control_ambientes
   DB_USERNAME=usuario_db
   DB_PASSWORD=contraseña_segura
   
   SESSION_SECURE_COOKIE=true
   ```

4. **Generar clave de aplicación**:
   ```bash
   php artisan key:generate
   ```

5. **Ejecutar migraciones**:
   ```bash
   php artisan migrate --force
   ```

6. **Optimizar**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

7. **Configurar permisos**:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

8. **Configurar servidor web**:

   **Apache** (`.htaccess` ya incluido):
   - Asegúrate de que `mod_rewrite` esté habilitado
   - El DocumentRoot debe apuntar a la carpeta `public`

   **Nginx** (ejemplo de configuración):
   ```nginx
   server {
       listen 80;
       server_name tu-dominio.com;
       root /ruta/al/proyecto/public;
       
       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";
       
       index index.php;
       
       charset utf-8;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }
       
       error_page 404 /index.php;
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }
       
       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

9. **Configurar SSL** (Let's Encrypt recomendado):
   ```bash
   sudo certbot --nginx -d tu-dominio.com
   ```

### Backup de Base de Datos

**SQLite**:
```bash
cp database/database.sqlite backups/database_$(date +%Y%m%d_%H%M%S).sqlite
```

**MySQL/MariaDB**:
```bash
mysqldump -u usuario -p control_ambientes > backups/backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## 🔧 Troubleshooting

### Problemas Comunes

#### Error: "No application encryption key has been specified"
```bash
php artisan key:generate
```

#### Error: "SQLSTATE[HY000] [2002] No such file or directory"
- Verifica la configuración de base de datos en `.env`
- Asegúrate de que el servidor de base de datos esté corriendo

#### Error: "Permission denied" en storage o cache
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Error 404 en todas las rutas
- Verifica que `mod_rewrite` esté habilitado (Apache)
- Verifica la configuración de Nginx
- Asegúrate de que el DocumentRoot apunte a `public`

#### Error: "Class 'X' not found"
```bash
composer dump-autoload
php artisan config:clear
```

#### Problemas con sesiones
- Verifica permisos en `storage/framework/sessions`
- Verifica configuración de `SESSION_DRIVER` en `.env`

#### Error al eliminar reservas (404)
- Asegúrate de que los assets JavaScript estén compilados
- Verifica que el formulario tenga el atributo `data-base-url`

### Logs y Debugging

- **Logs de Laravel**: `storage/logs/laravel.log`
- **Logs del servidor web**: `/var/log/apache2/` o `/var/log/nginx/`
- **Debug mode**: Activar `APP_DEBUG=true` en `.env` (solo desarrollo)

### Obtener Ayuda

1. Revisa los logs en `storage/logs/laravel.log`
2. Verifica la configuración en `.env`
3. Consulta la documentación oficial de Laravel: https://laravel.com/docs
4. Revisa los issues conocidos en el repositorio

---

## 🤝 Contribución

### Cómo Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/NuevaFuncionalidad`)
3. Commit tus cambios (`git commit -m 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/NuevaFuncionalidad`)
5. Abre un Pull Request

### Estándares de Código

- Seguir PSR-12 para código PHP
- Usar nombres descriptivos para variables y funciones
- Comentar código complejo
- Mantener funciones pequeñas y enfocadas
- Escribir tests para nuevas funcionalidades

### Reportar Bugs

Al reportar un bug, incluye:
- Descripción clara del problema
- Pasos para reproducirlo
- Comportamiento esperado vs. comportamiento actual
- Versión de PHP, Laravel y sistema operativo
- Logs relevantes (sin información sensible)

---

## 📄 Licencia

Este proyecto es software propietario desarrollado para el SENA. Todos los derechos reservados.

---

## 📞 Contacto y Soporte

Para soporte técnico o consultas:
- Revisa la documentación completa
- Consulta los logs del sistema
- Contacta al equipo de desarrollo

---

## 📝 Changelog

### Versión Actual: 1.0.0

#### Características Implementadas
- Sistema de autenticación con roles
- Gestión completa de fichas
- Gestión de ambientes y reservas
- Sistema de inventario
- Dashboard con estadísticas
- Exportación a CSV
- Interfaz responsive
- Validaciones robustas
- Seguridad implementada

#### Mejoras Futuras Planificadas
- Sistema de recuperación de contraseña
- Verificación de email
- API REST
- Sistema de logs de auditoría
- Notificaciones por email
- Reportes avanzados
- Calendario visual de reservas

---

**Última actualización**: Enero 2026

**Versión del documento**: 1.0.0
