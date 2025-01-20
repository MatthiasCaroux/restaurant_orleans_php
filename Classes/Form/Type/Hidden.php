<?php
declare(strict_types=1);

namespace Form\Type;
use Form\Type\Input;

final class Hidden extends Input {
    protected string $type = 'hidden'; 
}

?>