<?php

namespace App\Components;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class CEP
{
    protected static $redisClient = null;

    public static function get(string $value)
    {
        $value = Str::onlyNumbers($value);
        $data = self::getFromRedis($value);
        if ($data !== null)
            return $data;

        try {
            $client = new Client();
            $request = $client->request('GET', 'https://viacep.com.br/ws/' . $value . '/json/');
            $data = \json_decode($request->getBody()->getContents());
        } catch (ClientException $_) {};

        if (isset($data->erro) === true)
            $data = null;
        if ($data === null)
            return null;

        $data = (object)[
            'cep'      => $data->cep,
            'street'   => $data->logradouro,
            'district' => $data->bairro,
            'city'     => $data->localidade,
            'state'    => $data->uf,
        ];

        self::saveOnRedis($value, $data);
        return $data;
    }

    protected static function getRedisClient()
    {
        if (self::$redisClient === null)
            self::$redisClient = Redis::connection()->client();
        return self::$redisClient;
    }

    protected static function getRedisKeyByCPF(string $cep)
    { return ('cep.' . $cep); }

    protected static function getFromRedis(string $cep)
    {
        $redis = self::getRedisClient();
        $data = null;
        try { $data = \unserialize($redis->get(self::getRedisKeyByCPF($cep))); }
        catch (\Exception $_) {};

        if (is_object($data) === false)
            $data = null;
        return $data;
    }

    protected static function saveOnRedis(string $cep, $data)
    {
        $redis = self::getRedisClient();
        $redis->set(self::getRedisKeyByCPF($cep), \serialize($data));
    }
}
