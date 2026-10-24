# Publicar TEZAN

Guía paso a paso para poner la tienda en internet. Marca cada casilla al completarla.

## 1. Antes de publicar (en tu computadora)

- [ ] **Cambiar la contraseña del admin.** Hoy es `admin1234` (el panel muestra una alerta roja mientras siga así):
  ```bash
  php artisan tinker
  >>> User::where('email', 'admin@tezan.com')->first()->update(['password' => 'UnaClaveLargaYSegura']);
  ```
- [ ] **Contraseña de aplicación de Gmail** para `tezancompany@gmail.com`:
  1. Activar la verificación en 2 pasos en la cuenta de Google.
  2. Entrar a https://myaccount.google.com/apppasswords y crear una para "TEZAN".
  3. Guardar las 16 letras: van en `MAIL_PASSWORD`.
  4. Para probar en local: en `.env` poner `MAIL_MAILER=smtp` y `MAIL_PASSWORD=...`, luego `php artisan config:clear` y hacer una compra de prueba.
- [ ] **Revisión legal** de `/terminos-y-condiciones` y `/politica-de-privacidad` (son borradores).
- [ ] **Catálogo real**: prendas con fotos propias, tallas, colores (con su código de color) y stock.
- [ ] **Dirección completa** (con distrito y ciudad) en `TIENDA_DIRECCION`.
- [ ] Opcional: redes sociales (`TIENDA_INSTAGRAM`, `TIENDA_FACEBOOK`, `TIENDA_TIKTOK`) y política de cambios.

## 2. Contratar hosting y dominio

Necesitas un servidor con **PHP 8.3+** (con extensiones `gd`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`), **MySQL 8**, **Composer** y **Node 20+** (o compilar los assets en tu PC y subirlos).

Opciones habituales:
- **Laravel Cloud** o **Laravel Forge + VPS** (DigitalOcean, Hetzner, Vultr): lo más sencillo para Laravel.
- **Hosting compartido** con acceso SSH y PHP 8.3 (Hostinger, etc.): más barato; el dominio debe apuntar a la carpeta `public/`.

Dominio: `.pe` en https://punto.pe o cualquier registrador. Activar **SSL (https)**; la mayoría de hostings lo dan gratis con Let's Encrypt.

## 3. Instalar en el servidor

```bash
git clone <tu-repositorio> tezan && cd tezan
composer install --no-dev --optimize-autoloader
cp .env.production.example .env        # y completar los <...>
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=InitialStoreSeeder --force   # solo la primera vez (tallas, colores, categorías, admin)
php artisan storage:link
npm ci && npm run build
php artisan images:optimize
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Si el seeder crea el admin en producción, **cámbiale la contraseña de inmediato** (paso 1).

Las fotos de productos viven en `storage/app/public/products`. Si ya cargaste prendas en local, copia esa carpeta al servidor y exporta/importa la base de datos (phpMyAdmin o `mysqldump`).

## 4. Tareas automáticas

- **Copias de seguridad diarias** de la base de datos y de `storage/app/public` (la mayoría de paneles lo ofrece; si no, un cron con `mysqldump`).
- Si en el futuro se usan colas: `php artisan queue:work` como servicio. Hoy los correos se envían al momento y no hace falta.

## 5. Después de publicar

- [ ] Abrir la tienda en el celular y hacer **una compra real de prueba** con Yape: revisar que llegue el correo, validar el pago en el admin y que llegue el correo de pago confirmado.
- [ ] Probar "¿Olvidaste tu contraseña?" con una cuenta de cliente.
- [ ] Registrar una hoja de prueba en el **Libro de Reclamaciones** y responderla desde el admin.
- [ ] Dar de alta el sitio en **Google Search Console** y enviar `https://<tu-dominio>/sitemap.xml`.
- [ ] Probar un enlace de producto en WhatsApp para ver la vista previa.

## Cada vez que actualices la tienda

```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
