Kohana Events
==============

### Event system for Kohana framework ###

Version 1.0

Released on 2015-06-10

By Ernestas Kvedaras

Installation
------------

Clone this repository into Kohana `modules` directory.

Configuration
-------------

All events and their handlers have to be registered in `events.php` configuration file.
Configuration is devised into four parts for each environment, but you can add your own.
To register an event, put it's class name as an array key and it's handlers as values.

Example:

```php
return array(
    Kohana::PRODUCTION  => array(),
    Kohana::STAGING     => array(),
    Kohana::TESTING     => array(),
    Kohana::DEVELOPMENT => array(
        'Event_User_Registered' => array(
            'Event_Handler_User_SendWelcomeEmail',
            'Event_Handler_User_InformAdminAboutNewUser',
        ),
    ),
);
```

Usage
-----

* Create an event class

```php
/**
 * Class Event_User_Registered
 *
 * Fired when new user is registered
 */
class Event_User_Registered
{
    /**
     * @var Model_User
     */
    public $user;
    
    /**
     * @param Model_User $user
     */
    function __construct(Model_User $user)
    {
        $this->user = $user;
    }
}
```

* Create a handler class

```php
/**
 * Class Event_Handler_User_SendWelcomeEmail
 *
 * Sends an email notification to user about successful registration
 */
class Event_Handler_User_SendWelcomeEmail implements Event_Handler
{
    /**
     * Handle an event
     *
     * @param Event_User_Registered $event
     *
     * @return bool|null
     */
    public function handle($event)
    {
        // TODO send email
    }
}
```

* Fire event

```php
Event::fire(new Event_User_Registered($user));
```


All event handlers will be executed in the same order they are defined in `events.php` file.
By making `handle` method to return `false`, you can break the chain and no further handlers will be executed.

Extra
-----

There is an extra class `Event_Handler_Notification` for handling event notification easily.
However it requires Kohana emails module from https://github.com/shadowhand/email or you can adapt email sending for your needs.

To use this helper, simply make your handlers to extend this class and implement `formatSubject` and `getReceivers` methods.

Example:

```php
/**
 * Class Event_Handler_User_SendWelcomeEmail
 *
 * Sends an email notification to user about successful registration
 */
class Event_Handler_User_SendWelcomeEmail extends Event_Handler_Notification implements Event_Handler
{
    /**
     * Handle an event
     *
     * @param Event_User_Registered $event
     *
     * @return bool|null
     */
    public function handle($event)
    {
        $this->send($event, 'Welcome aboard user ' . $event->user->username . '!');
    }
    
    /**
     * Format email notification subject
     *
     * @param Event_User_Registered $event
     *
     * @return string
     */
    protected function formatSubject($event)
    {
        return 'Welcome ' . $user->username;
    }
    
    /**
     * Get notification receivers list
     *
     * @param Event_User_Registered $event
     *
     * @return array
     */
    protected function getReceivers($event)
    {
        return array(
            $event->user->email => $event->user->username
        );
    }
}
```