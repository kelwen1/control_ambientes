# Changelog

Todos los cambios notables en este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.0.0] - 2026-01-27

### Agregado
- Sistema de autenticación completo con roles (Administrador/Usuario)
- Gestión de fichas de formación con validaciones completas
- Sistema de reservas de ambientes con validación de solapamiento
- Gestión de inventario por ambiente
- Dashboard con estadísticas en tiempo real
- Sistema de usuarios con roles y permisos
- Página de ajustes de perfil personal
- Exportación a CSV para fichas y reservas
- Búsqueda avanzada en todos los módulos
- Sistema de paginación en listados
- Modales de confirmación para acciones destructivas
- Validaciones robustas con Form Requests
- Middleware de seguridad (CSRF, headers, HTTPS)
- Rate limiting en rutas críticas
- Protección contra SQL injection
- Interfaz responsive con Tailwind CSS
- Sistema de mensajes de éxito/error
- Bloqueo temporal después de intentos fallidos de login
- Actualización automática de estados de ambientes
- Validación de capacidad máxima de ambientes
- Validación de solapamiento de horarios en reservas

### Seguridad
- Implementación de tokens CSRF en todos los formularios
- Headers de seguridad (X-Frame-Options, X-Content-Type-Options, etc.)
- Rate limiting en rutas de autenticación y operaciones críticas
- Protección contra SQL injection mediante Eloquent y Query Builder
- Escapado de caracteres especiales en búsquedas LIKE
- Contraseñas hasheadas con bcrypt (12 rounds)
- Middleware de administrador para proteger rutas sensibles
- Validación de entrada en todos los formularios
- Sanitización de inputs

### Mejorado
- Optimización de consultas con eager loading
- Mejora en la experiencia de usuario con feedback visual
- Validaciones más robustas y mensajes de error personalizados
- Interfaz más intuitiva y moderna

### Corregido
- Error 404 al eliminar reservas (corregido URL base en JavaScript)
- Validación de número de ficha (máximo 9 dígitos implementado)
- Problemas de compatibilidad con diferentes navegadores
- Manejo de errores en operaciones de base de datos

### Documentación
- README.md completo con toda la información del proyecto
- CONTRIBUTING.md con guías de contribución
- CHANGELOG.md para historial de cambios
- Comentarios en código crítico

---

## [Unreleased]

### Planificado
- Sistema de recuperación de contraseña por email
- Verificación de email para nuevos usuarios
- API REST para integraciones externas
- Sistema de logs de auditoría
- Notificaciones por email para eventos importantes
- Reportes avanzados con gráficos
- Calendario visual de reservas
- Sistema de backup automático
- Tests unitarios y de integración
- Optimización de performance con caché
- Sistema de notificaciones en tiempo real
- Exportación a PDF
- Filtros avanzados en búsquedas
- Historial de cambios en registros
- Sistema de comentarios en reservas

---

## Tipos de Cambios

- **Agregado**: Para nuevas funcionalidades
- **Cambiado**: Para cambios en funcionalidades existentes
- **Deprecado**: Para funcionalidades que pronto serán eliminadas
- **Eliminado**: Para funcionalidades eliminadas
- **Corregido**: Para correcciones de bugs
- **Seguridad**: Para vulnerabilidades de seguridad

---

## Formato de Versiones

Este proyecto usa [Semantic Versioning](https://semver.org/lang/es/):
- **MAJOR**: Cambios incompatibles en la API
- **MINOR**: Nueva funcionalidad compatible hacia atrás
- **PATCH**: Correcciones de bugs compatibles hacia atrás

Ejemplo: `1.2.3` = Major.Minor.Patch
