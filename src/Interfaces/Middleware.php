<?php

namespace SuperFrameworkEngine\Interfaces;


interface Middleware
{
    public function handle(\Closure $closure);
}