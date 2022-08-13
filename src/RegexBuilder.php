<?php

namespace FT\RFC_ABNF;

use FT\RFC_ABNF\Enums\Specs;
use FT\RFC_ABNF\Exceptions\ABNFDoesNotExistForSpecException;
use InvalidArgumentException;
use ReflectionClass;

final class RegexBuilder {
    private ReflectionClass $class;
    private string $pattern = "";
    private array $spec_methods;

    public function __construct(public readonly Specs $spec)
    {
        if ($spec === null)
            throw new InvalidArgumentException("Spec can not be null");

        $this->class = new ReflectionClass("FT\\RFC_ABNF\\Specs\\" . $spec->name);

        foreach ($this->class->getMethods() as $method) {
            if (!$method->isPublic() && !$method->isStatic()) continue;
            if ($method->getReturnType()?->getName() !== "FT\\RFC_ABNF\\ABNF") continue;

            $this->spec_methods[$method->getName()] = $method->invoke(null);
        }
    }

    public function captureGroup(RegexBuilder | string $value, string $name = null) : RegexBuilder {
        $name = isset($name) ? "?<$name>" : "";
        $this->pattern .= "($name";
        $this->pattern .= $value instanceof RegexBuilder ? $value->build() : $value;
        $this->pattern .= ")";
        return $this;
    }

    /**
     * A non capturing group
     */
    public function group(RegexBuilder | string $value) : RegexBuilder {
        $this->pattern .= "(?:";
        $this->pattern .= $value instanceof RegexBuilder ? $value->build() : $value;
        $this->pattern .= ")";
        return $this;
    }

    /**
     * Append a '?' to the last builder pattern method
     */
    public function optional() : RegexBuilder {
        $this->pattern .= "?";
        return $this;
    }

    /**
     * Append a '*' to the last builder pattern method
     */
    public function zeroOrMore() : RegexBuilder {
        $this->pattern .= "*";
        return $this;
    }

    /**
     * Append a '+' to the last builder pattern method
     */
    public function oneOrMore() : RegexBuilder {
        $this->pattern .= "+";
        return $this;
    }

    /**
     * Follows normal regexp repition patterns.
     *
     * If $max == null then the syntax is '{#}'; equivalent to must have n occurrences exactly
     * else if $max == PHP_INT_MAX then the syntax is '{#,}'; equivalent to must have at least n occurrences
     * else the syntax is '{#,#}'; equivalent to must have between n and n occurrences
     */
    public function repitition(int $min, ?int $max = PHP_INT_MAX) : RegexBuilder {
        $this->pattern .= "{" . $min;
        if ($max === null) $this->pattern .= "}";
        else if ($max === PHP_INT_MAX) $this->pattern .= ",}";
        else $this->pattern .= ",$max}";
        return $this;
    }

    /**
     * Append the regexp pattern using an identifier named in the specs ABNF grammar.
     *
     * For example, if RFC9110 has 'DIGIT' as an identifier you would simply
     * use this method like: $builder->with("digit")->build()
     *
     * And the result would be:
     *
     * "[\x30-\x39]"
     */
    public function with(string $spec_identifier) : RegexBuilder {
        $this->pattern .= $this->getMethod($spec_identifier)->regexp;
        return $this;
    }

    /**
     * Append the regexp pattern using an identifier named in the specs ABNF grammar followed by a comma separated group of the same regexp.
     *
     * For example, if RFC9110 has 'DIGIT' as an identifier you would simply
     * use this method like: $builder->withCommaSeparated("digit")->build()
     *
     * And the result would be:
     *
     * "[\x30-\x39](?:OWS*,OWS*[\x30-\x39])"
     */
    public function withCommaSeparated(string $spec_identifier) : RegexBuilder {
        $regexp = $this->getMethod($spec_identifier)->regexp;
        $this->pattern .=  $regexp . '(?:' . Core::OWS()->regexp . ',' . Core::OWS()->regexp . $regexp . ')';
        return $this;
    }

    /**
     * Append any text to the regexp
     */
    public function text(string $text) : RegexBuilder {
        $this->pattern .= $text;
        return $this;
    }

    /**
     * Build a pattern using ABNF notation
     */
    public function abnf(string $abnf_expression, bool $remove_whitespace = true) : RegexBuilder {
        if ($abnf_expression === null)
            throw new InvalidArgumentException("Expression can not be null");

        if ($remove_whitespace)
            $abnf_expression = preg_replace("/\s+/", "", $abnf_expression);

        foreach ($this->spec_methods as $key => $value) {
            $regexp = join("[-_]", preg_split("/_/", $key));

            preg_match_all("/\:(#)?$regexp/i", $abnf_expression, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL);

            foreach ($matches as $match) {
                $identifier = $match[0][0];
                $index = $match[0][1];
                $isCommaSeparated = $match[1][0] !== null;
                $replace = $value->regexp;

                if ($isCommaSeparated)
                    $replace .= '(' . Core::OWS()->regexp . ',' . Core::OWS()->regexp . $value->regexp . ')';

                $abnf_expression = substr($abnf_expression, 0, $index)
                                   . $replace
                                   . substr($abnf_expression, $index + strlen($identifier));
            }
        }

        $this->pattern .= $abnf_expression;
        return $this;
    }

    public function build() : string {
        return $this->pattern;
    }

    private function getMethod(string $name) : ABNF {
        $name = strtoupper(str_replace('-', '_', $name));
        if (!key_exists($name, $this->spec_methods))
            throw new ABNFDoesNotExistForSpecException($name, $this->spec);

        return $this->class->getMethod(strtoupper($name))->invoke(null);
    }

}

?>