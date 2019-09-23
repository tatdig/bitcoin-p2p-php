<?php

declare(strict_types=1);

namespace BitWasp\Bitcoin\Tests\Networking\Messages;

use BitWasp\Bitcoin\Block\BlockFactory;
use BitWasp\Bitcoin\Bloom\BloomFilter;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Math\Math;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Networking\Messages\Factory;
use BitWasp\Bitcoin\Tests\Networking\TestCase;
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;

class MerkleBlockTest extends TestCase
{
    public function testMerkleBlock()
    {
        $factory = new Factory(NetworkFactory::tdcoin(), new Random());
        $hex = '0000002024b7a3a5c8926ef0f1c5cfb471000ea3a1d98f7df725245bb382050000000000f8c2d120bf9e200b5dc946352c619fdca58e4e5ed0cb6a10d0ad273346fcb942c06bef5ce00f081b67bb19c50102000000010000000000000000000000000000000000000000000000000000000000000000ffffffff0b0210276084f704bb1e0000000000000100d6117e030000001976a9144e07ae092298b2b020efbb868ce28838155f12b088ac00000000';

        $block = BlockFactory::fromHex($hex);
        $math = new Math();

        $flags = BloomFilter::UPDATE_ALL;
        $filter = BloomFilter::create($math, 10, 0.000001, 0, $flags);
        $filter->insertData(Buffer::hex('63194f18be0af63f2c6bc9dc0f777cbefed3d9415c4af83f3ee3a3d669c00cb5', 32));

        // Check that Merkleblock message is serialized correctly
        $filtered = $block->filter($filter);
        $this->assertEquals($block->getHeader(), $filtered->getHeader());

        $merkle = $factory->merkleblock($filtered);

        $serialized = $merkle->getNetworkMessage()->getBuffer();
        $parsed = $factory->parse(new Parser($serialized))->getPayload();
        $this->assertEquals($merkle, $parsed);
    }
}
