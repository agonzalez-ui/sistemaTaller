# SIMRH

Sistema web para la administración de un taller de motocicletas. Permite gestionar clientes, motocicletas, repuestos, inventario, órdenes de trabajo, facturación, usuarios, permisos, bitácoras y reportes.

## Requisitos

- PHP 8.3 o superior.
- Composer.
- Node.js y npm.
- MySQL.

## Instalación local

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Configure previamente en `.env` la conexión con la base de datos y el servicio de correo.

## Pruebas

```bash
php artisan test
```

## Producción

Las instrucciones específicas para publicar y actualizar el sistema están disponibles en `HOSTINGER.md`. Los procedimientos de respaldo y recuperación están documentados en `RESPALDOS.md`.
