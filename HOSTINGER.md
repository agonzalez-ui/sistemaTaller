# Publicación de SIMRH en Hostinger

## Configuración del ambiente

Cree el archivo `.env` directamente en Hostinger. No suba el archivo local ni publique contraseñas en Git.

```env
APP_NAME=SIMRH
APP_ENV=production
APP_DEBUG=false
APP_URL=https://simrh.crealabcr.com
APP_TIMEZONE=America/Costa_Rica
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
PUBLIC_REGISTRATION=false
```

Complete además las variables `DB_*` y `MAIL_*` con las credenciales del hosting.

## Actualización del proyecto

La raíz del sitio debe apuntar a la carpeta `public`. No suba el archivo `public/hot`; ese archivo solo existe mientras Vite se ejecuta localmente.

Después de actualizar los archivos, ejecute desde la carpeta donde se encuentra `artisan`:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Hostinger puede bloquear `proc_open`; por eso no es necesario ejecutar `php artisan about` ni `php artisan optimize`. El mecanismo de respaldo automático se revisará por separado.

## Comprobaciones

- Abra `/up` y confirme una respuesta satisfactoria.
- Inicie sesión con una cuenta administradora existente.
- Confirme que `/auth/register` responde con página no encontrada.
- Registre un dato de prueba y confirme que la hora corresponde a Costa Rica.
- Pruebe el envío de verificación y recuperación de contraseña.
- Revise que `APP_DEBUG` permanezca desactivado.
