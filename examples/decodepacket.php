<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Buffertools\Buffer;

if (!isset($argv[1])) {
    $hex = '12a6f55876657261636b000000000000000000005df6e0e2';
} else {
    $hex = $argv[1];
}

$net = new \BitWasp\Bitcoin\Networking\Serializer\NetworkMessageSerializer(\BitWasp\Bitcoin\Bitcoin::getDefaultNetwork());

print_r($net->parse(Buffer::hex($hex)));
