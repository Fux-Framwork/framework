<?php

namespace Fux\Middleware;

use Fux\Http\Request;

interface IMiddleware{
    public function handle();
    public function setNext($closure);
    public function setRequest(Request $request);
}