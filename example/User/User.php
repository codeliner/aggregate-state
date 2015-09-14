<?php
/*
 * This file is part of the codeliner/aggregate-state.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 9/14/15 - 10:08 PM
 */
namespace CodelinerExample\Aggregate\User;

use Codeliner\Aggregate\Process;
use CodelinerExample\Aggregate\User\Event\UserNameWasChanged;
use CodelinerExample\Aggregate\User\Event\UserWasRegistered;

/**
 * Class User
 *
 * @package CodelinerExample\Aggregate
 */
final class User extends Process
{
    public static function register($id, $name, $email)
    {
        $self = new self();

        $self->state = UserState::asEmptyState($id);

        $self->recordThat(UserWasRegistered::occur($id, [
            'name' => $name,
            'email' => $email
        ]));

        return $self;
    }

    /**
     * @param $newName
     */
    public function changeName($newName)
    {
        //Idempotent message receiver: if a ChangeName command arrives multiple times, we only handle it the first time
        if ($this->state->toArray()['name'] === $newName) {
            return;
        }

        $this->recordThat(UserNameWasChanged::occur($this->aggregateId(), [
            'oldName' => $this->state->toArray()['name'],
            'newName' => $newName
        ]));
    }

    /**
     * Required only for the test script!
     */
    public function dumpState()
    {
        var_dump($this->state->toArray());
    }

    /**
     * Return identifier of the aggregate as a string
     *
     * @return string
     */
    protected function aggregateId()
    {
        return $this->state->toArray()['aggregate_id'];
    }
}
 