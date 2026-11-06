
<?php
require_once __DIR__ . '/vendor/autoload.php';

function getRedis()
{
    static $redis = null;

    if ($redis !== null) {
        return $redis;
    }

    try {
        $client = new Predis\Client([
            'scheme' => 'tcp',
            'host' => '127.0.0.1',
            'port' => 6379,
            'timeout' => 1
        ]);

        $client->ping();
        $redis = $client;

        return $redis;
    } catch (Throwable $e) {
        error_log("Redis error: " . $e->getMessage());
        return null;
    }
}

function getCachedAnswer($question)
{
    $redis = getRedis();

    if ($redis === null) {
        return null;
    }

    try {
        return $redis->get(
            'ai_milk_tea:question:' . hash('sha256', $question)
        );
    } catch (Throwable $e) {
        error_log("Redis GET error: " . $e->getMessage());
        return null;
    }
}

function saveCachedAnswer($question, $answer)
{
    $redis = getRedis();

    if ($redis === null) {
        return false;
    }

    try {
        $redis->setex(
            'ai_milk_tea:question:' . hash('sha256', $question),
            3600,
            $answer
        );

        return true;
    } catch (Throwable $e) {
        error_log("Redis SET error: " . $e->getMessage());
        return false;
    }
}
?>
