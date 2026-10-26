# Despliegue en Hostinger

Este documento describe el despliegue real de **AgroNegocios MX** en el plan Business de Hostinger, con la base PostgreSQL en Neon.

## 1. Arquitectura del despliegue

```text
orchid-echidna-504216.hostingersite.com
   │ HTTPS
   ▼
~/domains/orchid-echidna-504216.hostingersite.com/public_html/
   ├── .htaccess              redirige a public/ y bloquea rutas sensibles
   ├── public/                front controller, assets y uploads
   ├── app/                   código, fuera del alcance directo
   ├── config/
   ├── database/
   ├── storage/               logs, sesiones, caché y constancias privadas
   └── .env                   credenciales, permisos 600
          │
          └─ pdo_pgsql (TLS 5432) ──► Neon PostgreSQL
```

El documento raíz del subdominio es `public_html`. El `.htaccess` raíz redirige las peticiones a `public/` y bloquea `app/`, `config/`, `database/`, `storage/`, `bin/` y `.env`.

## 2. Requisitos del plan

- PHP 8.3 (Hostinger lo tiene).
- Extensiones: `pdo_pgsql`, `curl`, `mbstring`, `fileinfo`, `gd`, `intl`, `zip` (verificadas en el servidor).
- Acceso SSH por el puerto 65002.
- SSL activo para el subdominio.

## 3. Base de datos en Neon

La base es `neondb` en el proyecto `ep-curly-sound-b4oc1jz4`. Para que libpq de Hostinger encuentre la rama correcta, la cadena debe incluir el endpoint:

```dotenv
DATABASE_URL="postgresql://USUARIO:CLAVE@ep-curly-sound-b4oc1jz4.c-6.us-east-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require&options=endpoint%3Dep-curly-sound-b4oc1jz4"
```

Notas:

- Usa el host **directo** (sin `-pooler`) y el endpoint **sin** `-pooler` en `options`. Si mezclas `-pooler` en el host con el endpoint pelado, Neon rechaza la conexión con `Inconsistent project name inferred from SNI`.
- `%3D` es el signo `=` codificado.
- `DB_DRIVER=pdo` fuerza TCP. El plan tiene `pdo_pgsql`.
- Si en el futuro se usa un hostings sin `pdo_pgsql`, `DB_DRIVER=http` utiliza el endpoint SQL sobre HTTPS de Neon (más lento, sin transacciones interactivas).

## 4. Migraciones y seeds

Se ejecutan **desde local** o por SSH, nunca desde el navegador. Las variables de entorno tienen prioridad sobre `.env`.

```bash
export DATABASE_URL='postgresql://...'
export DB_DRIVER=pdo
php bin/migrate.php
php bin/seed.php            # catálogos + contenido demostrativo
# o: php bin/seed.php --no-demo
php bin/create-admin.php \
  --name="Comité Editorial" \
  --email="admin@agronegocios.mx" \
  --password="UnaClaveLargaYUnica" \
  --state=CMX
php bin/check.php
```

Si ya hay contenido demostrativo y quieres regenerarlo:

```bash
php bin/seed.php --force
```

`--force` elimina sólo registros con `is_demo = true`; no toca contenido real.

## 5. Paquete de despliegue

```bash
tar czf /tmp/agronegocios.tar.gz \
  --exclude='.env' \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  --exclude='storage/sessions/*' \
  --exclude='storage/constancias/*' \
  --exclude='public/uploads/casos/*' \
  --exclude='public/uploads/archivos/*' \
  --exclude='public/uploads/avatares/*' \
  -C agronegocios-portal .
```

Subir por SSH:

```bash
scp -P 65002 /tmp/agronegocios.tar.gz u771496034@45.93.101.150:~/agronegocios.tar.gz
```

En el servidor:

```bash
cd ~/domains/orchid-echidna-504216.hostingersite.com
mv public_html public_html.bak-$(date +%Y%m%d-%H%M)   # respaldo
mkdir public_html
tar xzf ~/agronegocios.tar.gz -C public_html
rm ~/agronegocios.tar.gz
```

## 6. `.env` de producción

Crear en `public_html/.env`, nunca dentro de `public/`:

```dotenv
APP_NAME="AgroNegocios MX"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://orchid-echidna-504216.hostingersite.com
APP_TIMEZONE=America/Mexico_City
APP_LOCALE=es_MX
APP_KEY=base64:...

DATABASE_URL="postgresql://USUARIO:CLAVE@ep-curly-sound-b4oc1jz4.c-6.us-east-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require&options=endpoint%3Dep-curly-sound-b4oc1jz4"
DATABASE_URL_UNPOOLED="..."
DB_DRIVER=pdo

SESSION_NAME=agronegocios_session
SESSION_LIFETIME=7200
SESSION_SECURE=true

UPLOAD_MAX_BYTES=8388608
UPLOAD_MAX_FILES=4
UPLOAD_MAX_DOCUMENT_BYTES=12582912

MAIL_ENABLED=false
MAIL_FROM=no-reply@agronegocios.mx
MAIL_FROM_NAME="AgroNegocios MX"
AUTO_VERIFY_USERS=false
```

```bash
chmod 600 .env
chmod -R u+rwX,go+rX storage public/uploads
find storage public/uploads -type d -exec chmod 775 {} \;
```

## 7. Verificación

```bash
php bin/check.php
curl -I https://orchid-echidna-504216.hostingersite.com/
curl -s https://orchid-echidna-504216.hostingersite.com/salud | head
```

Comprobar en el navegador:

- Portada, `/casos`, `/reportes`, `/oportunidades`, `/universidades`.
- Registro con correo `.edu.mx` y con correo personal.
- Inicio de sesión, panel, perfil.
- Crear un borrador de caso y enviarlo a revisión.
- Panel editorial: publicar, solicitar cambios, verificar cuenta.
- Descarga PDF de un caso y de un reporte.

## 8. Correo

`MAIL_ENABLED=false` evita dependencias de correo en la demo. Para activar recuperación de contraseña:

1. Crea un buzón `no-reply@tu-dominio.mx` en hPanel.
2. Cambia `MAIL_ENABLED=true` y `MAIL_FROM`.
3. Prueba el flujo `/recuperar`.

Si el correo no está configurado, el enlace de recuperación se registra en `storage/logs` para diagnóstico y el usuario ve un mensaje genérico. También existe `php bin/reset-password.php --email=...`.

## 9. Respaldos

- Base: `pg_dump "$DATABASE_URL" > respaldo.sql` desde local.
- Archivos: `public/uploads/` y `storage/constancias/`.
- No respaldes `.env` en repositorios ni lo compartas.

## 10. Notas de seguridad

- Rota la contraseña de Neon después de la puesta en marcha.
- Rota la contraseña del administrador y elimina las cuentas demostrativas si el sitio es público.
- Mantén `APP_DEBUG=false`.
- Revisa que `public/uploads/.htaccess` bloquee la ejecución de PHP.
- No uses `--delete` en `rsync` sin respaldo.
