<?php

namespace SymfonyBundles\EventQueueBundle\Service;

use SymfonyBundles\QueueBundle\Service\Queue;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Dispatcher extends Queue implements DispatcherInterface
{

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function on($class, ...$args)
    {
        $reflection = new \ReflectionClass($class);

        if (false === $reflection->hasConstant('NAME')) {
            throw new Exception\InvalidEventNameException($class);
        }

        if (false === $reflection->isSubclassOf(Event::class)) {
            throw new Exception\InvalidEventParentClassException($class);
        }

        $this->push(['class' => $class, 'args' => $args]);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch()
    {
        if (0 === $this->count()) {
            return false;
        }

        $queue = $this->pop();
        $class = new \ReflectionClass($queue['class']);
        $event = $class->newInstanceArgs($queue['args']);

        return $this->dispatcher->dispatch($class->getConstant('NAME'), $event);
    }

}
