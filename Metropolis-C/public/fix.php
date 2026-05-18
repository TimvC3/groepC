<?php
$root = '/home/matthijs/htdocs/groepc.hosting.atd.avans.nl';
echo "<pre>";
echo shell_exec("cd {$root} && composer dump-autoload 2>&1");
echo shell_exec("cd {$root} && php artisan cache:clear 2>&1");
echo shell_exec("cd {$root} && php artisan config:clear 2>&1");
echo shell_exec("cd {$root} && php artisan migrate:fresh --seed 2>&1");
echo "</pre>";