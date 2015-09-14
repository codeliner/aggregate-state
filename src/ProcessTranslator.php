<?php
/*
 * This file is part of the codeliner/aggregate-state.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 9/14/15 - 11:25 PM
 */
namespace Codeliner\Aggregate;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Aggregate\AggregateTranslator;

final class ProcessTranslator extends Process implements AggregateTranslator
{
    private $processStateClass;

    public function __construct($processStateClass)
    {
        $this->processStateClass = $processStateClass;
    }
    /**
     * @param object $eventSourcedAggregateRoot
     * @return string
     */
    public function extractAggregateId($eventSourcedAggregateRoot)
    {
        return $eventSourcedAggregateRoot->aggregateId();
    }

    /**
     * @param AggregateType $aggregateType
     * @param Message[] $historyEvents
     * @return object reconstructed EventSourcedAggregateRoot
     */
    public function reconstituteAggregateFromHistory(AggregateType $aggregateType, $historyEvents)
    {
        $stateClass = $this->processStateClass;

        if (empty($historyEvents)) {
            throw new \Exception("History must not be empty");
        }

        $aggregateId = $historyEvents[0]->metadata()['aggregate_id'];

        $state = $stateClass::asEmptyState($aggregateId);

        foreach ($historyEvents as $historyEvent) {
            $state = $state->applyDomainEvent($historyEvent);
        }

        $aggregateClass = $aggregateType->toString();

        $aggregate = new $aggregateClass();

        $aggregate->updateState($state);

        return $aggregate;
    }

    /**
     * @param object $eventSourcedAggregateRoot
     * @return Message[]
     */
    public function extractPendingStreamEvents($eventSourcedAggregateRoot)
    {
        return $eventSourcedAggregateRoot->popRecordedEvents();
    }

    /**
     * Return identifier of the aggregate as a string
     *
     * @return string
     */
    protected function aggregateId()
    {
        throw new \BadMethodCallException("Not implemented by the decorator");
    }
}
 