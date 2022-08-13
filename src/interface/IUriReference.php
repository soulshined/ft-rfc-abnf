<?php
namespace FT\RFC_ABNF\Interface;

use FT\RFC_ABNF\ABNF;

interface IUriReference {
    public static function URI_REFERENCE() : ABNF;
    public static function ABSOLUTE_URI() : ABNF;
    public static function PATH_ABSOLUTE() : ABNF;
    public static function RELATIVE_PART() : ABNF;
    public static function AUTHORITY() : ABNF;
    public static function HOST() : ABNF;
    public static function PORT() : ABNF;
    public static function PATH_ABEMPTY() : ABNF;
    public static function SEGMENT() : ABNF;
    public static function QUERY() : ABNF;
}
?>