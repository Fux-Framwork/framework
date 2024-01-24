<?php

namespace Fux\Redis\Clients;

use Predis\Client;

class PredisClient extends RedisClientAbstract
{

    public function __construct($config, $options = [])
    {
        $allOptions = array_merge(['timeout' => 10.0], $options, $config['options'] ?? []);
        $this->client = new Client($config, $allOptions);
        return $this;
    }

    /**
     * Subscribe to a set of given channels for messages.
     *s
     * @param  array|string  $channels
     * @param  \Closure  $callback
     * @param  string  $method
     * @return void
     */
    public function createSubscription($channels, Closure $callback, $method = 'subscribe')
    {
        $loop = $this->pubSubLoop();

        call_user_func_array([$loop, $method], (array) $channels);

        foreach ($loop as $message) {
            if ($message->kind === 'message' || $message->kind === 'pmessage') {
                call_user_func($callback, $message->payload, $message->channel);
            }
        }

        unset($loop);
    }

}
