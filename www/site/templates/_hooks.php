// _hooks.php

// after saving an event, make sure it has a amenities child (a collection)
// which contains pages that form a matrix of dates in the range
// and amenities that are not closed

function ensure_event_amenities($event) {
	$p = $event->arguments('page');
	$this->message("You saved page with date_from: {$p->date_to} and date_to: {$p->date_to}");

} 

$this->addHookAfter('Pages::save', $this, 'ensure_event_amenities');

$this->message("we're here!");