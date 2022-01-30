<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Extract a variable from a response.
     *
     * For example a variable sent to a view.
     *
     * @param $response
     * @param $key
     * @return mixed
     */
    protected function getResponseData($response, $key){
        $content = $response->getOriginalContent();
        $data = $content->getData();
        return $data[$key];
    }
}
