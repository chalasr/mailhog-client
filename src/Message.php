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

use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * Immutable object representation of a MailHog message.
 */
class Message
{
    private $id;
    private $body;
    private $headers;
    private $parts;

    /**
     * @param string|int $id
     * @param string     $body
     * @param array      $headers
     * @param array      $parts
     */
    public function __construct($id, $body, array $headers = [], array $parts = [])
    {
        $this->id = $id;
        $this->body = $body;
        $this->headers = new HeaderBag($headers);
        $this->parts = $parts;
    }

    /**
     * Creates a new Message from a raw one.
     *
     * @param stdClass $raw The raw message
     *
     * @return Message
     */
    public static function createFromRaw(\stdClass $raw) : self
    {
        return new self(
            $raw->ID,
            $raw->Content->Body,
            (array) $raw->Content->Headers,
            $raw->MIME ? $raw->MIME->Parts : []
        );
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->headers->get('from');
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->headers->get('to');
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->headers->get('subject');
    }

    /**
     * @return string|null
     */
    public function getReplyTo()
    {
        return $this->headers->get('Reply-To');
    }

    /**
     * @return string\null
     */
    public function getReturnPath()
    {
        return $this->headers->get('Return-Path');
    }

    /**
     * @return HeaderBag
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Parses and returns the message HTML part.
     *
     * @return string|null
     */
    public function getHtmlPart()
    {
        $parts = $this->parts;

        if (!empty($parts[1]->Headers->{'Content-Type'})) {
            foreach ($parts[1]->Headers->{'Content-Type'} as $header_line) {
                if (false !== strpos($header_line, 'txt')) {
                    $part = $parts[2];

                    break;
                }
            }
        }

        if (!$part) {
            foreach($parts as $_part) {
                if (!empty($_part->Headers->{'Content-Type'})) {
                    foreach ($_part->Headers->{'Content-Type'} as $header_line) {
                        if (false !== strpos($header_line, 'txt')) {
                            $part = $_part;

                            break;
                        }
                    }
                }
            }
        }

        if (!$part) {
            return;
        }

        return $part->Body;
    }


    /**
     * Parses and returns the message TXT part.
     *
     * @return string|null
     */
    public function getTextPart()
    {
        $parts = $this->parts;

        if (!empty($parts[2]->Headers->{'Content-Type'})) {
            foreach ($parts[2]->Headers->{'Content-Type'} as $header_line) {
                if (false !== strpos($header_line, 'html')) {
                    $part = $parts[2];
                    break;
                }
            }
        }

        if (!$part) {
            foreach($parts as $_part) {
                if (!empty($_part->Headers->{'Content-Type'})) {
                    foreach ($_part->Headers->{'Content-Type'} as $header_line) {
                        if (false !== strpos($header_line, 'html')) {
                            $part = $_part;

                            break;
                        }
                    }
                }
            }
        }

        if (!$part) {
            return;
        }

        return $part->Body;
    }
}
