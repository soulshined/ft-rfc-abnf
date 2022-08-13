<?php

namespace FT\RFC_ABNF\Utils;

use Exception;
use FT\RFC_ABNF\ABNF;
use InvalidArgumentException;

function build_chrs_for_unicode_range(string $begin, string $end): array
{
    $chrs = [];

    if (
        $begin === null || $end === null ||
        !str_starts_with($begin, '\x') || !str_starts_with($end, '\x')
    )
        throw new InvalidArgumentException("Args must start with '\x'");

    $begin = hexdec(str_replace('\x', '', $begin));
    $end = hexdec(str_replace('\x', '', $end));

    if ($end <= $begin) return [];

    for ($i = $begin; $i <= $end; $i++) {
        $chr = mb_chr($i);

        if ($chr === false)
            throw new Exception("Can not get character for code '$i'");

        $chrs[] = $chr;
    }

    return $chrs;
}

function unitochr(string $uni): string
{
    return mb_chr(@hexdec($uni));
}

function build_or_regexp(ABNF|string ...$vals)
{
    return '(?:' .
        join('|', array_map(fn ($i) => $i instanceof ABNF ? $i->regexp : $i, $vals)) .
        ')';
}

function unique_abnf_chrs(array | ABNF | string ...$values): array
{
    $chrs = [];
    foreach ($values as $value) {
        if (is_array($value)) {
            foreach ($value as $v) {
                if (!is_string($v))
                    throw new Exception('Strings only allowed got ' . join($v));

                array_push($chrs, ...str_split($v));
            }
        }
        else if ($value instanceof ABNF)
            array_push($chrs, ...$value->chars);
        else array_push($chrs, ...str_split($value));
    }
    return array_unique($chrs);
}

function array_find(array $array, callable $callback)
{
    foreach ($array as $value) {
        if (call_user_func($callback, $value)) return $value;
    }

    return null;
}
