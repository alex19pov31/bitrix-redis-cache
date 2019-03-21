<?php

namespace Alex19pov31\BitrixRedisCache;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Data\ICacheEngine;
use Bitrix\Main\Data\ICacheEngineStat;
use Predis\Client;

class RedisCacheEngine implements ICacheEngine, ICacheEngineStat
{
    /**
     * Redis client
     *
     * @var Client
     */
    private $client;

    /**
     * @param array|null $options
     */
    public function __construct($options = null)
    {
        $cacheConfig = Configuration::getValue("cache");
        if (is_null($options) && isset($cacheConfig["type"]) &&
            $cacheConfig['type'] == 'redis' && isset($cacheConfig["redis"])) {
            $options = $cacheConfig["redis"];
        }

        $this->client = new Client($options);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function isAvailable()
    {
        try {
            return $this->getClient()->ping() == "PONG";
        } catch (\Exception $e) {
            return false;
        }
    }

    public function clean($baseDir, $initDir = false, $filename = false)
    {
        $key = $this->getKey($baseDir, $initDir, $filename);
        if ($filename) {
            $this->getClient()->del($key);
            return;
        }

        $key += '*';
        $keyList = $this->getClient()->keys($key);
        $this->getClient()->del($keyList);
    }

    public function read(&$arAllVars, $baseDir, $initDir, $filename, $TTL)
    {
        $key = $this->getKey($baseDir, $initDir, $filename);
        if ($this->getClient()->exists($key) <= 0) {
            return false;
        }

        $rawData = $this->getClient()->get($key);
        $arAllVars = json_decode($rawData, true);
        return true;
    }

    public function write($arAllVars, $baseDir, $initDir, $filename, $TTL)
    {
        $key = $this->getKey($baseDir, $initDir, $filename);
        $data = json_encode($arAllVars);
        $this->getClient()->set($key, $data);
        $this->getClient()->expire($key, $TTL);
    }

    private function getKey($baseDir, $initDir, $filename): string
    {
        return (string) $initDir . ":" . $baseDir . ":" . $filename;
    }

    public function isCacheExpired($path)
    {
        return false;
    }

    public function getReadBytes()
    {
        $info = $this->getClient()->info();
        return (int) $info['Stats']['total_net_output_bytes'];
    }

    public function getWrittenBytes()
    {
        $info = (int) $this->getClient()->info();
        return (int) $info['Stats']['total_net_input_bytes'];
    }

    public function getCachePath()
    {
        return "";
    }
}
