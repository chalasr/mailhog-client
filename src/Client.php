<?php

/*
 * This file is part of the RCH\MailHog package.
 *
 * (c) Robin Chalas <robin.chalas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RCH\MailHog;

use GuzzleHttp\Client as HttpClient;
use Psr\Http\Message\ResponseInterface;

/**
 * Client for the MailHog Api V2.
 */
class Client
{
    const PORT = 8025;

    /** @var HttpClient */
    private $client;

    /**
     * @param string $host
     * @param int    $port
     */
    public function __construct($baseUri, $port = self::PORT)
    {
        $this->client = new HttpClient(['base_uri' => sprintf('%s:%s/api/v2/', $baseUri, $port)]);
    }

    /**
     * Find all messages.
     *
     * @return Message[]
     */
    public function findAll()
    {
        return $this->doQuery(null, 'messages');
    }

    /**
     * Find one message containing a given keyword in its body/subject.
     *
     * @param string $search
     *
     * @return Message|null
     */
    public function findOneLike(string $search)
    {
        return $this->findOneBy('containing', $search);
    }

    /**
     * Find one message sent from a given address.
     *
     * @param string $from
     *
     * @return Message|null
     */
    public function findOneFrom(string $from)
    {
        return $this->findOneBy('from', $from);
    }

    /**
     * Find one message sent to a given address.
     *
     * @param string $to
     *
     * @return Message|null
     */
    public function findOneTo(string $to)
    {
        return $this->findOneBy('to', $to);
    }

    /**
     * Find all messages sent to a given address.
     *
     * @param string   $to
     * @param int|null $limit
     *
     * @return Message[]
     */
    public function findTo(string $to, int $limit = null)
    {
        return $this->findBy('to', $to, $limit);
    }

    /**
     * Find all messages sent to a given address.
     *
     * @param string   $from
     * @param int|null $limit
     *
     * @return Message[]
     */
    public function findFrom(string $from, int $limit = null)
    {
        return $this->findBy('from', $from, $limit);
    }

    /**
     * Find all messages for a given criteria.
     *
     * @param string   $criteria
     * @param string   $value
     * @param int|null $limit
     *
     * @return \Generator
     */
    public function findBy(string $criteria, string $search, int $limit = null)
    {
        return $this->doQuery(['kind' => $criteria, 'query' => $search, 'limit' => $limit ?? 100]);
    }

    /**
     * Find one message for given criteria.
     *
     * @param string $criteria
     * @param string $search
     *
     * @return Message|null
     */
    public function findOneBy(string $criteria, string $search)
    {
        return $this->findBy($criteria, $search, 1)->current();
    }

    /**
     * Get the last message sent.
     *
     * @return Message|null
     */
    public function getLast()
    {
        $messages = iterator_to_array($this->findAll());

        return end($messages);
    }

    /**
     * @param HttpClient $client
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Performs a given query.
     *
     * @param array|null  $query
     * @param string|null $endpoint
     *
     * @return Message[]
     */
    private function doQuery(array $query = null, string $endpoint = 'search')
    {
        return $this->getMessagesFromResponse(
            $this->client->get($endpoint, null === $query ? [] : ['query' => $query])
        );
    }

    /**
     * Gets Message objects from an Api Response.
     *
     * @param ResponseInterface $response
     *
     * @return \Generator
     *
     * @throws \Exception If the response is a failure
     */
    private function getMessagesFromResponse(ResponseInterface $response) : \Generator
    {
        if (200 !== $response->getStatusCode()) {
            throw new \Exception(
                sprintf('An error occured while executing the query.%s Status code: %d, Reason: %s', PHP_EOL, $response->getStatusCode(), $response->getReasonPhrase())
            );
        }

        $responseBody = json_decode($response->getBody());

        foreach ($responseBody->items as $item) {
            yield Message::createFromRaw($item);
        }
    }
}
