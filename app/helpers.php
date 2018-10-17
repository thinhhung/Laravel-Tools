<?php

use Illuminate\Support\Str;

if (! function_exists('str_beautifier')) {
    /**
     * Make the string beautifier.
     *
     * @param  string  $subject
     * @return string
     */
    function str_beautifier($subject)
    {
        return Str::title(preg_replace('/_+/', ' ', Str::snake($subject)));
    }
}
