<?php
/*
 * This file is part of the codeliner/aggregate-state.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 9/14/15 - 10:12 PM
 */
namespace CodelinerExample\Aggregate\User;

use Codeliner\Aggregate\AggregateState;
use CodelinerExample\Aggregate\User\Event\UserNameWasChanged;
use CodelinerExample\Aggregate\User\Event\UserWasRegistered;
use Prooph\Common\Messaging\Message;

final class UserState extends AggregateState
{
    public function whenUserWasRegistered(UserWasRegistered $event)
    {
        $this->aggregateData['name'] = $event->payload()['name'];
        $this->aggregateData['email'] = $event->payload()['email'];
    }

    public function whenUserNameWasChanged(UserNameWasChanged $event)
    {
        $this->aggregateData['name'] = $event->payload()['newName'];
    }

    protected function assertMyEvent(Message $event)
    {
        if ($this->aggregateId !== $event->payload()['user_id']) {
            throw new \Exception("Wrong event received");
        }
    }
}
 