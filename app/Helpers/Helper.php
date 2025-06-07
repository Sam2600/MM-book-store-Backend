<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

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
}
