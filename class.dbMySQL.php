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
// Class DbMySQL handles data queries to MySQL / MariaDB
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

class DbMySQL
{
    private $errConnectDB = 'Connection Failed.<br>Host:<font color=red>$host</font><br>Error: %s\n';
    private $errQuerySQL = 'Query Failed.<br>Query:<font color=red>%s</font><br>Error: %s<br>\n';

    private $mysqli = null;
    private $result = null;
    private $results = null;

    /**
     * Constructor, open a new connection to the MySQL server
     *
     * @access         public
     * @param          string            $host            Host address (or IP)
     * @param          string            $username        Username for database connection
     * @param          string            $passwd          Password for database connection
     * @param          string            $dbname          Database name
     * @return         void
     */
    function __construct(string $host, string $username, string $passwd, string $dbname) {
        //
        // connect to the database
        //
        $this->mysqli = new mysqli($host, $username, $passwd, $dbname);
        if ($this->mysqli->connect_errno) {
            printf($this->errConnectDB, $this->mysqli->connect_error);
        }
    }

    /**
     * Destructor, Closes a previously opened database connection
     *
     * @access         public
     * @return         void
     */
    function __destruct() {
        $this->close();
    }

    /**
     * Returns the error code from last connect call
     *
     * @access         public
     * @return         int               Error code from last connect call
     */
    function connected() {
        return !($this->mysqli->connect_errno);
    }

    /**
     * Closes a previously opened database connection
     *
     * @access         public
     * @return         void
     */
    function close() {
        $this->free();
        $this->freeall();
        if ($this->mysqli) {
            $this->mysqli->close();
            $this->mysqli = null;
        }
    }

    /**
     * Frees the memory associated with a result
     *
     * @access         public
     * @return         void
     */
    function free() {
        if ($this->result) {
            $this->result->free();
            $this->result = null;
        }
    }

    /**
     * Frees the memory associated with all stored results
     *
     * @access         public
     * @return         void
     */
    function freeall() {
        if ($this->results) {
            foreach($this->results as $result) {
                if ($result) {
                    $result->free();
                    $result = null;
                }
            }
        }
    }

    /**
     * Store the memory associated with a result
     *
     * @access         public
     * @param          string            $alias           Identify stored results
     * @return         void
     */
    function store(string $alias) {
        if ($this->result) {
            if (isset($this->results[$alias])) {
                $this->results[$alias]->free();
                $this->results[$alias] = null;
            }
            $this->results[$alias] = $this->result;
            $this->result = null;
        }
    }

    /**
     * Restore the memory associated with a result
     *
     * @access         public
     * @param          string            $alias           Identify stored results
     * @return         void
     */
    function restore(string $alias) {
        if (isset($this->results[$alias])) {
            $this->free();
            $this->result = $this->results[$alias];
            $this->results[$alias] = null;
        }
    }

    /**
     * Escapes special characters in a string for use in an SQL statement
     *
     * @access         public
     * @param          string            $escapestr       The string to be escaped
     * @return         string            Escaped string
     */
    function escapeString (string $escapestr) {
        return $this->mysqli->real_escape_string($escapestr);
    }

    /**
     * Performs a query on the database
     *
     * @access         public
     * @param          string            $query           The query, as a string
     * @param          boolean           $volatile        If true, drop mysqli_result object
     * @return         mixed             true/false when not volatile, mysqli_result object otherwise
     */
    function query(string $query, bool $volatile=false) {
        $result = $this->mysqli->query($query);
        if ($result) {
            if ($volatile) {
                return $result;
            } else {
                $this->result = $result;
                return true;
            }

        } else {
            printf($this->errQuerySQL, $query, $this->mysqli->error);
            return false;
        }
    }

    /**
     * Check if MySQL table exists
     *
     * @access         public
     * @param          string            $table           Table to check
     * @return         boolean           true/false according to existing table
     */
    function tableExists(string $table) {
        $query = "SHOW TABLES LIKE '".$table."'";
        $result = $this->query($query, true);
        if ($result) {
            $exists = $result->num_rows;
            $result->free();
            $result = null;
        }
        if ($exists) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Perform query and return a single value
     *
     * @access         public
     * @param          string            $query           The query, as a string
     * @param          string            $index           Which field to return
     * @return         mixed             desired value
     */
    function getValue(string $query, int $index=0) {
        $result = $this->query($query, true);
        if ($result) {
            $datarow = $result->fetch_array();
            $result->free();
            $result = null;

            return $datarow[$index];
        } else {
            return null;
        }
    }

    /**
     * Perform query and return its result, keep mysqli_result object for later use
     *
     * @access         public
     * @param          string            $query           The query, as a string
     * @return         array             associative array of strings representing the fetched row
     */
    function getRows(string $query) {
        if ($this->query($query)) {
            $datarow = $this->result->fetch_assoc();
            return $datarow;
        } else {
            return null;
        }
    }

    /**
     * Perform query and return its result, destroy mysqli_result object
     *
     * @access         public
     * @param          string            $query           The query, as a string
     * @return         array             associative array of strings representing the fetched row
     */
    function getRow(string $query) {
        $datarow = $this->getRows($query);
        if ($datarow) {
            $this->free();

            return $datarow;
        } else {
            return null;
        }
    }

    /**
     * Return next result row as an associative array
     *
     * @access         public
     * @return         array             associative array of strings representing the next fetched row
     */
    function getNextRow() {
        $datarow = $this->result->fetch_assoc();
        return $datarow;
    }

    /**
     * Adjusts the result pointer to an arbitrary row in the result
     *
     * @access         public
     * @param          int               $position        The field offset
     * @return         boolean           true/false on success or failure
     */
    function seekRow(int $position) {
        if ($this->result) {
            return $this->result->data_seek($position);
        } else {
            return false;
        }
    }

    /**
     * Gets the number of rows in a result
     *
     * @access         public
     * @return         int               number of rows in the result set
     */
    function getRowsCount() {
        if ($this->result) {
            return $this->result->num_rows;
        } else {
            return 0;
        }
    }
}

?>
