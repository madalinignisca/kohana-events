<?php

/**
 * Class Kohana_Event
 *
 * Responsible for firing events
 */
class Kohana_Event
{
    /**
     * @var array
     */
    private static $events;

    /**
     * Fire an event
     *
     * @param object $event
     */
    public static function fire($event)
    {
        Kohana::$log->add(Log::INFO, '---> Event fired: '.get_class($event));

        self::load();

        $handlers = Arr::get(self::$events, get_class($event));
        if (!count($handlers)) {
            return;
        }

        foreach ($handlers as &$handler) {
            try {
                if (self::handle($event, $handler) === false) {
                    break;
                }
            } catch (Exception $e) {
                Kohana::$log->add(Log::ERROR,
                    get_class($event) . ' -> ' . get_class($handler) . ': ' .
                    $e->getMessage() .
                    self::getEventContext($event) .
                    PHP_EOL . $e->getTraceAsString()
                );
            }
        }
    }

    /**
     * Load event hooks
     *
     * @throws \Kohana_Exception
     */
    private static function load()
    {
        if (!self::$events) {
            self::$events = Kohana::$config->load('events.' . Kohana::$environment);
        }
    }

    /**
     * @param object               $event
     * @param string|Event_Handler $handler
     *
     * @return bool
     */
    private static function handle($event, &$handler)
    {
        self::boot_handler($handler);

        if (is_object($handler) && $handler instanceof Event_Handler) {
            Kohana::$log->add(Log::INFO, '---> Running event handler '.get_class($handler).' for event '.get_class($event));
            return $handler->handle($event);
        }

        return true;
    }

    /**
     * Boot event handler
     *
     * @param string|Event_Handler $handler
     */
    private static function boot_handler(&$handler)
    {
        if (is_string($handler) && class_exists($handler)) {
            $handler = new $handler();
        }
    }

    /**
     * Get event variables as string
     *
     * @param object $event
     *
     * @return string
     */
    private static function getEventContext($event)
    {
        $eventVars = get_object_vars($event);

        if (!count($eventVars)) {
            return '';
        }

        return PHP_EOL . 'Event context: ' . PHP_EOL . self::arrayToString($eventVars);
    }

    /**
     * Get array represented as string
     *
     * @param array $array
     * @param int   $level
     *
     * @return string
     */
    private static function arrayToString($array, $level = 0)
    {
        $lines = array(PHP_EOL);
        foreach ($array as $name => $value) {
            $lines[] = str_repeat('    ', $level).'[' . $name . '] => ' . self::toString($value, $level);
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Transform given value to string
     *
     * @param mixed $value
     * @param int   $level
     *
     * @return string
     */
    private static function toString($value, $level = 0)
    {
        if(is_array($value)) {
            return self::arrayToString($value, $level + 1);
        } else if (is_object($value) && $value instanceof ORM) {
            return self::toString(print_r($value->as_array(), true));
        }

        return $value;
    }
}