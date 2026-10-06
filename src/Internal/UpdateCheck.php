<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

final class UpdateCheck
{
    private readonly float $startedAt;

    private readonly int $owner;

    private function __construct(private readonly \CurlMultiHandle $multi, private readonly \CurlHandle $handle)
    {
        $this->startedAt = microtime(true);
        $this->owner = (int) getmypid();
    }

    public static function start(): ?self
    {
        $handle = curl_init(Defaults::UPDATE_NOTICE['REGISTRY_URL']);

        if ($handle === false) {
            return null;
        }

        $milliseconds = Protocol::UPDATE_NOTICE['TIMEOUT_MS'];

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_NOSIGNAL => true,
            CURLOPT_TIMEOUT_MS => $milliseconds,
            CURLOPT_CONNECTTIMEOUT_MS => $milliseconds,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: ' . Defaults::USER_AGENT],
        ]);

        $multi = curl_multi_init();
        curl_multi_add_handle($multi, $handle);
        $check = new self($multi, $handle);
        $check->settled();

        return $check;
    }

    public function isOwned(): bool
    {
        return $this->owner === (int) getmypid();
    }

    public function remaining(): float
    {
        return max(0.0, Protocol::UPDATE_NOTICE['TIMEOUT_MS'] / 1000 - (microtime(true) - $this->startedAt));
    }

    public function settled(float $budget = 0.0): ?string
    {
        $deadline = microtime(true) + $budget;

        while (true) {
            $running = 0;

            if (curl_multi_exec($this->multi, $running) !== CURLM_OK) {
                curl_multi_remove_handle($this->multi, $this->handle);

                return '';
            }

            if ($running === 0) {
                break;
            }

            if ($deadline - microtime(true) <= 0) {
                return null;
            }

            curl_multi_select($this->multi, min($deadline - microtime(true), 0.1));
        }

        $body = curl_multi_getcontent($this->handle);
        $status = curl_getinfo($this->handle, CURLINFO_RESPONSE_CODE);
        curl_multi_remove_handle($this->multi, $this->handle);

        return \is_string($body) && $status >= 200 && $status <= 299 ? $body : '';
    }
}
