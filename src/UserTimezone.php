<?php

namespace BenComeau\UserTimeZone;

use Auth;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;

/**
 * @mixin Illuminate\Database\Eloquent\Model
 */
trait UserTimeZone
{
    /**
     * The timezone
     * 
     * @var $model
     */
    protected $user;

    public static function booUserTimeZone()
    {
        $this->user = Auth::user()->time_zone ?: config('app.timezone');
    }

	/**
	 * Overrides the default Illuminate\Database\Eloquent\Model method
	 * allowing the Carbon dates to be mutated to a user's timezone as
	 * set in a user's profile. Defaults to the config.app value
	 * 
	 * @param  mixed  $value
     * @return \Carbon\Carbon
	 */
	protected function asDateTime($value)
	{
		// If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
        	return $value;
        }

         // If the value is already a DateTime instance, we will just skip the rest of
         // these checks since they will be a waste of time, and hinder performance
         // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return new Carbon(
                $value->format('Y-m-d H:i:s.u'), $value->getTimeZone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        return Carbon::createFromTimestamp(strtotime($value))->timezone($this->timeZone);
	}
}