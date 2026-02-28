<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SuperFrameworkEngine\App\UtilResponse\Response;

class ResponseTest extends TestCase
{
    public function testJsonResponse(): void
    {
        $response = new Response();
        $data = ['status' => 'success'];
        $json = @$response->json($data);
        
        $this->assertEquals(json_encode($data), $json);
    }

    public function testJsonCallableResponse(): void
    {
        $response = new Response();
        $json = @$response->json(fn() => ['status' => 'ok']);
        
        $this->assertEquals(json_encode(['status' => 'ok']), $json);
    }

    public function testDownloadResponse(): void
    {
        $response = new Response();
        $content = 'file content';
        $result = @$response->download($content, 'test.txt');
        
        $this->assertEquals($content, $result);
    }
}
