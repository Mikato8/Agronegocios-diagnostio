# Arquitectura

## Capas

```text
HTTP
 │
 ├─ public/index.php            front controller
 ├─ app/Core/App.php             arranque, CSRF, router, errores
 ├─ app/Core/Router.php          rutas declarativas
 ├─ app/Controllers             validación, autorización, respuestas
 ├─ app/Repositories            SQL parametrizado
 ├─ app/Services                reglas de verificación
 ├─ app/Support                 PDF, cálculos financieros, helpers
 ├─ app/Views                   PHP + layouts
 └─ app/Core/Database           PDO o HTTP Neon
```

No hay ORM ni Composer. El autoloader de `app/bootstrap.php` resuelve `App\` contra `app/`.

## Flujo de una petición

1. `public/index.php` carga `app/bootstrap.php`.
2. `App::run()` captura `Request`, valida CSRF en métodos no GET, carga `routes/web.php` y despacha.
3. El controlador valida, consulta repositorios y renderiza una vista con `View::render`.
4. `Response::send()` aplica cabeceras de seguridad y envía HTML, JSON, PDF o un archivo.

## Transporte de datos

`PdoDatabase` convierte los marcadores `$1, $2, …` a marcadores nombrados de PDO, conserva el orden y castea tipos. `HttpDatabase` usa `POST /sql` de Neon con la cadena de conexión en la cabecera `Neon-Connection-String`; se usa cuando no hay `pdo_pgsql` o cuando `DB_DRIVER=http`.

El driver se elige en `Database::configure`:

- `pdo`: fuerza PDO.
- `http`: fuerza HTTPS.
- `auto`: intenta PDO y cae a HTTPS si falla.

## Contenido y estados

Los tres tipos (`cases`, `reports`, `opportunities`) comparten estados:

```text
borrador → en_revision → publicado
                │
                ├→ cambios_solicitados → en_revision
                └→ rechazado
```

- El autor puede editar en `borrador`, `cambios_solicitados` y `rechazado`.
- Un editor o administrador puede editar y revisar cualquier estado.
- Publicar asigna `published_at`; cualquier otro estado lo limpia.
- Cada decisión se registra en `reviews` y `audit_log`.

## Verificación de estudiantes

`StudentVerifier` recibe el correo y la universidad seleccionada:

1. Busca el dominio en `universities.email_domains`.
2. Si no está y el dominio termina en `.edu.mx`, verifica automáticamente.
3. Si no, deja la cuenta en `pendiente` con método `manual`.
4. El administrador puede verificarla manualmente o suspenderla.

La constancia de estudios se guarda en `storage/constancias/{user_id}/` y se sirve sólo desde `/admin/constancias/...` a administradores autenticados.

## Cálculos financieros

`Financials::ratios` calcula:

- Utilidad bruta y de operación.
- Márgenes bruto, operativo y neto (porcentajes).
- Razón circulante (veces).
- Pasivo/activo (porcentaje).
- ROE y ROA (porcentajes).
- Gastos/ingresos (porcentaje).

Sólo calcula cuando existen las cifras necesarias; no imputa valores faltantes. `Financials::health` aplica criterios orientativos para el semáforo.

## Seguridad

- CSRF en todos los POST; token regenerado al iniciar sesión.
- `password_hash` / `password_verify`; rehash automático al iniciar sesión.
- Límite de intentos por correo/IP en `login_attempts`.
- Cookies `HttpOnly`, `SameSite=Lax`, `Secure` cuando `APP_URL` es HTTPS.
- Uploads: MIME real con `finfo`, dimensiones, tamaño, nombres aleatorios y `.htaccess` que bloquea PHP.
- Consultas parametrizadas; nunca concatenación de entrada del usuario.
- Escapado de salida con `e()` en todas las vistas.
- Cabeceras CSP, `X-Content-Type-Options`, `Referrer-Policy` y HSTS en HTTPS.

## Decisiones

- **Sin cédula:** el dominio profesional no aplica a estudiantes. La verificación es académica y privada.
- **Tres tipos de contenido:** comparten interacción (comentarios, reacciones, favoritos) mediante `target_type`/`target_id`, pero tienen tablas propias por sus campos distintos.
- **Folios legibles:** secuencias PostgreSQL por tipo (`CAS-2026-0001`, `REP-…`, `OPO-…`).
- **PDF propio:** `SimplePdf` evita dependencias y funciona en hostings compartidos.
- **Contenido demostrativo aislado:** `is_demo = true` permite regenerarlo o retirarlo sin tocar contenido real.
