<?php

/**
 * Interface Event_Handler
 *
 * Handles events
 */
interface Event_Handler {

    /**
     * Handle an event
     *
     * @param object $event
     *
     * @return bool|null
     */
    public function handle($event);
}