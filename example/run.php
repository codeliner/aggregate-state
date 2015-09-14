<?php
/*
 * This file is part of the codeliner/aggregate-state.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 9/14/15 - 11:46 PM
 */
require __DIR__ . '/../vendor/autoload.php';

$eventStore = new \Prooph\EventStore\EventStore(
    new \Prooph\EventStore\Adapter\InMemoryAdapter(),
    new \Prooph\Common\Event\ProophActionEventEmitter()
);

$eventStore->beginTransaction();

$streamName = new \Prooph\EventStore\Stream\StreamName('event_stream');
$singleStream = new \Prooph\EventStore\Stream\Stream($streamName, []);

$eventStore->create($singleStream);

$eventStore->commit();

$userRepository = new \CodelinerExample\Aggregate\Repository\UserRepository($eventStore);

$user = \CodelinerExample\Aggregate\User\User::register('123', 'John', 'doe@test.com');

$eventStore->beginTransaction();

$userRepository->addAggregateRoot($user);

$eventStore->commit();

$freshUserRepository = new \CodelinerExample\Aggregate\Repository\UserRepository($eventStore);

$eventStore->beginTransaction();

$freshUser = $freshUserRepository->getAggregateRoot('123');

$freshUser->changeName('Jane');

$eventStore->commit();

$finalUserRepo = new \CodelinerExample\Aggregate\Repository\UserRepository($eventStore);

$finalUser = $finalUserRepo->getAggregateRoot('123');

echo "Final user state:\n\n";
$finalUser->dumpState();

