<?php
namespace EM\Event;
use EM_Event;

/**
 * Handles timeslot saving and retrieval for an event, which additionally saves individual timeslots into a separate table.
 *
 * @property int $length The number of timeslots in this collection.
 */
class Timerange extends \EM\Timerange {

	/**
	 * @var EM_Event
	 */
	protected $event;
	protected $event_id;
	public $timeslots = [];

	public function __construct( $data = false, $EM_Event = null ) {
		parent::__construct($data);
		if ( $EM_Event instanceof EM_Event ) {
			$this->event = $EM_Event;
			$this->allow_timeslots = $EM_Event->get_option( 'dbem_event_timeslots', true );
		}
		// deactivate dynamic timeranges if disabled
		$this->allow_timeslots = (bool) $this->event->get_option('dbem_event_timeranges_advanced');
	}

	/**
	 * Loads timeslots belonging to this specific range for this event.
	 * @param $reload
	 *
	 * @return Timeslot[]
	 */
	public function load_timeslots( $reload = false ) {
		global $wpdb;
		if ( !$this->timeslots || $reload ) {
			$this->timeslots = [];
			if ( $this->event->event_id > 0 ) {
				// get timeslots from DB
				$timeslots_data = $wpdb->get_results( "SELECT * FROM " . EM_EVENT_TIMESLOTS_TABLE . " WHERE timerange_id=" . absint($this->timerange_id) . " AND event_id=" . absint( $this->event->event_id ), ARRAY_A );
				if ( !$timeslots_data ) {
					// prefill with start and end dates of the event
					$timeslots_data = [[
						'timeslot_all_day' => $this->timerange_all_day,
						'timeslot_start' => $this->timerange_start,
						'timeslot_end' => $this->timerange_end,
						'timeslot_id' => null,
					]];
				}
				foreach ( $timeslots_data as $timeslot_data ) {
					$this->timeslots[ $timeslot_data['timeslot_id'] ] = $this->get_timeslot( $timeslot_data );
				}
			}
		}
		return $this->timeslots;
	}

	public function get_event() {
		if ( !($this->event instanceof EM_Event) && $this->event_id ) {
			$this->event = em_get_event( $this->event_id );
		}
		return $this->event;
	}

	public function get_timeslot( $data ) {
		return new Timeslot( $data, $this->event );
	}

	/**
	 * Generates an array of timeranges based on current timerange rules.
	 * @return string[]
	 */
	public function get_timeslots() {
		$this->load_timeslots();
		return $this->timeslots;
	}
}