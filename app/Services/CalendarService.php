<?php

namespace App\Services;

use Beta\Microsoft\Graph\Model\Calendar;
use Beta\Microsoft\Graph\Model\Event;
use Illuminate\Support\Facades\Log;

class CalendarService extends MicrosoftGraphBaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUserCalendars($accessToken)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to fetch calendars.');
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $calendars = $this->graph->createRequest("GET", "/me/calendars")
                ->setReturnType(Calendar::class)
                ->execute();

            // Log::info('Fetched user calendars successfully.', ['calendars_count' => count($calendars)]);
            return $calendars;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException fetching calendars.', [
            //     'error_message' => $e->getMessage(),
            //     'response_body' => $e->getResponseBody(),
            // ]);
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception fetching calendars.', [
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return null;
        }
    }

    public function getUserEvents($accessToken, $calendarId)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to fetch events.');
            return null;
        }

        $this->graph->setAccessToken($accessToken);

        try {
            $events = $this->graph->createRequest("GET", "/me/calendars/{$calendarId}/events")
                ->setReturnType(Event::class)
                ->execute();

            // Log::info('Fetched events from calendar.', [
            //     'calendar_id' => $calendarId,
            //     'events_count' => count($events),
            // ]);

            return $events;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException fetching events.', [
            //     'calendar_id' => $calendarId,
            //     'error_message' => $e->getMessage(),
            //     'response_body' => $e->getResponseBody(),
            // ]);
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception fetching events.', [
            //     'calendar_id' => $calendarId,
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return null;
        }
    }


    public function createEvent($accessToken, array $eventDetails)
    {
        if (!$accessToken) {
            // Log::error('Access token is required to create an event.');
            return null;
        }

        $this->graph->setAccessToken($accessToken); // تهيئة رمز الوصول

        try {
            $event = $this->graph->createRequest("POST", "/me/events")
                ->attachBody($eventDetails)
                ->setReturnType(Event::class)
                ->execute();

            // Log::info('Event created via Microsoft Graph API.', ['event_id' => $event->getId()]);
            return $event;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException creating event.', [
            //     'event_details' => $eventDetails,
            //     'error_message' => $e->getMessage(),
            //     'response_body' => $e->getResponseBody(),
            // ]);
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception creating event.', [
            //     'event_details' => $eventDetails,
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return null;
        }
    }



    public function getEventDetails($accessToken, $eventId)
    {
        try {
            $this->graph->setAccessToken($accessToken);
            $event = $this->graph->createRequest("GET", "/me/events/{$eventId}")
                ->setReturnType(Event::class)
                ->execute();

            return $event;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException fetching event details.', [
            //     'event_id' => $eventId,
            //     'error_message' => $e->getMessage(),
            //     'response_body' => $e->getResponseBody(),
            // ]);
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception fetching event details.', [
            //     'event_id' => $eventId,
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return null;
        }
    }


    public function updateEvent($accessToken, $eventId, $eventData)
    {
        try {
            $this->graph->setAccessToken($accessToken);
            $updatedEvent = $this->graph->createRequest("PATCH", "/me/events/{$eventId}")
                ->attachBody($eventData)
                ->setReturnType(Event::class)
                ->execute();

            return $updatedEvent;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException updating event.', [
            //     'event_id' => $eventId,
            //     'error_message' => $e->getMessage(),
            //     'response_body' => $e->getResponseBody(),
            // ]);
            return null;
        } catch (\Exception $e) {
            // Log::error('Exception updating event.', [
            //     'event_id' => $eventId,
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return null;
        }
    }


    public function deleteEvent($accessToken, $eventId)
    {
        try {
            $this->graph->setAccessToken($accessToken);
            $this->graph->createRequest("DELETE", "/me/events/{$eventId}")
                ->execute();

            return true;
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            // Log::error('GraphException deleting event.', [
            //     'event_id' => $eventId,
            //     'error_message' => $e->getMessage(),
            //     'response_body' => $e->getResponseBody(),
            // ]);
            return false;
        } catch (\Exception $e) {
            // Log::error('Exception deleting event.', [
            //     'event_id' => $eventId,
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ]);
            return false;
        }
    }
}
