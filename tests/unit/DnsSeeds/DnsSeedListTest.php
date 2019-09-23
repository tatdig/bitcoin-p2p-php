<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\DnsSeeds;

use BitWasp\Bitcoin\Networking\DnsSeeds\DnsSeedList;
use BitWasp\Bitcoin\Networking\DnsSeeds\MainNetDnsSeeds;
use BitWasp\Bitcoin\Networking\DnsSeeds\TestNetDnsSeeds;
use BitWasp\Bitcoin\Tests\Networking\TestCase;

class DnsSeedListTest extends TestCase
{
    public function testAddToList()
    {
        $val = '1.1.1.1';

        $list = new DnsSeedList([]);
        $this->assertEquals(0, count($list->getHosts()));

        $list->addHost($val);
        $this->assertEquals(1, count($list->getHosts()));
        $this->assertEquals([$val], $list->getHosts());
    }

    public function testListConstructor()
    {
        $args = [
            '1.1.1.1',
        ];

        $list = new DnsSeedList($args);

        $this->assertEquals($args, $list->getHosts());
    }

    public function testSeedsInList()
    {
        $fixture1 = [new MainNetDnsSeeds(), [
            'seeds.nigez.com',
            'seeds4.nigez.com',
        ]];
        $fixture2 = [new TestNetDnsSeeds(), [
            'test-seeds.nigez.com',
        ]];

        foreach ([$fixture1, $fixture2] as $fixture) {
            list ($list, $known) = $fixture;
            /** @var DnsSeedList $list */
            $hosts = $list->getHosts();
            foreach ($known as $host) {
                $this->assertTrue(in_array($host, $hosts));
            }
        }
    }
}
