<?php

/*
 * This file is part of the RCH\MailHog package.
 *
 * (c) Robin Chalas <robin.chalas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RCH\MailHog\Tests;

use RCH\MailHog\Client;
use RCH\MailHog\Message;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testFindAll()
    {
        $httpClient = $this->getHttpClient();
        $response = new Response(200, [], json_encode($this->getResponseBodyStub()));
        $httpClient->get('messages', [])->willReturn($response);

        $client = new Client('http://localhost');
        $client->setHttpClient($httpClient->reveal());

        $res = $client->findAll();

        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertInstanceOf(Message::class, $res->current());
    }

    public function testFindBy()
    {
        $httpClient = $this->getHttpClient();
        $response = new Response(200, [], json_encode($this->getResponseBodyStub()));
        $httpClient
            ->get('search', ['query' => ['kind' => 'containing', 'query' => 'dummy_subject', 'limit' => 100]])
            ->willReturn($response);

        $client = new Client('http://localhost');
        $client->setHttpClient($httpClient->reveal());

        $res = $client->findBy('containing', 'dummy_subject');

        $this->assertInstanceOf(\Generator::class, $res);
        $this->assertInstanceOf(Message::class, $res->current());
    }

    public function testFindOneBy()
    {
        $httpClient = $this->getHttpClient();
        $response = new Response(200, [], json_encode($this->getResponseBodyStub()));
        $httpClient->get('search', ['query' => ['kind' => 'containing', 'query' => 'dummy_subject', 'limit' => 1]])->willReturn($response);

        $client = new Client('http://localhost');
        $client->setHttpClient($httpClient->reveal());

        $res = $client->findOneBy('containing', 'dummy_subject');

        $this->assertInstanceOf(Message::class, $res);
    }

    private function getHttpClient()
    {
        return $this->prophesize(HttpClient::class);
    }

    private function getResponseBodyStub()
    {
        $rawMessage = new \stdClass();
        $rawMessage->ID = 'dummy_id';
        $rawMessage->Content = new \stdClass();
        $rawMessage->Content->Body = 'dummy_body';
        $rawMessage->Content->Headers = new \stdClass();
        $rawMessage->Content->Headers->To = 'dummy_to';
        $rawMessage->Content->Headers->From = 'dummy_from';
        $rawMessage->Content->Headers->Subject = 'dummy_subject';
        $rawMessage->MIME = null;

        $body = new \stdClass();
        $body->items = [$rawMessage];

        return $body;
    }
}
