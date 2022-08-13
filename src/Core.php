<?php
namespace FT\RFC_ABNF;

use function FT\RFC_ABNF\Utils\{
    build_chrs_for_unicode_range,
    unitochr
};

abstract class Core {
    public static function ALPHA() : ABNF {
        return new ABNF("ALPHA", "%x41-5A / %x61-7A", "A-Z or a-z",
            build_chrs_for_unicode_range('\x41', '\x5A'),
            '[\x41-\x5A]'
        );
    }

    public static function BIT() : ABNF {
        return new ABNF("BIT", '"0" / "1"', "zero or one", '01', '[01]');
    }

    public static function CHAR() : ABNF {
        return new ABNF("CHAR", "%x01-7F", "any 7-bit US-ASCII character excluding NUL",
            build_chrs_for_unicode_range('\x01', '\x7F'),
            '[\x01-\x7F]');
    }

    public static function CR() : ABNF {
        return new ABNF("CR", "%x0D", "carriage return", '\r', '[\x0D]');
    }

    public static function CRLF() : ABNF {
        return new ABNF( "CRLF", "CR LF", "Internet standard newline",
            "\r\n",
            '\x0D\x0A'
        );
    }

    public static function CTL() : ABNF {
        return new ABNF( "CTL", "%x00-1F / %x7F", "controls",
            build_chrs_for_unicode_range('\x00', '\x1F'),
            "[\x00-\x1F\x7F]"
        );
    }

    public static function DIGIT() : ABNF {
        return new ABNF("DIGIT", '%x30-39', '0-9',
            '0123456789',
            '[\x30-\x39]'
        );
    }

    public static function DQUOTE() : ABNF {
        return new ABNF("DQUOTE", '%x22', 'Double Quote (")', '"', '[\x22]');
    }

    public static function HEXDIG() : ABNF {
        return new ABNF("HEXDIG", "DIGIT / 'A' / 'B' / 'C' / 'D' / 'E' / 'F'",
            "Any digit 0-9 or 'A' or 'B' or 'C' or 'D' or 'E' or 'F'",
            "0123456789ABCDEF",
            "[0-9ABCDEF]"
        );
    }

    public static function HTAB() : ABNF {
        return new ABNF("HTAB", '%x09', 'Horizontal tab', [unitochr('\x09')], "[\x09]");
    }

    public static function LF() : ABNF {
        return new ABNF("LF", "%x0A", "linefeed", "\n", "[\x0A]");
    }

    public static function LWPS() : ABNF {
        return new ABNF("LWSP", "*(WSP / CRLF WSP)", "Linear white space",
            array_merge(Core::SP()->chars, Core::HTAB()->chars, Core::CRLF()->chars),
            "(?:[\x20\x09]|\x0D\x0A[\x20\x09])*"
        );
    }

    public static function OCTET() : ABNF {
        return new ABNF("OCTET", "%x00-FF", "8 bits of data",
            build_chrs_for_unicode_range('\x00', '\xFF'),
            '[\x00-\xFF]'
        );
    }

    public static function SP() : ABNF {
        return new ABNF("SP", '%x20', "Space", [unitochr('\x20')], '[\x20]');
    }

    public static function VCHAR() : ABNF {
        return new ABNF("VCHAR", "%x21-7E", "Visible printing characters",
            build_chrs_for_unicode_range('\x21', '\x7E'),
            '[\x21-\x7E]'
        );
    }

    public static function WSP() : ABNF {
        return new ABNF("WSP", "SP / HTAB", "White space",
            array_merge(Core::SP()->chars, Core::HTAB()->chars),
            '[\x20\x09]'
        );
    }

    public static function OWS() : ABNF {
        return new ABNF("OWS", "*( SP / HTAB )", "Optional whitespace",
            array_merge(Core::SP()->chars, Core::HTAB()->chars)
        , '[\x20\x09]*');
    }

    public static function RWS() : ABNF {
        return new ABNF("RWS", "1*( SP / HTAB )", "Required whitespace",
            array_merge(Core::SP()->chars, Core::HTAB()->chars)
        , '[\x20\x09]+');
    }

    public static function BWS() : ABNF {
        return new ABNF("BWS", "OWS", "Bad whitespace",
            Core::OWS()->chars,
            Core::OWS()->regexp
        );
    }
}
?>