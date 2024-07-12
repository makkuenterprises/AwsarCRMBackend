<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;

class GoogleCalendarService
{
    protected $client;
    

    public function __construct($token)
    {
        $this->client = new Google_Client();
        $this->client->setAccessToken($token);
    }

    public function createEvent($summary, $description, $start, $end, $attendees = [])
    {
        $service = new Google_Service_Calendar($this->client);
        $event = new Google_Service_Calendar_Event([
            'summary' => $summary,
            'description' => $description,
            'start' => new Google_Service_Calendar_EventDateTime(['dateTime' => $start]),
            'end' => new Google_Service_Calendar_EventDateTime(['dateTime' => $end]),
            'attendees' => $attendees,
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
        ]);

        $calendarId = 'primary';
        $event = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

        return $event;
    }
    
}