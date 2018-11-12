<?PHP
////////////////////////////////////////////////////
//
// WeatherOffice
//
// https://github.com/BmdOnline/WeatherOffice
//
// Copyright (C) 09/2018
//    BmdOnline,
//
// See COPYING for license info
//
////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// Class Database handles data queries
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
CREATE TABLE `ws2300_weather` (
  `timestamp` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rec_date` DATE NOT NULL DEFAULT '0000-00-00',
  `rec_time` TIME NOT NULL DEFAULT '00:00:00',
  `temp_in` decimal(4,1) NOT NULL DEFAULT '0.0',
  `temp_out` decimal(4,1) NOT NULL DEFAULT '0.0',
  `dewpoint` decimal(4,1) NOT NULL DEFAULT '0.0',
  `rel_hum_in` tinyint(3) NOT NULL DEFAULT '0',
  `rel_hum_out` tinyint(3) NOT NULL DEFAULT '0',
  `wind_speed` decimal(3,1) NOT NULL DEFAULT '0.0',
  `wind_angle` decimal(4,1) NOT NULL DEFAULT '0.0',
  `wind_direction` char(3) NOT NULL DEFAULT '',
  `wind_chill` decimal(4,1) NOT NULL DEFAULT '0.0',
  `rain_1h` decimal(3,1) NOT NULL DEFAULT '0.0',
  `rain_24h` decimal(3,1) NOT NULL DEFAULT '0.0',
  `rain_total` decimal(5,1) NOT NULL DEFAULT '0.0',
  `rel_pressure` decimal(5,1) NOT NULL DEFAULT '0.0',
  `tendency` varchar(7) NOT NULL DEFAULT '',
  `forecast` varchar(6) NOT NULL DEFAULT '',
  PRIMARY KEY (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

require_once 'class.dbMySQL.php';

class DATABASE
{
    private $tableWeather = "ws2300_weather";
    private $tableCache = "ws2300_cache";
    private $tableMinMaxAvg = "ws2300_MinMaxAvg";
    private $tableAdditionalSensors = "ws2300_additionalsensors";
    private $tableAdditionalValues = "ws2300_additionalvalues";

    private $currentDB;

    function __construct(string $host, string $username, string $passwd, string $dbname) {
        //
        // connect to the database
        //
        $this->currentDB = new dbMySQL($host, $username, $passwd, $dbname);
    }

    function __destruct() {
        $this->close();
    }

    function connected() {
        return $this->currentDB->connected();
    }

    function close() {
        $this->currentDB->close();
    }

    function free() {
        $this->currentDB->free();
    }

    function freeall() {
        $this->currentDB->freeall();
    }

    function store($name) {
        $this->currentDB->store($name);
    }

    function restore($name) {
        $this->currentDB->restore($name);
    }

    function getNextRow() {
        return $this->currentDB->getNextRow();
    }

    function seekRow($position) {
        $this->currentDB->seekRow($position);
    }

    function getRowsCount() {
        return $this->currentDB->getRowsCount();
    }

    private function _parseField($field, $table, $family=null) {
        $lstTables = array($this->tableWeather, $this->tableAdditionalValues);
        $field = str_replace("`", "", $field);
        $field = trim($field);

        if ($family == null) {
            $family = $field;
        }
        $family = explode(".", strtolower($family));

        if (in_array($table, $lstTables)) {
            if ($field==null) {
                $newField = $family[0];
            } else {
                $newField = $field;
            }

            /*switch($family[0]) {
                case 'timestamp';
                        if (!isset($family[1])) {
                            if ($field==null) {
                                // field=null, replace with original field name
                                $newField = "timestamp";
                            } elseif ($field==$family[0]) {
                                // replace datetime with conversion to timestamp
                                $newField = "timestamp";
                            } else {
                                // replace datetime with conversion to timestamp, but using $field instead of datetime
                                $newField = $field;
                            }
                        } elseif ($family[1]=="rev") {
                            // replace datetime with conversion to timestamp, but using $field instead of datetime
                            $newField = $field;
                        }
                    break;
                case 'rec_date';
                            $newField = $family[0];
                    break;
                case 'rec_time';
                            $newField = $family[0];
                    break;
                case 'windspeed':
                            $newField = $family[0];
                    break;
                default:
                    $newField = $field;
                    break;
            }*/
        } else {
            $newField = $field;
        }
        return $newField;
    }

    private function _parseFields($fields, $table) {
        foreach(explode(",", $fields) as $field) {
            $newField = $this->_parseField($field, $table);
            if ($newField != "*") {
                $newField = $newField . " as " . $field;
            }
            $newFields[] = $newField;
        }
        return implode(", ", $newFields);
    }

    function _getFieldsFromPeriod($fields, $table, $starttime, $stoptime, $strict=true, $condition=null, $count=-1) {
        $fields = $this->_parseFields($fields, $table);
        $tsField = $this->_parseField(null, $table, "timestamp");
        $query = "select " . $fields;
        $query .= " from `$table`";

        $startVal = $this->_parseField($starttime, $table, "timestamp.rev");
        $stopVal = $this->_parseField($stoptime, $table, "timestamp.rev");

        if ($strict) {
            $query .= " where $tsField>$startVal";
            $query .= " and $tsField<$stopVal";
        } else {
            $query .= " where $tsField>=$startVal";
            $query .= " and $tsField<=$stopVal";
        }
        if ($condition) {
            $query .= "and " . $condition;
        }
        $query .= " order by " . $tsField;

        if ($count==1) {
            return $this->currentDB->getRow($query);
        } else {
            return $this->currentDB->getRows($query);
        }
    }

    /**
     * All queries related to weather table
     *
     */

    function getWeatherFirstDate() {
        $tsField = $this->_parseField(null, $this->tableWeather, "timestamp");
        $field = "min($tsField)";
        $field = $this->_parseField($field, $this->tableWeather, "timestamp");
        $query = "select " . $field;
        $query .=" from `$this->tableWeather`";

        return $this->currentDB->getValue($query);
    }

    function getWeatherLastDate() {
        $tsField = $this->_parseField(null, $this->tableWeather, "timestamp");
        $field = "max($tsField)";
        $field = $this->_parseField($field, $this->tableWeather, "timestamp");
        $query = "select " . $field;
        $query .=" from `$this->tableWeather`";

        return $this->currentDB->getValue($query);
    }

    function getWeatherFieldsFromDate($fields, $timestamp) {
        $fields = $this->_parseFields($fields, $this->tableWeather);
        $tsField = $this->_parseField(null, $this->tableWeather, "timestamp");
        $tsVal = $this->_parseField($timestamp, $this->tableWeather, "timestamp.rev");
        $query = "select " . $fields;
        $query .= " from `$this->tableWeather`";
        $query .= " where $tsField=$tsVal";

        return $this->currentDB->getRow($query);
    }

    function getWeatherFieldsFromPeriod($fields, $starttime, $stoptime, $strict=true, $count=-1) {
        $condition = null;
        return $this->_getFieldsFromPeriod($fields, $this->tableWeather, $starttime, $stoptime, $strict, $condition, $count);
    }

    function getWeatherFromDate($timestamp) {
        $fields = "timestamp, rec_date, rec_time,";
        $fields .= " `temp_in`, `temp_out`, `dewpoint`, `rel_hum_in`, `rel_hum_out`,";
        $fields .= " windspeed, `wind_angle`, `wind_direction`, `wind_chill`,";
        $fields .= " `rain_1h`, `rain_24h`, `rain_total`, `rel_pressure`, `tendency`, `forecast`";

        return $this->getWeatherFieldsFromDate($fields, $timestamp);
    }

    function getWeatherFromPeriod($starttime, $stoptime, $strict=true, $count=-1) {
        $fields = "timestamp, rec_date, rec_time,";
        $fields .= " `temp_in`, `temp_out`, `dewpoint`, `rel_hum_in`, `rel_hum_out`,";
        $fields .= " windspeed, `wind_angle`, `wind_direction`, `wind_chill`,";
        $fields .= " `rain_1h`, `rain_24h`, `rain_total`, `rel_pressure`, `tendency`, `forecast`";

        return $this->getWeatherFieldsFromPeriod($fields, $starttime, $stoptime, $strict, $count);
    }

    /**
     * All queries related to cache
     *
     */
    function haveCache() {
        return ($this->currentDB->tableExists($this->tableCache));
    }

    function createTableCache() {
        $query = "CREATE TABLE IF NOT EXISTS `$this->tableCache` (";
        $query .= " `startTime`  bigint(14) unsigned NOT NULL default '0',";
        $query .= " `stopTime`   bigint(14) unsigned NOT NULL default '0',";
        $query .= " `day`        int(10) unsigned NOT NULL default '0',";
        $query .= " `rows`       int(10) unsigned NOT NULL default '0',";
        $query .= " `accessTime` int(10) unsigned NOT NULL default '0',";
        $query .= " `value`      text default NULL,";
        $query .= " PRIMARY KEY (`startTime`,`stopTime`, `day`)) CHARSET=utf8;";

        if ($this->currentDB->query($query, true)) {
            return true;
        } else {
            return false;
        }
    }

    function storeCacheValues($startTime, $stopTime, $rows, $day, $stat) {
        $dataString = $this->currentDB->escapeString(serialize($stat));
        $now = time();

        $query = "REPLACE INTO `$this->tableCache`";
        $query .= " SET startTime=$startTime, stopTime=$stopTime,";
        $query .= " rows=$rows, day=$day, accessTime=$now, value=\"$dataString\"";

        if ($this->currentDB->query($query, true)) {
            return true;
        } else {
            return false;
        }
    }

    function getCacheValues($startTime, $stopTime, $rows, $day) {
        $query = "SELECT value FROM `$this->tableCache`";
        $query .= " WHERE startTime=$startTime AND stopTime=$stopTime";
        $query .= " AND day=$day AND rows=$rows";

        return $this->currentDB->getRows($query);
    }

    function updateCacheTime($startTime, $stopTime, $rows, $day) {
        $now = time();

        $query = "UPDATE `$this->tableCache`";
        $query .= " SET accessTime=$now";
        $query .= " WHERE startTime=$startTime AND stopTime=$stopTime";
        $query .= " AND day=$day AND rows=$rows";

        if ($this->currentDB->query($query, true)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * All queries related to additional MinMaxAvg
     *
     */
    function haveMinMaxAvg() {
        return ($this->currentDB->tableExists($this->tableMinMaxAvg));
    }

    function createTableMinMaxAvg($fields, $ops) {

        $columns[] = "`timestamp` bigint(14) DEFAULT '0'";
        $columns[] = "`type` char(10) NOT NULL DEFAULT '0'";
        foreach ($fields as $field) {
            $field = strtolower($field);
            foreach ($ops as $op) {
                $op = strtolower($op);
                switch($field) {
                    case 'rel_pressure';
                            $precision = "4,0";
                        break;
                    case 'wind_angle';
                            $precision = "4,1";
                        break;
                    case 'rain_total';
                            $precision = "5,1";
                        break;
                    default:
                        $precision="3,1";
                        break;
                }
                $columns[] = "${field}_$op decimal($precision) DEFAULT NULL";
            }
        }
        $columns[] = "PRIMARY KEY (`timestamp`)";
        $columnCreate = implode(", ", $columns);

        $query = "CREATE TABLE IF NOT EXISTS `$this->tableMinMaxAvg` (";
        $query .= $columnCreate;
        $query .= ") CHARSET=utf8;";
        //$query .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        if ($this->currentDB->query($query, true)) {
            return true;
        } else {
            return false;
        }
    }

    function calculateMinMaxAvg($fields, $Type, $tStampStart, $tStampLenght, $startTS) {
        foreach ($fields as $field)
        {
            if($field != "rain_total")
            {
                $srcField = $this->_parseField($field, $this->tableWeather);
                $arrFields[] = "ROUND(AVG($srcField),1) AS ${field}_avg";
                $arrFields[] = "ROUND(MIN($srcField),1) AS ${field}_min";
                $arrFields[] = "ROUND(MAX($srcField),1) AS ${field}_max ";
                $arrTarget[] = "${field}_avg";
                $arrTarget[] = "${field}_min";
                $arrTarget[] = "${field}_max";
            }
        }
        $queryFields = implode(", ", $arrFields);
        $targetCols = implode(", ", $arrTarget);

        $field = "timestamp";
        $timestamp = $this->_parseField($field, $this->tableWeather);

        $query = "REPLACE INTO `$this->tableMinMaxAvg`(timestamp, type, $targetCols)";
        $query .= " SELECT substr($timestamp, $tStampStart, $tStampLenght) as timestamp,";
        $query .= "  \"${Type}\" as Type, $queryFields";
        $query .= " FROM `$this->tableWeather`";
        $query .= " WHERE $timestamp > $startTS";
        $query .= " GROUP BY substr($timestamp, $tStampStart, $tStampLenght)";

        if ($this->currentDB->query($query)) {
            return true;
        } else {
            return false;
        }
    }

    function aggregateMinMaxAvg($fields, $Type, $tStampStart, $tStampLenght, $dataType) {
        foreach ($fields as $field)
        {
            $srcField = $this->_parseField($field, $this->tableMinMaxAvg);
            if($field == "rain_total" && $Type != 'MONTH')
            {
                $arrFields[] = "ROUND(SUM(${srcField}_avg),1) AS ${field}_avg";
                $arrFields[] = "ROUND(SUM(${srcField}_min),1) AS ${field}_min";
                $arrFields[] = "ROUND(SUM(${srcField}_max),1) AS ${field}_max ";
            }
            else
            {
                $arrFields[] = "ROUND(AVG(${srcField}_avg),1) AS ${field}_avg";
                $arrFields[] = "ROUND(MIN(${srcField}_min),1) AS ${field}_min";
                $arrFields[] = "ROUND(MAX(${srcField}_max),1) AS ${field}_max ";
            }

            $arrTarget[] = "${field}_avg";
            $arrTarget[] = "${field}_min";
            $arrTarget[] = "${field}_max";
        }
        $queryFields = implode(", ", $arrFields);
        $targetCols = implode(", ", $arrTarget);

        $field = "timestamp";
        $timestamp = $this->_parseField($field, $this->tableMinMaxAvg);

        $query = "REPLACE INTO `$this->tableMinMaxAvg`(timestamp, type, $targetCols)";
        $query .= " SELECT substr($timestamp, $tStampStart, $tStampLenght) as timestamp,";
        $query .= "  \"${Type}\" as Type, $queryFields";
        $query .= " FROM `$this->tableMinMaxAvg`";
        $query .= " WHERE type='$dataType'";
        $query .= " GROUP BY substr($timestamp, $tStampStart, $tStampLenght)";

        if ($this->currentDB->query($query)) {
            return true;
        } else {
            return false;
        }
    }

    function updateMinMaxAvg($tStampStart, $tStampLenght, $tRainStart, $tRainLenght, $startTS) {
        $field = "timestamp";
        $timestamp = $this->_parseField($field, $this->tableWeather);

        $query = "update `$this->tableMinMaxAvg` AS tar";
        $query .= " inner join";
        $query .= " (SELECT SUBSTR(YYYYDD,$tStampStart, $tStampLenght) AS timestamp,";
        $query .= " ROUND(AVG(rainfall),1) AS rain_total_avg,";
        $query .= " ROUND(MIN(rainfall),1) AS rain_total_min,";
        $query .= " ROUND(MAX(rainfall),1) AS rain_total_max";
        $query .= " FROM (select max(rain_total)-min(rain_total) AS rainfall,";
        $query .= "    substr($timestamp ,$tRainStart, $tRainLenght) AS YYYYDD";
        $query .= "   from `$this->tableWeather`";
        $query .= "   where $timestamp > $startTS";
        $query .= "   group by substr($timestamp ,$tRainStart, $tRainLenght)) AS T1";
        $query .= " GROUP BY SUBSTR(YYYYDD,$tStampStart, $tStampLenght))";
        $query .= " as sor on tar.timestamp = sor.timestamp";
        $query .= " set";
        $query .= "    tar.rain_total_avg=sor.rain_total_avg,";
        $query .= "    tar.rain_total_min=sor.rain_total_min,";
        $query .= "    tar.rain_total_max=sor.rain_total_max";

        if ($this->currentDB->query($query)) {
            return true;
        } else {
            return false;
        }
    }

    function getMinMaxAvgFirstDate($type) {
        $query = "select min(timestamp)";
        $query .=" from `$this->tableMinMaxAvg`";
        $query .= " where type='$type'";

        return $this->currentDB->getValue($query);
    }

    function getMinMaxAvgLastDate($type) {
        $query = "select max(timestamp)";
        $query .= " from `$this->tableMinMaxAvg`";
        $query .= " where type='$type'";

        return $this->currentDB->getValue($query);
    }

    function getMinMaxAvgExtremeValue($field, $op, $type) {
        $query = "select timestamp, $field";
        $query .= " from `$this->tableMinMaxAvg`";
        $query .=" where type='$type'";
        $query .= " and $field=(SELECT $op($field) FROM `$this->tableMinMaxAvg` WHERE type='$type')";

        return $this->currentDB->getRow($query);
    }

    function getMinMaxAvgFieldsFromType($fields, $type) {
        $fields = $this->_parseFields($fields, $this->tableMinMaxAvg);
        $query = "select " . $fields;
        $query .= " from `$this->tableMinMaxAvg`";
        $query .= " where type='$type'";

        return $this->currentDB->getRows($query);
    }

    function getMinMaxAvgFieldsFromDate($fields, $type, $timestamp) {
        $fields = $this->_parseFields($fields, $this->tableMinMaxAvg);
        $query = "select " . $fields;
        $query .= " from `$this->tableMinMaxAvg`";
        $query .= " where type='$type'";
        $query .= " and timestamp=$timestamp";

        return $this->currentDB->getRow($query);
    }

    function getMinMaxAvgFromDate($type, $timestamp) {
        $fields = "*";

        return $this->getMinMaxAvgFieldsFromDate($fields, $type, $timestamp);
    }

    function getMinMaxAvgMeanValues() {
        $query = "select substr(DAY,5,2) as MONTH,";
        $query .= " round(avg(DAY_TEMP_MAX),1) as MEAN_TEMP_MAX,";
        $query .= " round(avg(DAY_TEMP_MIN),1) as MEAN_TEMP_MIN";
        $query .= " FROM (SELECT SUBSTR(timestamp, 1,8) AS DAY,";
        $query .= "    MAX(temp_out_max) AS DAY_TEMP_MAX,";
        $query .= "    MIN(temp_out_min) AS DAY_TEMP_MIN";
        $query .= "    FROM `$this->tableMinMaxAvg`";
        $query .= "    WHERE Type='DAY'";
        $query .= "    GROUP BY substr(timestamp, 1,8)) AS T1";
        $query .= " group by substr(DAY,5,2)";

        return $this->currentDB->getRows($query);
    }

    function getMinMaxAvgRainDaysValues() {
        $query = "select substr(YYYYDD,5,2) as MONTH,";
        $query .= " round(avg(RAINDAYS)) as RAINDAYS_AVG";
        $query .= " from (SELECT COUNT(DAY) AS RAINDAYS, SUBSTR(DAY,1,6) AS YYYYDD";
        $query .= "    FROM (select DAY, RAINFALL";
        $query .= "        from (SELECT SUBSTR(timestamp,1,8) as DAY, type as Type, rain_total_max as rainfall";
        $query .= "            FROM `$this->tableMinMaxAvg`";
        $query .= "            GROUP BY substr(timestamp,1,8)) AS T1";
        $query .= "        where Type='DAY' and rainfall > 0) as T2";
        $query .= "    GROUP BY SUBSTR(DAY,1,6)) AS T3";
        $query .= " group by substr(YYYYDD,5,2)";
        $query .= " order by substr(YYYYDD,5,2);";

        return $this->currentDB->getRows($query);
    }

    /**
     * All queries related to additional sensors
     *
     */
    function haveSensors($active=null) {
        if ($this->currentDB->tableExists($this->tableAdditionalSensors)) {
            if ($active) {
                $this->listSensors($active);
                return $this->currentDB->getRowsCount();
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function listSensors($active=null) {
        $fields = "id, name, filename, linenumber, unit, Active";

        $fields = $this->_parseFields($fields, $this->tableAdditionalSensors);
        $query = "select " . $fields;
        $query .= " from `$this->tableAdditionalSensors`";
        if ($active) {
            $query .= " where active=$active";
        }
        $query .= " order by id";

        return $this->currentDB->query($query);
    }

    function addSensors($sensorId, $name, $filename, $linenumber, $unit) {
        $query = "INSERT INTO $this->tableAdditionalSensors";
        $query .= " Values($sensorId,";
        $query .= " \"$name\",";
        $query .= " \"$filename\",";
        $query .= " \"$linenumber\",";
        $query .= " \"$unit\", 1)";

        return $this->currentDB->query($query);
    }

    function getSensorLastId() {
        $query = "select max(id)";
        $query .=" from `$this->tableAdditionalSensors`";

        return $this->_getValue($query);
    }

    function getSensorFromId($sensorId) {
        $query = "select *";
        $query .= " from `$this->tableAdditionalSensors`";
        $query .= " where id=\"$sensorId\"";

        return $this->currentDB->getRows($query);
    }

    function addSensorsValue($sensorId, $timestamp, $value) {
        $query = "INSERT INTO $this->tableAdditionalValues";
        $query .= " Values($sensorId,";
        $query .= " \"$timestamp\",";
        $query .= " \"$value\")";

        return $this->currentDB->query($query);
    }

    function getSensorValuesFromPeriod($sensorId, $starttime, $stoptime, $strict=true, $count=-1) {
        $fields = "value, timestamp";
        $condition = " id=\"$sensorId\"";

        return $this->_getFieldsFromPeriod($fields, $this->tableAdditionalValues, $starttime, $stoptime, $strict, $condition, $count);
    }
}
?>
