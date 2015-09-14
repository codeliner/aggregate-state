<?php
/*
 * This file is part of the codeliner/aggregate-state.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 9/14/15 - 11:23 PM
 */
namespace CodelinerExample\Aggregate\Repository;

use Codeliner\Aggregate\ProcessTranslator;
use CodelinerExample\Aggregate\User\User;
use CodelinerExample\Aggregate\User\UserState;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\EventStore;

final class UserRepository extends AggregateRepository
{
    public function __construct(EventStore $eventStore)
    {
        parent::__construct($eventStore, AggregateType::fromAggregateRootClass(User::class), new ProcessTranslator(UserState::class));
    }
}
 