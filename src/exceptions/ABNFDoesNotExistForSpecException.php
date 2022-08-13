<?php

namespace FT\RFC_ABNF\Exceptions;

use Exception;
use FT\RFC_ABNF\Enums\Specs;

final class ABNFDoesNotExistForSpecException extends Exception {

    public function __construct(string $method, Specs $spec)
    {
        parent::__construct($spec->name . " does not have '$method' defined ABNF");
    }

}

?>