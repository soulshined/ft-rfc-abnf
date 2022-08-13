<?php

namespace FT\RFC_ABNF;

class ABNF {
    public readonly array $chars;

    public function __construct(
        public readonly string $rule,
        public readonly string $notation,
        public readonly string $definition,
        array | string $chars,
        public readonly string $regexp
    )
    {
        $this->chars = is_array($chars)
            ? $chars
            : str_split($chars);
    }

    public function createAlias(string $name, string $notation = null) : ABNF {
        return new ABNF(
            $name,
            $notation ?? $this->notation,
            $this->definition,
            $this->chars,
            $this->regexp
        );
    }
}

?>