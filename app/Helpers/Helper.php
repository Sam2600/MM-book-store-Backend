<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait Helper
{
    public function logException(\Throwable $th)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? null;

        $class = $trace['class'] ?? 'unknown class';
        $function = $trace['function'] ?? 'unknown function';
        $line = $trace['line'] ?? 'unknown line';
        $file = $trace['file'] ?? 'unknown file';

        Log::error($th->getMessage() . " in {$file} at line {$line} within {$class}::{$function}");
    }

    public function getDefaultDisk(): String
    {
        return config("filesystems.default");
    }

    public function deleteFile(String $disk, String $path): void
    {
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function checkAndCreateDirectory(String $disk, String $path): void
    {
        if (!Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->makeDirectory($path, 0777, true, true);
        }
    }
}
