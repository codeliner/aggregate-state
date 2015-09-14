<?php
/*
 * This file is part of the codeliner/aggregate-state.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 9/14/15 - 9:49 PM
 */
namespace Codeliner\Aggregate;

use Prooph\Common\Messaging\Message;

/**
 * Class AggregateState
 *
 * The AggregateState is an immutable value object representing the current state of the linked aggregate
 * The state can only be modified by applying domain events to the AggregateState. This will cause the state
 * to mutate but results in a new AggregateState object
 *
 * @package Codeliner\Aggregate
 */
class AggregateState
{
    /**
     * @var array
     */
    protected $aggregateData;

    /**
     * @var int
     */
    protected $aggregateVersion = 0;

    /**
     * @var string
     */
    protected $aggregateId;

    /**
     * Returns a new empty AggregateState linked with the aggregate by the aggregateId
     *
     * @param string $aggregateId
     * @return AggregateState
     */
    public static function asEmptyState($aggregateId)
    {
        return new static(['aggregate_id' => $aggregateId, 'aggregate_version' => 0]);
    }

    /**
     * @param array $aggregateData
     * @return AggregateState
     */
    public static function fromArray(array $aggregateData)
    {
        return new static($aggregateData);
    }

    private function __construct(array $aggregateData)
    {
        //@TODO: Assert data
        $this->aggregateId = $aggregateData['aggregate_id'];
        $this->aggregateVersion = $aggregateData['aggregate_version'];

        unset($aggregateData['aggregate_id']);
        unset($aggregateData['aggregate_version']);

        $this->aggregateData = $aggregateData;
    }

    /**
     * @return array Current state as array copy
     */
    public function toArray()
    {
        $aggregateData = $this->aggregateData;

        $aggregateData['aggregate_id'] = $this->aggregateId;
        $aggregateData['aggregate_version'] = $this->aggregateVersion;

        return $aggregateData;
    }

    /**
     * Return identifier of the linked aggregate as a string
     *
     * @return string
     */
    public function aggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * Get the verion of the aggregate this state represents
     *
     * @return int
     */
    public function aggregateVersion()
    {
        return $this->aggregateVersion;
    }

    /**
     * Apply state changes represented by given domain event
     * This method MUST return a new AggregateState because the state is a value object and therefor immutable
     *
     * @param Message $message
     * @throws \RuntimeException
     * @return AggregateState
     */
    public function applyDomainEvent(Message $message)
    {
        $newState = static::fromArray($this->toArray());

        $handler = $this->determineEventHandlerMethodFor($message);

        if (! method_exists($newState, $handler)) {
            throw new \RuntimeException(sprintf(
                "Missing event handler method %s for aggregate root %s",
                $handler,
                get_class($newState)
            ));
        }

        $newState->{$handler}($message);

        $newState->aggregateVersion = $message->version();

        return $newState;
    }

    /**
     * Determine event name
     *
     * @param Message $message
     *
     * @return string
     */
    protected function determineEventHandlerMethodFor(Message $message)
    {
        return 'when' . implode('', array_slice(explode('\\', get_class($message)), -1));
    }
}
 