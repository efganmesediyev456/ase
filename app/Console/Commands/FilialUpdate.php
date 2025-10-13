<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Filial;

class FilialUpdate extends Command
{
    protected $signature = 'update:filial';
    protected $description = 'Update filials array in config/filial.php';

    public function handle()
    {
        $filials = Filial::where('deleted_at', null)->get();

        $filialArray = [];
        foreach ($filials as $filial) {
            $filialArray[] = ['value' => $filial->fid.' - '.$filial->type_id, 'text' => $filial->name.' - '.$filial->address.' - '.$filial->type_id];
        }

        $configContent = "<?php\n\nreturn [\n    'filials' => '" . json_encode($filialArray, JSON_UNESCAPED_UNICODE) . "'\n];\n";

        File::put(config_path('filial.php'), $configContent);

        $this->info('Filial config file updated successfully.');
    }
}
