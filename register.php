<?php

declare(strict_types=1);

(static function (): void {
    foreach ([__DIR__ . '/../../autoload.php', __DIR__ . '/vendor/autoload.php'] as $autoload) {
        if (is_file($autoload)) {
            require_once $autoload;

            break;
        }
    }

    Envless\Env\Env::load();
})();
