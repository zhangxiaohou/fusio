<?php

namespace Fusio\Backend\Api\App;

use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Http\Stream\TempStream;
use PSX\Test\ControllerDbTestCase;
use PSX\Test\Environment;
use PSX\Url;

class EntityTest extends ControllerDbTestCase
{
	public function getDataSet()
	{
		return $this->createFlatXMLDataSet(__DIR__ . '/../../../fixture.xml');
	}

	public function testGet()
	{
		$response = $this->sendRequest('http://127.0.0.1/backend/app/2', 'GET', array(
			'User-Agent'    => 'Fusio TestCase', 
			'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
		));

		$body   = (string) $response->getBody();
		$expect = <<<'JSON'
{
    "id": 2,
    "userId": 2,
    "status": 1,
    "name": "Foo-App",
    "url": "http:\/\/google.com",
    "appKey": "5347307d-d801-4075-9aaa-a21a29a448c5",
    "appSecret": "342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d",
    "scopes": [
        "foo",
        "bar"
    ],
    "tokens": []
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
		$this->assertJsonStringEqualsJsonString($expect, $body, $body);
	}

	public function testPost()
	{
		$response = $this->sendRequest('http://127.0.0.1/backend/app/2', 'POST', array(
			'User-Agent'    => 'Fusio TestCase', 
			'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
		), json_encode([
			'name'   => 'Foo',
			'class'  => 'Fusio\Action\SqlFetchRow',
			'config' => [
				'connection' => 1,
				'sql'        => 'SELECT * FROM foo'
			],
		]));

		$body = (string) $response->getBody();

		$this->assertEquals(405, $response->getStatusCode(), $body);
	}

	public function testPut()
	{
		$response = $this->sendRequest('http://127.0.0.1/backend/app/4', 'PUT', array(
			'User-Agent'    => 'Fusio TestCase', 
			'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
		), json_encode([
			'status' => 2,
			'userId' => 2,
			'name'   => 'Bar',
			'url'    => 'http://microsoft.com',
			'scopes' => ['foo', 'bar']
		]));

		$body   = (string) $response->getBody();
		$expect = <<<'JSON'
{
    "success": true,
    "message": "App successful updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
		$this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'userId', 'name', 'url')
            ->from('fusio_app')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(4, $row['id']);
        $this->assertEquals(2, $row['status']);
        $this->assertEquals(2, $row['userId']);
        $this->assertEquals('Bar', $row['name']);
        $this->assertEquals('http://microsoft.com', $row['url']);

        $scopes = Environment::getService('table_manager')->getTable('Fusio\Backend\Table\Scope')->getByApp(4);

        $this->assertEquals(array('bar', 'foo'), $scopes);
	}

	public function testDelete()
	{
		$response = $this->sendRequest('http://127.0.0.1/backend/app/4', 'DELETE', array(
			'User-Agent'    => 'Fusio TestCase', 
			'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
		));

		$body   = (string) $response->getBody();
		$expect = <<<'JSON'
{
    "success": true,
    "message": "App successful deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
		$this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id')
            ->from('fusio_app')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(3, $row['id']);
	}
}