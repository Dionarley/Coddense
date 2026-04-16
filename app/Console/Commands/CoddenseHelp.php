<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CoddenseHelp extends Command
{
    protected $signature = 'coddense:help';

    protected $description = 'Display Coddense help information';

    public function handle(): int
    {
        $this->info('Coddense - Mapeamento de Repositórios PHP');
        $this->line('');
        $this->info('Comandos disponíveis:');
        $this->line('');
        $this->line('  php artisan serve              Iniciar servidor de desenvolvimento');
        $this->line('  php artisan queue:work         Iniciar worker de filas');
        $this->line('  php artisan coddense:help      Mostrar esta ajuda');
        $this->line('');
        $this->info('URLs úteis:');
        $this->line('  http://localhost:8000          Dashboard');
        $this->line('  http://localhost:8000/api/repositories  API');
        $this->line('');

        return Command::SUCCESS;
    }
}
