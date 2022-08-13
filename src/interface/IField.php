<?php

namespace FT\RFC_ABNF\Interface;

use FT\RFC_ABNF\ABNF;

interface IField {
    public static function FIELD_NAME() : ABNF;
    public static function FIELD_VALUE() : ABNF;
    public static function FIELD_VCHAR() : ABNF;
    public static function FIELD_CONTENT() : ABNF;
}

?>