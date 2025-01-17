<?php

namespace CommonsBooking\Model;

use CommonsBooking\Wordpress\CustomPostType\Timeframe;
use DateTime;
use Exception;
use WP_Post;

class Day {

	/**
	 * @var
	 */
	protected $date;

	/**
	 * @var array
	 */
	protected $locations;

	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var array|mixed
	 */
	protected $types;

	/**
	 * Day constructor.
	 *
	 * @param string $date
	 * @param array $locations
	 * @param array $items
	 * @param array $types
	 */
	public function __construct( string $date, array $locations = [], array $items = [], array $types = [] ) {
		$this->date      = $date;
		$this->locations = array_map( function ( $location ) {
			return $location instanceof WP_Post ? $location->ID : $location;
		}, $locations );
		$this->items     = array_map( function ( $item ) {
			return $item instanceof WP_Post ? $item->ID : $item;
		}, $items );

		$this->types = $types;
	}

	/**
	 * @return false|string
	 */
	public function getDayOfWeek() {
		return date( 'w', strtotime( $this->getDate() ) );
	}

	/**
	 * @return DateTime
	 * @throws Exception
	 */
	public function getDateObject(): DateTime {
		return new DateTime( $this->getDate() );
	}

	/**
	 * @return string
	 */
	public function getDate(): string {
		return $this->date;
	}

	/**
	 * Returns formatted date.
	 *
	 * @param $format string Date format
	 *
	 * @return false|string
	 */
	public function getFormattedDate( string $format ) {
		return date( $format, strtotime( $this->getDate() ) );
	}

	/**
	 * Returns name of the day.
	 * @return false|string
	 */
	public function getName() {
		return date( 'l', strtotime( $this->getDate() ) );
	}

	/**
	 * Returns grid for the day defined by the timeframes.
	 * @return array
	 * @throws Exception
	 */
	public function getGrid(): array {
		$timeFrames = \CommonsBooking\Repository\Timeframe::get(
			$this->locations,
			$this->items,
			$this->types,
			$this->getDate(),
			false,
			null,
			[ 'publish', 'confirmed', 'unconfirmed' ]
		);

		// check if user is allowed to book this timeframe and remove unallowed timeframes from array
		foreach ( $timeFrames as $key => $timeframe ) {
			if ( ! commonsbooking_isCurrentUserAllowedToBook( $timeframe->ID ) ) {
				unset( $timeFrames[ $key ] );
			}
		}

		return $this->getTimeframeSlots( $timeFrames );
	}

	/**
	 * Returns the slot number for specific timeframe and time.
	 *
	 * @param DateTime $date
	 * @param $grid
	 *
	 * @return float|int
	 */
	protected function getSlotByTime( DateTime $date, $grid ) {
		$hourSlots   = $date->format( 'H' ) / $grid;
		$minuteSlots = $date->format( 'i' ) / 60 / $grid;

		return $hourSlots + $minuteSlots;
	}

	/**
	 * Returns start-slot id.
	 *
	 * @param $grid
	 * @param $timeframe
	 *
	 * @return float|int
	 * @throws Exception
	 */
	protected function getStartSlot( $grid, $timeframe ) {
		// Timeframe
		$fullDay   = get_post_meta( $timeframe->ID, 'full-day', true );
		$startTime = $this->getStartTime( $timeframe );
		$startSlot = $this->getSlotByTime( $startTime, $grid );

		// If we have a overbooked day, we need to mark all slots as booked
		if ( get_post_meta( $timeframe->ID, 'type', true ) == Timeframe::BOOKING_ID ) {

			$booking          = new Booking( $timeframe );
			$startDateBooking = intval( $booking->getStartDate() );
			$startDateDay     = strtotime( $this->getDate() );

			// if booking starts on day before, we set startslot to 0
			if ( $startDateBooking < $startDateDay ) {
				$startSlot = 0;
			}
		}

		// If timeframe is full day, it starts at slot 0
		if ( $fullDay ) {
			$startSlot = 0;
		}

		return $startSlot;
	}

	/**
	 * Returns end-slot id.
	 *
	 * @param $slots
	 * @param $grid
	 * @param $timeframe
	 *
	 * @return float|int
	 */
	protected function getEndSlot( $slots, $grid, $timeframe ) {
		// Timeframe
		$fullDay = get_post_meta( $timeframe->ID, 'full-day', true );
		$endTime = $this->getEndTime( $timeframe );
		$endDate = $this->getEndDate( $timeframe );

		// Slots
		$endSlot = count( $slots );

		// If timeframe isn't configured as full day
		if ( ! $fullDay ) {
			$endSlot = $this->getSlotByTime( $endTime, $grid );
		}

		// If we have a overbooked day, we need to mark all slots as booked
		if ( ! Timeframe::isOverBookable( $timeframe ) ) {
			// Check if timeframe ends after the current day
			if ( strtotime( $this->getFormattedDate( 'd.m.Y 23:59' ) ) < $endDate->getTimestamp() ) {
				$endSlot = count( $slots );
			}
		}

		return $endSlot;
	}

