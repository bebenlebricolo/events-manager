<?php
// The following are included in the scope of this event date range picker
/* @var int $id */
/* @var EM_Event $EM_Event */
$name = "event_timeranges";
$locked = false;
$Timeranges = $EM_Event->get_timeranges();
if ( $EM_Event->is_recurring( true ) ) {
	$locked = true;
	$Timeranges->allow_timeranges = false;
	$Timeranges->get_first()->allow_timeslots = false;
} elseif ( $EM_Event->event_id && ( $Timeranges->count() > 1 || $Timeranges->get_first()->has_timeslots() ) ) {
	$locked = true;
}
include( em_locate_template('forms/timeranges/timeranges.php') );