# Guía de Contribución

Gracias por tu interés en contribuir al Sistema de Control de Ambientes. Esta guía te ayudará a entender cómo puedes contribuir de manera efectiva.

## 📋 Tabla de Contenidos

- [Código de Conducta](#código-de-conducta)
- [Cómo Contribuir](#cómo-contribuir)
- [Estándares de Código](#estándares-de-código)
- [Proceso de Desarrollo](#proceso-de-desarrollo)
- [Reportar Bugs](#reportar-bugs)
- [Sugerir Mejoras](#sugerir-mejoras)
- [Preguntas](#preguntas)

---

## 📜 Código de Conducta

### Nuestro Compromiso

Nos comprometemos a mantener un ambiente abierto y acogedor para todos, independientemente de edad, tamaño corporal, discapacidad, etnia, identidad y expresión de género, nivel de experiencia, nacionalidad, apariencia personal, raza, religión o identidad y orientación sexual.

### Comportamiento Esperado

- Usar lenguaje acogedor e inclusivo
- Respetar diferentes puntos de vista y experiencias
- Aceptar críticas constructivas con gracia
- Enfocarse en lo que es mejor para la comunidad
- Mostrar empatía hacia otros miembros de la comunidad

### Comportamiento Inaceptable

- Uso de lenguaje o imágenes sexualizadas
- Comentarios despectivos, insultos o ataques personales
- Acoso público o privado
- Publicar información privada de otros sin permiso
- Otras conductas que razonablemente podrían considerarse inapropiadas en un entorno profesional

---

## 🤝 Cómo Contribuir

### Tipos de Contribuciones

Aceptamos diferentes tipos de contribuciones:

1. **Reportar Bugs**: Encuentra y reporta problemas
2. **Sugerir Mejoras**: Propón nuevas funcionalidades
3. **Escribir Código**: Implementa nuevas características o corrige bugs
4. **Mejorar Documentación**: Ayuda a mejorar la documentación
5. **Revisar Código**: Revisa pull requests de otros contribuidores

### Proceso de Contribución

1. **Fork el Repositorio**
   ```bash
   git clone https://github.com/tu-usuario/control_ambientes.git
   cd control_ambientes
   ```

2. **Crea una Rama**
   ```bash
   git checkout -b feature/nombre-de-tu-feature
   # o
   git checkout -b fix/descripcion-del-bug
   ```

3. **Haz tus Cambios**
   - Sigue los estándares de código
   - Escribe código limpio y comentado
   - Agrega tests si es posible

4. **Commit tus Cambios**
   ```bash
   git add .
   git commit -m "Descripción clara y concisa de los cambios"
   ```

5. **Push a tu Fork**
   ```bash
   git push origin feature/nombre-de-tu-feature
   ```

6. **Abre un Pull Request**
   - Describe claramente los cambios
   - Menciona issues relacionados si los hay
   - Espera feedback y revisión

---

## 📝 Estándares de Código

### PHP (PSR-12)

- Usar espacios en lugar de tabs (4 espacios)
- Líneas máximo 120 caracteres
- Nombres de clases en PascalCase
- Nombres de métodos y variables en camelCase
- Constantes en UPPER_SNAKE_CASE

**Ejemplo**:
```php
<?php

namespace App\Http\Controllers;

class EjemploController extends Controller
{
    public function ejemploMetodo(string $parametro): array
    {
        $variableEjemplo = 'valor';
        const CONSTANTE_EJEMPLO = 'valor';
        
        return [];
    }
}
```

### JavaScript

- Usar 2 espacios para indentación
- Punto y coma al final de las declaraciones
- Comillas simples para strings
- Nombres descriptivos y en camelCase

**Ejemplo**:
```javascript
function ejemploFuncion(parametro) {
    const variableEjemplo = 'valor';
    return variableEjemplo;
}
```

### Convenciones de Nombres

- **Controladores**: `NombreController.php` (ej: `UsersController.php`)
- **Modelos**: Singular, PascalCase (ej: `User.php`, `Ficha.php`)
- **Vistas**: snake_case, en carpetas (ej: `users/index.blade.php`)
- **Rutas**: kebab-case (ej: `/fichas/create`)
- **Variables**: camelCase (ej: `$totalAmbientes`)

### Comentarios

- Comentar código complejo o no obvio
- Usar PHPDoc para clases y métodos públicos
- Mantener comentarios actualizados

**Ejemplo**:
```php
/**
 * Calcula el total de ambientes disponibles.
 *
 * @param int $totalAmbientes Total de ambientes
 * @param int $ocupados Ambientes ocupados
 * @param int $mantenimiento Ambientes en mantenimiento
 * @return int Ambientes disponibles
 */
public function calcularDisponibles(int $totalAmbientes, int $ocupados, int $mantenimiento): int
{
    return max(0, $totalAmbientes - $ocupados - $mantenimiento);
}
```

---

## 🔄 Proceso de Desarrollo

### Antes de Empezar

1. Revisa los issues existentes
2. Asegúrate de que tu idea no esté ya implementada
3. Si trabajas en un issue, comenta que lo estás haciendo

### Durante el Desarrollo

1. **Escribe Código Limpio**
   - Funciones pequeñas y enfocadas
   - Evita duplicación (DRY)
   - Nombres descriptivos

2. **Prueba tu Código**
   - Prueba manualmente todas las funcionalidades
   - Verifica que no rompas funcionalidades existentes
   - Prueba casos edge

3. **Mantén Commits Atómicos**
   - Un commit por cambio lógico
   - Mensajes descriptivos
   - No mezcles cambios no relacionados

### Mensajes de Commit

Usa el formato convencional:

```
tipo(alcance): descripción breve

Descripción más detallada si es necesario

Fixes #123
```

**Tipos**:
- `feat`: Nueva funcionalidad
- `fix`: Corrección de bug
- `docs`: Cambios en documentación
- `style`: Formato, punto y coma faltante, etc.
- `refactor`: Refactorización de código
- `test`: Agregar o corregir tests
- `chore`: Cambios en build, dependencias, etc.

**Ejemplos**:
```
feat(fichas): agregar validación de máximo 9 dígitos para número de ficha

fix(reservas): corregir error 404 al eliminar reservas

docs(readme): actualizar sección de instalación
```

---

## 🐛 Reportar Bugs

### Antes de Reportar

1. Verifica que el bug no haya sido reportado ya
2. Intenta reproducirlo en la última versión
3. Revisa la documentación

### Información a Incluir

**Título**: Descripción clara y concisa del problema

**Descripción**:
- Qué esperabas que pasara
- Qué pasó realmente
- Pasos para reproducir el problema
- Comportamiento esperado vs. actual

**Entorno**:
- Versión de PHP
- Versión de Laravel
- Sistema operativo
- Navegador (si aplica)
- Versión de base de datos

**Logs**:
- Errores relevantes de `storage/logs/laravel.log`
- Mensajes de error del navegador (si aplica)

**Screenshots**:
- Si es un problema visual, incluye capturas

**Ejemplo de Reporte**:

```
**Título**: Error 404 al eliminar reservas

**Descripción**:
Al intentar eliminar una reserva desde la página de ambientes, se genera un error 404.

**Pasos para Reproducir**:
1. Ir a /ambientes
2. Hacer clic en "Eliminar" en cualquier reserva
3. Confirmar eliminación en el modal
4. Se muestra error 404

**Comportamiento Esperado**:
La reserva debería eliminarse y mostrar mensaje de éxito.

**Entorno**:
- PHP 8.2.0
- Laravel 12.0
- Windows 10
- Chrome 120

**Logs**:
[Incluir logs relevantes]
```

---

## 💡 Sugerir Mejoras

### Antes de Sugerir

1. Verifica que la mejora no esté ya sugerida
2. Piensa en el caso de uso y beneficios
3. Considera la complejidad de implementación

### Información a Incluir

**Título**: Descripción clara de la mejora

**Descripción**:
- Qué problema resuelve
- Cómo funcionaría
- Beneficios para los usuarios
- Alternativas consideradas

**Ejemplo**:

```
**Título**: Agregar sistema de recuperación de contraseña

**Descripción**:
Actualmente los usuarios no pueden recuperar su contraseña si la olvidan. 
Sería útil implementar un sistema de recuperación por email.

**Funcionamiento Propuesto**:
1. Usuario hace clic en "Olvidé mi contraseña"
2. Ingresa su correo electrónico
3. Recibe un enlace por email
4. Puede restablecer su contraseña

**Beneficios**:
- Reduce carga administrativa
- Mejora experiencia de usuario
- Estándar en aplicaciones web

**Alternativas Consideradas**:
- Recuperación por teléfono (más complejo)
- Preguntas de seguridad (menos seguro)
```

---

## ❓ Preguntas

Si tienes preguntas sobre:
- Cómo usar alguna funcionalidad → Revisa el README.md
- Cómo implementar algo → Abre un issue con la etiqueta "question"
- Problemas técnicos → Abre un issue con la etiqueta "bug"

---

## ✅ Checklist para Pull Requests

Antes de enviar tu PR, asegúrate de:

- [ ] El código sigue los estándares del proyecto
- [ ] Los cambios están probados manualmente
- [ ] No hay errores de sintaxis o linting
- [ ] La documentación está actualizada si es necesario
- [ ] Los commits tienen mensajes descriptivos
- [ ] El PR tiene una descripción clara
- [ ] Se mencionan issues relacionados si los hay
- [ ] No hay conflictos con la rama principal

---

## 🎯 Prioridades Actuales

Si buscas en qué trabajar, estas son las áreas prioritarias:

1. **Testing**: Agregar tests unitarios y de integración
2. **Documentación**: Mejorar documentación técnica
3. **Performance**: Optimizar consultas y agregar caché
4. **Seguridad**: Auditoría de seguridad y mejoras
5. **UX**: Mejoras en la experiencia de usuario

---

Gracias por contribuir al proyecto! 🎉
