<?php

declare(strict_types=1);

/**
 * Escapa texto para mostrarlo de forma segura en HTML (prevención de XSS).
 */
function e(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Comprueba la conexión a la base de datos.
 *
 * @return array{ok: bool, mensaje: string}
 */
function comprobarBaseDeDatos(): array
{
    $host     = getenv('DB_HOST') ?: 'db';
    $puerto   = getenv('DB_PORT') ?: '3306';
    $base     = getenv('DB_DATABASE') ?: '';
    $usuario  = getenv('DB_USERNAME') ?: '';
    $clave    = getenv('DB_PASSWORD') ?: '';

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $puerto, $base);

    try {
        $pdo = new PDO($dsn, $usuario, $clave, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
        ]);

        $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();

        return ['ok' => true, 'mensaje' => "Conectado a MySQL $version"];
    } catch (PDOException $excepcion) {
        // No mostramos el mensaje técnico completo en pantalla: lo registramos.
        error_log('Fallo de conexión a la BD: ' . $excepcion->getMessage());

        return ['ok' => false, 'mensaje' => 'No se pudo conectar a la base de datos'];
    }
}

$requeridas = ['mbstring', 'intl', 'pdo_mysql', 'zip', 'opcache'];
$bd = comprobarBaseDeDatos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stack PHP en Docker</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 2rem auto; padding: 0 1rem; }
        .ok { color: #15803d; } .fail { color: #b91c1c; }
        li { margin: .25rem 0; }
    </style>
</head>
<body>
    <h1>Stack PHP en Docker</h1>

    <p>PHP <strong><?= e(PHP_VERSION) ?></strong> · SAPI: <?= e(PHP_SAPI) ?> · Entorno: <?= e((string) getenv('APP_ENV')) ?></p>

    <h2>Extensiones</h2>
    <ul>
        <?php foreach ($requeridas as $extension): ?>
            <?php $cargada = extension_loaded($extension); ?>
            <li class="<?= $cargada ? 'ok' : 'fail' ?>">
                <?= $cargada ? '✔' : '✘' ?> <?= e($extension) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Base de datos</h2>
    <p class="<?= $bd['ok'] ? 'ok' : 'fail' ?>"><?= e($bd['mensaje']) ?></p>
</body>
</html>
