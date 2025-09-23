<?php

namespace App\Http\Actions;

class HousekeepingAction
{
    /**
     * Garante a existência do diretório.
     */
    public function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    /**
     * Limpa ARQUIVOS do diretório público informado.
     *
     * @param string $dir Caminho absoluto (ex.: public_path('customers'))
     * @param bool   $preserveGitignore Mantém .gitignore se existir
     * @param bool   $removeSubdirs     Se true, remove subpastas recursivamente
     */
    public function cleanPublicFolder(string $dir, bool $preserveGitignore = true, bool $removeSubdirs = false): void
    {
        $this->ensureDir($dir);

        foreach (glob($dir.'/*') as $path) {
            if (is_file($path)) {
                if ($preserveGitignore && basename($path) === '.gitignore') {
                    continue;
                }
                @unlink($path);
                continue;
            }

            if ($removeSubdirs && is_dir($path)) {
                $this->rrmdir($path);
            }
        }
    }

    /**
     * Remove diretório recursivamente.
     */
    public function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir.DIRECTORY_SEPARATOR.$item;
            is_dir($path) ? $this->rrmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
