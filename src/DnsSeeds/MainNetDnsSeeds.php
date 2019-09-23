<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Networking\DnsSeeds;

class MainNetDnsSeeds extends DnsSeedList
{
    public function __construct()
    {
        parent::__construct([
            'seeds.nigez.com',
            'seeds2.nigez.com',
            'seeds3.nigez.com',
            'seeds4.nigez.com',
//            'bitseed.xf2.org',
            'seeds5.nigez.com',
            'seeds6.nigez.com'
        ]);
    }
}