	/**
	 * Returns repetition-start DateTime.
	 *
	 * @param $timeframe
	 *
	 * @return DateTime
	 */
	protected function getStartDate( $timeframe ): DateTime {
		$startDateString = get_post_meta( $timeframe->ID, 'repetition-start', true );
		$startDate       = new DateTime();
		$startDate->setTimestamp( $startDateString );

		return $startDate;
	}

	/**
	 * Returns start-time DateTime.
	 *
	 * @param $timeframe
	 *
	 * @return DateTime
	 */
	protected function getStartTime( $timeframe ): DateTime {
		$startDateString = get_post_meta( $timeframe->ID, 'repetition-start', true );
		$startTimeString = get_post_meta( $timeframe->ID, 'start-time', true );
		$startDate       = new DateTime();
		$startDate->setTimestamp( $startDateString );
		if ( $startTimeString ) {
			$startTime = new DateTime();
			$startTime->setTimestamp( strtotime( $startTimeString ) );
			$startDate->setTime( $startTime->format( 'H' ), $startTime->format( 'i' ) );
		}

		return $startDate;
	}

	/**
	 * Returns end-date DateTime.
	 *
	 * @param $timeframe
	 *
	 * @return DateTime
	 */
	protected function getEndDate( $timeframe ): DateTime {
		$endDateString = intval( get_post_meta( $timeframe->ID, \CommonsBooking\Model\Timeframe::REPETITION_END, true ) );
		$endDate       = new DateTime();
		$endDate->setTimestamp( $endDateString );

		return $endDate;
	}

	/**
	 * Returns start-time DateTime.
	 *
	 * @param $timeframe
	 *
	 * @return DateTime
	 * @throws Exception
	 */
	protected function getEndTime( $timeframe ): DateTime {
		$endDateString = $this->getDateObject()->getTimestamp();
		$endTimeString = get_post_meta( $timeframe->ID, 'end-time', true );
		$endDate       = new DateTime();
		$endDate->setTimestamp( $endDateString );
		if ( $endTimeString ) {
			$endTime = new DateTime();
			$endTime->setTimestamp( strtotime( $endTimeString ) );
			$endDate->setTime( $endTime->format( 'H' ), $endTime->format( 'i' ) );
		}

		return $endDate;
	}

	/**
	 * Checks if timeframe is relevant for current day/date.
	 *
	 * @param $timeframe
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function isInTimeframe( $timeframe ): bool {
		$repetitionType = get_post_meta( $timeframe->ID, 'timeframe-repetition', true );

		if (
			$repetitionType && $repetitionType !== "norep"
		) {
			switch ( $repetitionType ) {
				// Weekly Rep
				case "w":
					$dayOfWeek         = intval( $this->getDateObject()->format( 'w' ) );
					$timeframeWeekdays = get_post_meta( $timeframe->ID, 'weekdays', true );

					// Because of different day of week calculation we need to recalculate
					if ( $dayOfWeek == 0 ) {
						$dayOfWeek = 7;
					}

					if ( is_array( $timeframeWeekdays ) && in_array( $dayOfWeek, $timeframeWeekdays ) ) {
						return true;
					} else {
						return false;
					}

				// Monthly Rep
				case "m":
					$dayOfMonth               = intval( $this->getDateObject()->format( 'j' ) );
					$timeframeStartDayOfMonth = $this->getStartDate( $timeframe )->format( 'j' );

					if ( $dayOfMonth == $timeframeStartDayOfMonth ) {
						return true;
					} else {
						return false;
					}

				// Yearly Rep
				case "y":
					$date          = intval( $this->getDateObject()->format( 'dm' ) );
					$timeframeDate = $this->getStartDate( $timeframe )->format( 'dm' );
					if ( $date == $timeframeDate ) {
						return true;
					} else {
						return false;
					}
			}
		}

		return true;
	}

	/**
	 * Maps timeframes to timeslots.
	 *
	 * @param $slots
	 * @param $timeframes
	 *
	 * @throws Exception
	 */
	protected function mapTimeFrames( &$slots, $timeframes ) {
		$grid = 24 / count( $slots );

		// Iterate through timeframes and fill slots
		foreach ( $timeframes as $timeframe ) {
			// Check for repetition timeframe selected days
			if ( ! $this->isInTimeframe( $timeframe ) ) {
				continue;
			}

			// Slots
			$startSlot = $this->getStartSlot( $grid, $timeframe );
			$endSlot   = $this->getEndSlot( $slots, $grid, $timeframe );

			// Add timeframe to relevant slots
			while ( $startSlot < $endSlot ) {
				// Set locked property
				$timeframe->locked = Timeframe::isLocked( $timeframe );

				if ( ! array_key_exists( 'timeframe', $slots[ $startSlot ] ) || ! $slots[ $startSlot ]['timeframe'] ) {
					$slots[ $startSlot ]['timeframe'] = $timeframe;
				} else {
					$slots[ $startSlot ]['timeframe'] = Timeframe::getHigherPrioFrame( $timeframe, $slots[ $startSlot ]['timeframe'] );
				}

				$startSlot ++;
			}
		}

		$this->sanitizeSlots( $slots );
	}

