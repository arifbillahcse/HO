<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Keep a trailing slash ("/web-hosting/") the way browsers send it. Laravel's
     * test client strips it, which would hit the slash redirect on every request.
     */
    protected function prepareUrlForRequest($uri)
    {
        $prepared = parent::prepareUrlForRequest($uri);
        $path = strtok((string) $uri, '?#');

        if ($path === '/' || ! str_ends_with((string) $path, '/')) {
            return $prepared;
        }

        $query = strpbrk($prepared, '?#');

        return ($query === false ? $prepared : substr($prepared, 0, -strlen($query))) . '/' . ($query ?: '');
    }
}
