<?php

declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Networking\Message;
use BitWasp\Bitcoin\Networking\Messages\GetData;
use BitWasp\Bitcoin\Networking\Peer\ConnectionParams;
use BitWasp\Bitcoin\Networking\Peer\Locator;
use BitWasp\Bitcoin\Networking\Peer\Peer;
use BitWasp\Bitcoin\Networking\Services;
use BitWasp\Bitcoin\Networking\Settings\Testnet3Settings;
use BitWasp\Bitcoin\Networking\Structure\Inventory;
use BitWasp\Bitcoin\Transaction\TransactionFactory;

$network = \BitWasp\Bitcoin\Network\NetworkFactory::TDCoinTestnet();
Bitcoin::setNetwork($network);
$transaction = TransactionFactory::fromHex('01000000010000000000000000000000000000000000000000000000000000000000000000ffffffff2202fc0104664b8c5d0004f0d91400000000102f5444434f494e205374726174756d2f00000000020000000000000000266a24aa21a9ede2f61c3f71d1defd3fa999dfa36953755c690689799962b48bebd836974e8cf900d6117e030000001976a914699dde28bdd51bee7fbb72245543aa7a61e3de1788ac00000000');

// Check for witnesses. If they are found, we increase the counter, eventually checking whether it equals zero.
$isWitness = (array_reduce($transaction->getWitnesses(), function ($counter, \BitWasp\Bitcoin\Script\ScriptWitnessInterface $wit) {
    return $wit->isNull() ? $counter : $counter + 1;
}, 0) !== 0);

$hash = $isWitness ? $transaction->getTxId() : $transaction->getWitnessTxId();

$loop = React\EventLoop\Factory::create();
$factory = new \BitWasp\Bitcoin\Networking\Factory($loop, $network);
$factory->setSettings(new Testnet3Settings());

$locator = $factory->getLocator();

$params = new ConnectionParams();
$params->setLocalServices(Services::NETWORK | Services::WITNESS);
$params->setRequiredServices(Services::NETWORK | Services::WITNESS);

$connector = $factory->getConnector($params);
$manager = $factory->getManager($connector);

$nodeRequestedTx = false;

$onGetData = function (Peer $peer, GetData $data) use ($hash, $transaction, $loop, &$nodeRequestedTx) {
    foreach ($data->getItems() as $inv) {
        if ($inv->getHash()->equals($hash)) {
            echo "Peer requested tx\n";
            $peer->tx($transaction);
            $nodeRequestedTx = true;
            //$loop->stop();
        }
    }
};

$onConnect = function (Peer $peer) use ($onGetData, $hash, $loop) {
    echo "connected to node\n";
    $loop->addTimer(5, function () use ($peer) {
        echo "timeout - close connection\n";
        $peer->close();
    });

    $peer->on(Message::GETDATA, $onGetData);
    $peer->inv([Inventory::tx($hash)]);
};

$manager->on('connection', $onConnect);

$locator->queryDnsSeeds()->then(function (Locator $locator) use ($manager, $onConnect) {
    return $manager
        ->connectNextPeer($locator)
        ->then($onConnect);
});

$loop->run();

if ($nodeRequestedTx) {
    echo "Node requested tx from us!\n";
} else {
    echo "Node ignored tx!\n";
}