	/**
	 * Remove empty and merge connected slots.
	 *
	 * @param $slots
	 */
	protected function sanitizeSlots( &$slots ) {
		$this->removeEmptySlots( $slots );

		// merge multiple slots if they are of same type
		foreach ( $slots as $slotNr => $slot ) {
			if ( ! array_key_exists( $slotNr - 1, $slots ) ) {
				continue;
			}
			$slotBefore = $slots[ $slotNr - 1 ];

			// If Slot before is of same timeframe and we have no hourly grid, we merge them.
			if (
				$slotBefore &&
				$slotBefore['timeframe']->ID == $slot['timeframe']->ID &&
				(
					get_post_meta( $slot['timeframe']->ID, 'full-day', true ) == 'on' ||
					get_post_meta( $slot['timeframe']->ID, 'grid', true ) == 0
				)
			) {
				// Take over start time from slot before
				$slots[ $slotNr ]['timestart']      = $slotBefore['timestart'];
				$slots[ $slotNr ]['timestampstart'] = $slotBefore['timestampstart'];

				// unset timeframe from slot before
				unset( $slots[ $slotNr - 1 ]['timeframe'] );
			}
		}

		$this->removeEmptySlots( $slots );
	}

	/**
	 * remove slots without timeframes
	 *
	 * @param $slots
	 */
	protected function removeEmptySlots( &$slots ) {
		// remove slots without timeframes
		foreach ( $slots as $slotNr => $slot ) {
			if ( ! array_key_exists( 'timeframe', $slot ) || ! ( $slot['timeframe'] instanceof WP_Post ) ) {
				unset( $slots[ $slotNr ] );
			}
		}
	}

	/**
	 * Returns array of timeslots filled with timeframes.
	 *
	 * @param $timeframes
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function getTimeframeSlots( $timeframes ): array {
		$slots       = [];
		$slotsPerDay = 24;

		// Init Slots
		for ( $i = 0; $i < $slotsPerDay; $i ++ ) {
			$slots[ $i ] = [
				'timestart'      => date( get_option( 'time_format' ), $i * ( ( 24 / $slotsPerDay ) * 3600 ) ),
				'timeend'        => date( get_option( 'time_format' ), ( $i + 1 ) * ( ( 24 / $slotsPerDay ) * 3600 ) ),
				'timestampstart' => $this->getSlotTimestampStart( $slotsPerDay, $i ),
				'timestampend'   => $this->getSlotTimestampEnd( $slotsPerDay, $i )
			];
		}

		$this->mapTimeFrames( $slots, $timeframes );

		return $slots;
	}

	/**
	 * Returns timestamp when $slotNr starts.
	 *
	 * @param $slotsPerDay
	 * @param $slotNr
	 *
	 * @return false|float|int
	 */
	protected function getSlotTimestampStart( $slotsPerDay, $slotNr ) {
		return strtotime( $this->getDate() ) + ( $slotNr * ( ( 24 / $slotsPerDay ) * 3600 ) );
	}

	/**
	 * Returns timestamp when $slotNr ends.
	 *
	 * @param $slotsPerDay
	 * @param $slotNr
	 *
	 * @return false|float|int
	 */
	protected function getSlotTimestampEnd( $slotsPerDay, $slotNr ) {
		return strtotime( $this->getDate() ) + ( ( $slotNr + 1 ) * ( ( 24 / $slotsPerDay ) * 3600 ) ) - 1;
	}

}
