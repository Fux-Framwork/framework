<?php

namespace Fux\Redis;

use Fux\Redis\Clients\PredisClient;
use Predis\Client;

class Redis
{


    private static $client;

    public static function client()
    {
        if (self::$client) return self::$client;
        if (!self::$client) {
            switch (REDIS_CLIENT) {
                case 'predis':
                    self::$client = new PredisClient(REDIS_CONFIG);
                    return self::$client;
            }
        }
        throw new \Exception("Invalid redis client " . REDIS_CLIENT);
    }

    /**
     * Pass other method calls down to the underlying client.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return self::client()->command($method, $parameters);
    }

}
