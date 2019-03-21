[![Latest Stable Version](https://poser.pugx.org/alex19pov31/bitrix-redis-cache/v/stable)](https://packagist.org/packages/alex19pov31/bitrix-redis-cache)

# Bitrix Redis cache

Кеширование данных посредством Redis.

## Установка

```bash
composer require alex19pov31/bitrix-redis-cache
```

## Настройка 

В файле **/bitrix/.settings_extra.php** (если его нет, то создать) прописываем настройки:

```php
<?php
return [
  'cache' => [
    'value' => [
      'type' => [
          'class_name' => 'Alex19pov31\BitrixRedisCache\RedisCacheEngine'
        ],
      'redis' => [
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379,
      ],
      'sid' => $_SERVER["DOCUMENT_ROOT"]."#01"
    ],
  ],
];
```

Подробней о параметрах подключения можно прочитать тут - https://github.com/nrk/predis/wiki/Connection-Parameters

## Использование

```php
use Bitrix\Main\Data\Cache;

$data = null;
$ttl = 3600; // кешируем на час
$key = "test_key"; // ключ кеша

$cache = Cache::createInstance();
if ($cache->initCache($ttl, $key, 'redis')) {
    $data = $cache->getVars();
    // или если имело место быть кеширование вывода
    $cache->output();
} elseif ($cache->startDataCache($ttl, $key, 'redis', [])) {
	$data = 'Тестовые данные';
	$cache->endDataCache($data);
}
```

## Принудительное использование (без конфигурационного файла)

```php
use Alex19pov31\BitrixRedisCache\RedisCacheEngine;
use Bitrix\Main\Data\Cache;

$cacheEngine = new RedisCacheEngine([
	'scheme' => 'tcp',
	'host' => '127.0.0.1',
	'port' => 6379,
]);

$data = null;
$ttl = 3600; // кешируем на час
$key = "test_key"; // ключ кеша

$cache = new Cache($cacheEngine);
if ($cache->initCache($ttl, $key, 'redis')) {
    $data = $cache->getVars();
    // или если имело место быть кеширование вывода
    $cache->output();
} elseif ($cache->startDataCache($ttl, $key, 'redis', [])) {
	$data = 'Тестовые данные';
	$cache->endDataCache($data);
}