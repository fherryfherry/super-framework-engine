<?php

declare(strict_types=1);

namespace SuperFrameworkEngine\App\UtilORM\Drivers;

class Mysql extends Driver
{
    public function __construct(array $arguments)
    {
        parent::__construct($arguments);
        $this->randomFuncTemplate = "RAND()";
        $this->pdoQueryTemplate = "mysql:host={host};dbname={database}";
    }
}
