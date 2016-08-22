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

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testFindAll()
    {
        $this->assertInstanceOf(\Generator::class, (new Client('http://ems.dryva.dev'))->findAll());
    }

    public function testFindBy()
    {
        $this->assertInstanceOf(\Generator::class, (new Client('http://ems.dryva.dev'))->findBy('containing', 'dummy'));
    }

    public function testFindOneBy()
    {
        $this->assertNull((new Client('http://ems.dryva.dev'))->findOneBy('containing', 'dummy'));
    }
}
