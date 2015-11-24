<?php

/**
 * Event dispatcher
 *
 */
class Mjolnic_EventDispatcher
{
    public $handlers = array();
    public $emitted = array();
    /**
     *
     * @var EventDispatcher
     */
    protected static $instance = null;

    /**
     *
     * @return EventDispatcher
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Listens to an event
     *
     * @param string $event The event name
     * @param callable $handler Callable function to be triggered when the event is emited
     * @param int $priority Handler priority
     * @param mixed $emitter Object to listen to
     */
    public function on($event, $handler, $priority = 0, $emitter = null)
    {
        $this->handlers[$event][$priority][] = array(
            "handler" => $handler,
            "emitter" => $emitter
        );
        ksort($this->handlers[$event][$priority]);
    }

    /**
     *
     * @param string $event Event to be unlistened
     * @param mixed $emitter Object that emits the event, if null a global event is unlistened
     * @return int Total of unregistered handlers
     */
    public function off($event, $emitter = null)
    {
        $counter = 0;
        if (isset($this->handlers[$event])) {
            foreach ($this->handlers[$event] as $priority => $handlers) {
                foreach ($handlers as $i => $h) {
                    if ($emitter === $h["emitter"]) {
                        unset($this->handlers[$event][$priority][$i]);
                        $counter++;
                    }
                }
            }
        }
        return $counter;
    }

    /**
     *
     * @param string $event Event name
     * @param array $args Arguments that will be passed to the callable handler, after the dispatcher and emitter instances
     * @param mixed $emitter Object that emits the event, if null a global event is emitted
     * @return int The total handlers that listened to the event
     */
    public function trigger($event, array $args = array(), $emitter = null)
    {
        $counter = 0;
        $start_mtime = microtime(true);
        // first two parameters will always be $this (the emitter) and $emitter (the sender or instance that emitted the event)
        array_unshift($args, $this, $emitter);
        if (isset($this->handlers[$event])) {
            foreach ($this->handlers[$event] as $priority => $handlers) {
                foreach ($handlers as $i => $h) {
                    if (($emitter === $h["emitter"])) {
                        call_user_func_array($h["handler"], $args);
                        $counter++;
                    }
                }
            }
        }
        $this->emitted[$event][] = array(
            "count" => $counter,
            "start_time" => $start_mtime,
            "end_time" => microtime(true)
        );
        return $counter;
    }
}
