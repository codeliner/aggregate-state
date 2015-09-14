<?php
/*
 * This file is part of the codeliner/aggregate-state.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 9/14/15 - 9:47 PM
 */
namespace Codeliner\Aggregate;

use Prooph\Common\Messaging\Message;

/**
 * Class Process
 *
 * The Process is the processing part of the aggregate
 * It does not manage the state of the aggregate but only processes incoming commands and records domain events which
 * track the changes caused by the command
 *
 * @package Codeliner\Aggregate
 */
abstract class Process
{
    /**
     * Current version
     *
     * @var float
     */
    protected $version = 0;

    /**
     * List of events that are not committed to the EventStore
     *
     * @var Message[]
     */
    protected $recordedEvents = [];

    /**
     * @var AggregateState
     */
    protected $state;

    /**
     * Return identifier of the aggregate as a string
     *
     * @return string
     */
    abstract protected function aggregateId();

    /**
     * We do not allow public access to __construct, this way we make sure that an aggregate root can only
     * be constructed by static factories
     */
    protected function __construct()
    {
    }

    /**
     * @param AggregateState $aggregateState
     * @return mixed
     */
    protected function updateState(AggregateState $aggregateState)
    {
        $this->state = $aggregateState;
        $this->version = $aggregateState->aggregateVersion();
    }

    /**
     * Get pending events and reset stack
     *
     * @return Message[]
     */
    protected function popRecordedEvents()
    {
        $pendingEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $pendingEvents;
    }

    /**
     * Record a domain event
     *
     * @param Message $event
     */
    protected function recordThat(Message $event)
    {
        $this->version++;

        $this->recordedEvents[] = $event->withVersion($this->version);
    }
}
 