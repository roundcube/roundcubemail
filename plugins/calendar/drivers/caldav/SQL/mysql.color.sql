/**
$sql = "UPDATE `caldav_calendars` SET color = substring(MD5(RAND()), -6)";




UPDATE caldav_calendars
SET color = (
SELECT substring(MD5(RAND()), -6)
)
WHERE condition_column = 1;




*/

UPDATE `caldav_calendars` SET color = substring(MD5(RAND()), -6);