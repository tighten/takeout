<?php

namespace App;

use Illuminate\Support\Str;

function underscore_slug(string $string): string
{
    return Str::slug($string, '_');
}

function title_case(string $string): string
{
    return Str::title($string);
}

function title_from_slug(string $string): string
{
    return Str::of($string)->replace('_', ' ')->title();
}
