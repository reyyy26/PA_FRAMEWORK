<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Session;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function jsonWithCsrf(string $method, string $uri, array $data = [], array $headers = []): TestResponse
    {
        Session::start();
        $token = Session::token();

        $headers = array_merge([
            'X-CSRF-TOKEN' => $token,
            'X-Requested-With' => 'XMLHttpRequest',
        ], $headers);

        $payload = array_merge(['_token' => $token], $data);

        return $this->json($method, $uri, $payload, $headers);
    }

    protected function postJsonWithCsrf(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->jsonWithCsrf('POST', $uri, $data, $headers);
    }

    protected function putJsonWithCsrf(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->jsonWithCsrf('PUT', $uri, $data, $headers);
    }

    protected function deleteJsonWithCsrf(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->jsonWithCsrf('DELETE', $uri, $data, $headers);
    }
}
