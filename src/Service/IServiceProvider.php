<?php

namespace Fux\Service;
interface IServiceProvider
{
    public static function bootstrap();

    public static function dispose();
}