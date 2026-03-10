# Documentación Técnica

Documentación detallada para desarrolladores sobre la arquitectura, diseño y decisiones técnicas del Sistema de Control de Ambientes.

## 📋 Tabla de Contenidos

- [Arquitectura](#arquitectura)
- [Flujo de Datos](#flujo-de-datos)
- [Estructura de Base de Datos](#estructura-de-base-de-datos)
- [Modelos y Relaciones](#modelos-y-relaciones)
- [Controladores](#controladores)
- [Middleware](#middleware)
- [Validaciones](#validaciones)
- [Frontend](#frontend)
- [Seguridad](#seguridad)
- [Performance](#performance)
- [Decisiones de Diseño](#decisiones-de-diseño)

---

## 🏗️ Arquitectura

### Patrón Arquitectónico

El proyecto sigue el patrón **MVC (Model-View-Controller)** de Laravel:

```
Request → Route → Middleware → Controller → Model → Database
                                    ↓
                                  View ← Data
```

### Capas de la Aplicación

1. **Capa de Presentación (Views)**
   - Blade templates en `resources/views/`
   - JavaScript modular en `resources/js/`
   - Estilos con Tailwind CSS

2. **Capa de Lógica de Negocio (Controllers)**
   - Controladores en `app/Http/Controllers/`
   - Form Requests para validación en `app/Http/Requests/`
   - Helpers en `app/Helpers/`

3. **Capa de Datos (Models)**
   - Modelos Eloquent en `app/Models/`
   - Migraciones en `database/migrations/`

4. **Capa de Seguridad (Middleware)**
   - Middleware en `app/Http/Middleware/`
   - Protección de rutas y validación de permisos

---

## 🔄 Flujo de Datos

### Flujo de una Petición Típica

```
1. Usuario hace petición HTTP
   ↓
2. Route (routes/web.php) identifica la ruta
   ↓
3. Middleware ejecuta:
   - AuthMiddleware: Verifica autenticación
   - ForceHttps: Redirige a HTTPS (producción)
   - SecurityHeaders: Agrega headers de seguridad
   ↓
4. Controller procesa la petición:
   - Valida datos (Form Request)
   - Ejecuta lógica de negocio
   - Interactúa con Modelos
   ↓
5. Model consulta/actualiza base de datos
   ↓
6. Controller prepara datos para la vista
   ↓
7. View renderiza con Blade
   ↓
8. Response HTTP enviada al cliente
```

### Ejemplo: Crear una Reserva

```
POST /reservas
   ↓
Route: reservas.store → ReservasController@store
   ↓
Middleware: auth, force.https, throttle:20,1
   ↓
StoreReservaRequest valida:
   - id_ambiente: required|integer|exists
   - id_ficha: required|integer|exists
   - dia_semana: required|in:lunes,sabado,domingo (sábado/domingo: solo horario 7:00-17:00)
   - hora_inicio: required|date_format:H:i
   - hora_fin: required|date_format:H:i|after:hora_inicio
   - fecha_inicio: required|date
   - fecha_fin: required|date|after_or_equal:fecha_inicio
   ↓
ReservasController@store:
   1. Valida capacidad del ambiente
   2. Valida solapamiento de horarios
   3. Crea la reserva (Reserva::create)
   4. Actualiza estado del ambiente
   ↓
Redirect a ambientes.index con mensaje de éxito
```

---

## 🗄️ Estructura de Base de Datos

### Diagrama de Relaciones

```
users (id_cedula PK)
  └── (no relaciones directas en código)

programa (id_programa PK)
  └── ficha (id_programa FK)

ambientes (id_ambiente PK)
  ├── reservas (id_ambiente FK)
  └── inventario (id_ambiente FK)

ficha (id_ficha PK)
  ├── programa (id_programa FK)
  └── reservas (id_ficha FK)

reservas (id_reserva PK)
  ├── ambientes (id_ambiente FK)
  ├── ficha (id_ficha FK)
  └── estado_reserva (id_estado_reserva FK)

inventario (id_Inventario PK)
  └── ambientes (id_ambiente FK)
```

### Índices y Optimizaciones

**Índices Clave**:
- `users.user` (único) - Búsqueda rápida en login
- `users.correo` (único) - Validación de unicidad
- `ficha.num_ficha` - Búsqueda de fichas
- `ambientes.num_ambiente` - Búsqueda de ambientes
- `reservas.id_ambiente` - Join eficiente
- `reservas.id_ficha` - Join eficiente
- `reservas.dia_semana` - Filtrado de reservas

**Consultas Optimizadas**:
- Uso de `with()` para eager loading y evitar N+1
- Índices en foreign keys para joins rápidos
- Paginación para grandes volúmenes de datos

---

## 📦 Modelos y Relaciones

### User Model

```php
class User extends Authenticatable
{
    protected $primaryKey = 'id_cedula';
    protected $keyType = 'string';
    public $incrementing = false;
    
    // Relaciones: Ninguna definida (usuarios independientes)
    
    // Métodos importantes:
    - isAdmin(): bool
    - isUser(): bool
    - getAuthPassword(): string
}
```

### Ficha Model

```php
class Ficha extends Model
{
    protected $table = 'ficha';
    protected $primaryKey = 'id_ficha';
    
    // Relaciones:
    - programa(): BelongsTo (Programa)
    
    // Validaciones en Controller:
    - num_ficha: max 9 dígitos
    - cant_aprendices: 1-44
}
```

### Reserva Model

```php
class Reserva extends Model
{
    protected $table = 'reservas';
    protected $primaryKey = 'id_reserva';
    public $timestamps = true;
    
    // Relaciones:
    - ambiente(): BelongsTo (Ambiente)
    - ficha(): BelongsTo (Ficha)
    
    // Lógica de negocio en Controller:
    - Validación de solapamiento
    - Actualización de estado de ambiente
}
```

### Ambiente Model

```php
class Ambiente extends Model
{
    protected $table = 'ambientes';
    public $timestamps = false;
    
    // Estados:
    // 1 = Disponible
    // 2 = Mantenimiento
    // 3 = Ocupado
    
    // Lógica de negocio:
    - Estado actualizado automáticamente por ReservasController
}
```

---

## 🎮 Controladores

### Estructura de un Controlador Típico

```php
class EjemploController extends Controller
{
    // 1. Métodos de visualización
    public function index(Request $request) { }
    public function create() { }
    public function edit($id) { }
    
    // 2. Métodos de acción
    public function store(Request $request) { }
    public function update(Request $request, $id) { }
    public function destroy($id) { }
    
    // 3. Métodos auxiliares
    public function export(Request $request) { }
    private function metodoPrivado() { }
}
```

### ReservasController - Lógica Compleja

**Método `store()`**:

```php
1. Validación con StoreReservaRequest
2. Validar capacidad máxima del ambiente
3. Validar solapamiento de horarios:
   - Buscar reservas activas (id_estado_reserva = 1)
   - Mismo ambiente y día
   - Horarios solapados: inicio1 < fin2 AND fin1 > inicio2
4. Crear reserva
5. Actualizar estado del ambiente (ocupado si es activa)
```

**Método `actualizarEstadoAmbiente()`**:

```php
1. Verificar si hay reservas activas para el ambiente
2. Si hay reservas activas → Estado = Ocupado (3)
3. Si no hay reservas activas → Estado = Disponible (1)
```

### DashboardController - Agregaciones

```php
- Total ambientes: Ambiente::count()
- Ambientes ocupados: WHERE id_estado = 3
- Ambientes en mantenimiento: WHERE id_estado = 2
- Ambientes disponibles: Total - Ocupados - Mantenimiento
- Usuarios activos: User::count()
- Fichas activas: WHERE fecha_fin >= '2026-01-01' OR fecha_fin IS NULL
- Fichas inactivas: WHERE fecha_fin < '2026-01-01'
```

---

## 🛡️ Middleware

### AdminMiddleware

```php
Flujo:
1. Verificar autenticación (auth()->check())
2. Verificar si es administrador (auth()->user()->isAdmin())
3. Si no es admin → Redirect a dashboard con error
4. Si es admin → Continuar
```

**Registro**: En `bootstrap/app.php` o `app/Http/Kernel.php`

### ForceHttps

```php
Flujo:
1. Verificar si APP_ENV = production
2. Verificar si request no es HTTPS
3. Redirigir a versión HTTPS
```

### SecurityHeaders

```php
Headers agregados:
- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy: geolocation=(), microphone=(), camera=()
```

---

## ✅ Validaciones

### Form Requests

**StoreReservaRequest**:

```php
authorize(): bool
  - Verifica que usuario esté autenticado
  - Verifica que tenga rol válido (1 o 2)

rules(): array
  - Validaciones de formato y tipos
  - Validaciones de existencia (exists:tabla,campo)
  - Validaciones personalizadas (closures)

messages(): array
  - Mensajes de error personalizados en español
```

**Validaciones Personalizadas**:

```php
// En hora_fin:
function ($attribute, $value, $fail) {
    if ($horaFin <= $horaInicio) {
        $fail('La hora de fin debe ser posterior a la hora de inicio.');
    }
}
```

### Validaciones en Controllers

**Capacidad Máxima**:
```php
if ($ficha->cant_aprendices > $ambiente->capacidad_max) {
    return redirect()->back()->with('error', '...');
}
```

**Solapamiento**:
```php
$conflictos = DB::table('reservas')
    ->where('id_ambiente', $request->id_ambiente)
    ->where('dia_semana', $request->dia_semana)
    ->where('id_estado_reserva', 1)
    ->where(function($query) use ($request) {
        $query->where('hora_inicio', '<', $request->hora_fin)
              ->where('hora_fin', '>', $request->hora_inicio);
    })
    ->exists();
```

---

## 🎨 Frontend

### Estructura JavaScript

```
resources/js/
├── app.js              # Entry point, inicializa módulos
├── bootstrap.js        # Configuración de Axios
├── modals.js           # Funciones genéricas de modales
├── forms.js            # Validaciones de formularios
├── navigation.js       # Transiciones de navegación
├── search.js           # Funcionalidad de búsqueda
├── select-search.js    # Select con búsqueda
├── ambientes.js        # Lógica específica de ambientes
├── fichas.js           # Lógica específica de fichas
├── inventario.js       # Lógica específica de inventario
├── reservas.js         # Lógica específica de reservas
└── users.js            # Lógica específica de usuarios
```

### Flujo de JavaScript

```javascript
1. app.js carga al iniciar
2. Importa módulos necesarios
3. Inicializa listeners cuando DOM está listo
4. Cada módulo específico sobrescribe funciones globales si es necesario
```

### Ejemplo: Eliminación de Reservas

```javascript
// En ambientes.js
window.openDeleteModal = function(id) {
    const form = document.getElementById('deleteForm');
    const baseUrl = form.dataset.baseUrl; // Desde Blade: data-base-url
    form.action = `${baseUrl}/${id}`;
    // Mostrar modal...
};

// En Blade
<form id="deleteForm" data-base-url="{{ url('/reservas') }}">
    @csrf
    @method('DELETE')
</form>
```

### Tailwind CSS

- Framework CSS utility-first
- Configuración en `<script>` inline en `layouts/app.blade.php`
- Clases responsive: `sm:`, `md:`, `lg:`
- Colores personalizados: `#39B54A` (verde SENA)

---

## 🔒 Seguridad

### Protección CSRF

```php
// En Blade
@csrf  // Genera: <input type="hidden" name="_token" value="...">

// En Laravel (automático)
VerifyCsrfToken middleware valida todos los POST/PUT/DELETE
```

### Rate Limiting

```php
// En routes/web.php
Route::post('/login', ...)->middleware('throttle:5,1');
// 5 requests por minuto

Route::post('/fichas', ...)->middleware('throttle:20,1');
// 20 requests por minuto
```

### Protección SQL Injection

```php
// ✅ CORRECTO
DB::table('users')->where('user', $username)->first();
User::where('user', $username)->first();

// ✅ CORRECTO (con escapado)
$search = SearchHelper::escapeLikeSpecialChars($search);
DB::table('users')->where('nombre', 'like', '%' . $search . '%');

// ❌ INCORRECTO (nunca hacer esto)
DB::raw("SELECT * FROM users WHERE user = '$username'");
```

### Autenticación

```php
// Login
Auth::attempt(['user' => $username, 'password' => $password]);

// Verificar autenticación
auth()->check();

// Obtener usuario actual
auth()->user();

// Verificar rol
auth()->user()->isAdmin();
```

---

## ⚡ Performance

### Optimizaciones Implementadas

1. **Eager Loading**:
```php
$fichas = Ficha::with('programa')->get(); // Evita N+1
```

2. **Paginación**:
```php
$reservas = $query->paginate(10); // Solo carga 10 registros
```

3. **Índices en Base de Datos**:
- Foreign keys automáticamente indexadas
- Índices únicos en campos críticos

4. **Caché de Configuración** (producción):
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Consultas Optimizadas

**Ejemplo: Dashboard**:
```php
// Una consulta por métrica (podría optimizarse con una sola consulta)
$totalAmbientes = Ambiente::count();
$ambientesOcupados = DB::table('ambientes')->where('id_estado', 3)->count();
```

**Mejora Potencial**:
```php
$estadisticas = DB::table('ambientes')
    ->selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN id_estado = 3 THEN 1 ELSE 0 END) as ocupados,
        SUM(CASE WHEN id_estado = 2 THEN 1 ELSE 0 END) as mantenimiento
    ')
    ->first();
```

---

## 🎯 Decisiones de Diseño

### Por qué SQLite por defecto

- **Ventajas**:
  - No requiere servidor de base de datos separado
  - Fácil para desarrollo y testing
  - Archivo único, fácil de respaldar
  - Suficiente para proyectos pequeños/medianos

- **Desventajas**:
  - Limitado para alta concurrencia
  - Sin usuarios/permisos avanzados

**Recomendación**: Usar MySQL/MariaDB en producción.

### Por qué Query Builder en lugar de Eloquent

En algunos lugares se usa `DB::table()` en lugar de Eloquent:

**Razones**:
- Consultas complejas con múltiples joins
- Mejor control sobre SELECT específicos
- Performance ligeramente mejor en consultas complejas

**Ejemplo**:
```php
// Query Builder (usado en AmbientesController)
DB::table('reservas')
    ->leftJoin('ambientes', ...)
    ->leftJoin('ficha', ...)
    ->select('reservas.*', 'ambientes.num_ambiente', ...)
    ->get();

// vs Eloquent (más verboso para este caso)
Reserva::with(['ambiente', 'ficha'])
    ->select('reservas.*')
    ->get();
```

### Por qué Form Requests separados

**Ventajas**:
- Validación reutilizable
- Autorización centralizada
- Mensajes personalizados
- Código más limpio en controllers

**Ejemplo**:
```php
// En lugar de validar en el controller:
public function store(Request $request) {
    $request->validate([...]); // ❌ Mezcla responsabilidades
}

// Usar Form Request:
public function store(StoreReservaRequest $request) {
    // Validación ya hecha ✅
}
```

### Por qué JavaScript Modular

**Ventajas**:
- Código reutilizable
- Fácil mantenimiento
- Separación de responsabilidades
- Posibilidad de tree-shaking

**Estructura**:
```javascript
// modals.js - Funciones genéricas
export function openDeleteModal(...) { }

// ambientes.js - Específico de ambientes
import { openDeleteModal } from './modals';
window.openDeleteModal = function(id) {
    openDeleteModal(id, null, baseUrl);
};
```

---

## 📊 Métricas y Monitoreo

### Logs Importantes

- **Errores**: `storage/logs/laravel.log`
- **Autenticación**: Logs de intentos fallidos
- **Operaciones críticas**: Logs en controllers (ReservasController, UsersController)

### Métricas a Monitorear

1. **Performance**:
   - Tiempo de respuesta de consultas
   - Uso de memoria
   - Tiempo de carga de páginas

2. **Seguridad**:
   - Intentos fallidos de login
   - Rate limiting activado
   - Errores de validación

3. **Uso**:
   - Número de reservas creadas
   - Usuarios activos
   - Ambientes más utilizados

---

## 🔧 Herramientas de Desarrollo

### Comandos Útiles

```bash
# Desarrollo
php artisan serve
php artisan tinker
php artisan route:list

# Debugging
php artisan log:clear
tail -f storage/logs/laravel.log

# Optimización
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Base de datos
php artisan migrate
php artisan migrate:status
php artisan db:seed
```

### Debugging

**Tinker**:
```php
php artisan tinker
>>> $user = User::first();
>>> $user->nombre;
>>> Reserva::with('ambiente', 'ficha')->first();
```

**Logging**:
```php
Log::info('Mensaje informativo');
Log::error('Error: ' . $e->getMessage());
Log::debug('Debug: ', ['data' => $data]);
```

---

**Última actualización**: Enero 2026
