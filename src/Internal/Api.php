<?php

declare(strict_types=1);

namespace Envless\Env\Internal;

use Envless\Envless;
use Envless\Exception\ApiException;
use Envless\Exception\EnvlessException;
use Envless\Exception\NetworkException;
use Envless\Http\HttpClient;

final class Api
{
    private ?Envless $client = null;

    public function __construct(#[\SensitiveParameter] private readonly string $token, private readonly ?string $endpoint, private readonly ?HttpClient $http) {}

    public function workspaceId(): ?string
    {
        $workspace = $this->call(static fn(Envless $client): array => $client->workspace->get(), '/workspace', []);
        $id = $workspace['id'] ?? null;

        return \is_string($id) && $id !== '' ? $id : null;
    }

    /** @return list<Variable> */
    public function variables(Target $target): array
    {
        $path = '/projects/' . Query::segment($target->project) . '/environments/' . Query::segment($target->environment) . '/variables';
        $variables = [];
        $offset = 0;

        while (true) {
            $query = ['limit' => Protocol::PUBLIC_PAGE_LIMIT, 'offset' => $offset, 'product' => $target->product];
            $payload = $this->call(static fn(Envless $client): mixed => $client->raw->request($path, 'GET', $query), $path, $query);
            $page = \is_array($payload) ? $payload : [];
            $items = $page['variables'] ?? [];

            if (!\is_array($items) || !array_is_list($items)) {
                throw new ApiFailure(Messages::NOT_A_LIST, false);
            }

            foreach ($items as $item) {
                $variable = Variable::fromApi($item);

                if ($variable !== null) {
                    $variables[] = $variable;
                }
            }

            $pagination = $page['pagination'] ?? null;

            if ($items === [] || !\is_array($pagination) || !Query::truthy($pagination['hasMore'] ?? null)) {
                return $variables;
            }

            $offset += \count($items);
        }
    }

    /**
     * @template T
     * @param \Closure(Envless): T $request
     * @param array<string, mixed> $query
     * @return T
     */
    private function call(\Closure $request, string $path, array $query): mixed
    {
        try {
            return $request($this->client ??= new Envless(token: $this->token, baseUrl: $this->endpoint, httpClient: $this->http, userAgent: Defaults::USER_AGENT, disableUpdateNotice: true));
        } catch (NetworkException $error) {
            $message = $error->isTimeout() ? \sprintf(Messages::TIMED_OUT, Query::url($this->client?->raw->baseUrl ?? '', $path, $query)) : $error->getMessage();

            throw new ApiFailure($message, true, $error);
        } catch (ApiException $error) {
            throw new ApiFailure($error->getMessage(), \in_array($error->status, Protocol::RETRYABLE_STATUSES, true), $error);
        } catch (EnvlessException $error) {
            throw new ApiFailure($error->getMessage(), false, $error);
        }
    }
}
