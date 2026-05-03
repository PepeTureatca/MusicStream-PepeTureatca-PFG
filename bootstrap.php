<?php
/**
 * Carga el archivo .env de la raíz del proyecto y registra cada variable
 * con putenv(), $_ENV y $_SERVER para que getenv() funcione en todo el código.
 *
 * Solo se ejecuta una vez gracias a la variable estática.
 */
(static function (): void {
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $envFile = __DIR__ . '/.env';
    if (!is_file($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        // Ignorar comentarios y líneas vacías
        if ($line === '' || $line[0] === '#') {
            continue;
        }

        // Debe tener al menos un '='
        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);

        // Quitar comillas simples o dobles envolventes
        if (
            strlen($value) >= 2
            && (($value[0] === '"'  && $value[-1] === '"')
             || ($value[0] === "'"  && $value[-1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        // No sobreescribir variables ya definidas en el entorno del servidor
        if (getenv($name) === false) {
            putenv("$name=$value");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
})();
