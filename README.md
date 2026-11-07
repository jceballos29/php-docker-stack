# Stack PHP + Nginx + MySQL con Docker

Entorno de desarrollo reproducible para aprender PHP.

## Requisitos
- Docker y Docker Compose
- Git

## Puesta en marcha
```bash
git clone https://github.com/jceballos29/php-docker-stack.git php-docker-stack
cd php-docker-stack
cp .env.example .env      # edita las contraseñas
docker compose up -d --build
```

- App: http://localhost:8080
- Adminer (BD): http://localhost:8081  (servidor: `db`)

## Comandos útiles
| Acción | Comando |
|--------|---------|
| Ver logs | `docker compose logs -f` |
| Shell en PHP | `docker compose exec php sh` |
| Composer | `docker compose exec php composer <comando>` |
| Detener | `docker compose down` |
| Reiniciar BD desde cero | `docker compose down -v` |

## Xdebug
Pon `XDEBUG_MODE=debug` en `.env`, ejecuta `docker compose up -d` y arranca
"Escuchar Xdebug (Docker)" en VS Code.

## Estructura
```
.
├── docker/
│   ├── nginx/default.conf   # Servidor web: sirve /public y envía .php a PHP-FPM
│   └── php/
│       ├── Dockerfile       # PHP 8.4-FPM + extensiones + Composer + Xdebug
│       ├── php.ini          # Ajustes de PHP para desarrollo
│       └── xdebug.ini       # Configuración de Xdebug (modo vía XDEBUG_MODE)
├── src/                     # Código de la aplicación (montado en /var/www/html)
│   ├── public/index.php     # Punto de entrada web (document root de Nginx)
│   ├── info.php             # Script CLI: versión de PHP y sistema
│   └── db-check.php         # Script CLI: comprueba la conexión a MySQL
├── .vscode/launch.json      # Depuración con Xdebug en VS Code
├── .env.example             # Plantilla de variables de entorno (copiar a .env)
├── docker-compose.yml       # Servicios: php, nginx, db (MySQL 8.4) y adminer
└── .editorconfig            # Estilo de código común entre editores
```

Los scripts CLI se ejecutan dentro del contenedor:
```bash
docker compose exec php php info.php
docker compose exec php php db-check.php
```

Los puertos de Nginx y Adminer solo se publican en `127.0.0.1`, por lo que no
son accesibles desde otros equipos de tu red.

## Solución de problemas

### Los archivos creados desde el contenedor pertenecen a otro usuario
Si `composer` o PHP crean archivos que no puedes editar, el UID/GID del
contenedor no coincide con el tuyo. Ejecuta `id -u` e `id -g`, ajusta
`HOST_UID` y `HOST_GID` en `.env` y reconstruye la imagen:
```bash
docker compose up -d --build
```

### Xdebug no conecta con VS Code
- Verifica `XDEBUG_MODE=debug` en `.env` y recrea los contenedores con
  `docker compose up -d`.
- Comprueba que "Escuchar Xdebug (Docker)" está activo (puerto 9003) y que el
  firewall permite conexiones entrantes a ese puerto.
- En Linux, `host.docker.internal` funciona gracias a `extra_hosts` con
  `host-gateway` en `docker-compose.yml`.

### Error de conexión a la base de datos o puerto ocupado
- `php` espera a que MySQL esté sano (`healthcheck`); el primer arranque puede
  tardar unos segundos. Revisa con `docker compose ps` y `docker compose logs db`.
- Si cambias las credenciales en `.env` después del primer arranque, MySQL
  conserva las anteriores: reinicia la BD con `docker compose down -v`
  (**borra los datos**).
- Si `8080` u `8081` están ocupados, cambia `APP_PORT` o `ADMINER_PORT` en `.env`.
