<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Database controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Database_Contoller
{

    /**
     * Tables to ignore cache clear notice. Use controller.admin hook to add to this
     *
     * @var array
     */
    public $cache_notice_tables_ingore = array(
        'CubeCart_access_log',
        'CubeCart_admin_error_log',
        'CubeCart_admin_log',
        'CubeCart_admin_users',
        'CubeCart_blocker',
        'CubeCart_email_log',
        'CubeCart_extension_info',
        'CubeCart_history',
        'CubeCart_permissions',
        'CubeCart_request_log',
        'CubeCart_saved_cart',
        'CubeCart_search',
        'CubeCart_sessions',
        'CubeCart_system_error_log',
        'CubeCart_transactions'
    );

    /**
     * Do we have a connection?
     *
     * @var bool
     */
    public $connected = false;

    /**
     * Array of page one names for canonical skip
     *
     * @var array
     */
    public $page_one = array();
    /**
     * Allowed exceptions
     *
     * @var array
     */
    protected $_allowed_exceptions = array('CURRENT_TIMESTAMP', 'NOW()', 'offline_capture', 'NULL');
    /**
     * Was it a cached query
     *
     * @var bool
     */
    protected $_cached   = false;
    /**
     * Cache block functions
     *
     * @var string (pipe separated)
     */
    protected $_cache_block_functions   = 'UNIX_TIMESTAMP()|NOW()';

    /**
     * DB connection
     *
     * @var id
     */
    protected $_db_connect_id = null;
    /**
     * Number of rows found
     *
     * @var $_found_rows int
     */
    protected $_found_rows  = null;
    /**
     * Store prefix
     *
     * @var string
     */
    protected $_prefix   = '';
    /**
     * Query to execute
     *
     * @var $_query string
     */
    protected $_query   = false;
    /**
     * Query run time
     *
     * @var $_query_time float
     */
    protected $_query_time  = null;
    /**
     * Query result
     *
     * @var $_result
     */
    protected $_result   = null;
    /**
     * Query allowed columns memory cache
     *
     * @var array
     */
    protected $_allowedColumns = array();
    /**
     * Database Engine in use
     *
     * @var string
     */
    protected $_db_engine = '';

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    protected function __construct()
    {
    }

    public function __destruct()
    {
    }

    //=====[ Public ]=======================================

    /**
     * Get table checksum
     *
     * @param string $table
     *
     * @return string/false
     */
    public function checksum($table) {
        $this->_query = "CHECKSUM TABLE `{$this->_prefix}$table`;";
        $this->_execute();
        if(isset($this->_result[0]['Checksum'])) {
            return $this->_result[0]['Checksum'];
        } else {
            return false;
        }
    }

    /**
     * Display column sort
     *
     * @param string $column_name
     * @param string $display_text
     * @param string $order_by
     * @param string $current_page
     * @param array_type $current_sort
     * @param string $anchor
     * @return string
     */
    public function column_sort($column_name, $display_text, $order_by = 'sort', $current_page, $current_sort = false, $anchor = false)
    {
        $link   = "$current_page&{$order_by}[$column_name]=";

        if ($anchor) {
            $anchor = '#'.$anchor;
        }

        $text_desc = sprintf($GLOBALS['language']->form['sort_by_desc'], strtolower($display_text));
        $text_asc = sprintf($GLOBALS['language']->form['sort_by_asc'], strtolower($display_text));

        if (isset($current_sort[$column_name]) && $current_sort[$column_name] == 'ASC') {
            $html_out  = '<a href="'.$link.'DESC'.$anchor.'" class="clearfix"><div class="left">'.$display_text.'</div><div class="right"><i class="fa fa-sort-asc" title="'.$text_asc.'"></i></div></a>';
        } elseif (isset($current_sort[$column_name]) && $current_sort[$column_name] == 'DESC') {
            $html_out  = '<a href="'.$link.'ASC'.$anchor.'" class="clearfix"><div class="left">'.$display_text.'</div><div class="right"><i class="fa fa-sort-desc" title="'.$text_desc.'"></i></div></a>';
        } else {
            $html_out  = '<a href="'.$link.'ASC'.$anchor.'" class="clearfix"><div class="left">'.$display_text.'</div><div class="right"><i class="fa fa-sort" title="'.$text_desc.'"></i></div></a>';
        }
        
        return  $html_out;
    }
    
    /**
     * Query count a field
     *
     * @param string $table
     * @param string $field
     * @param string $where
     *
     * @return bool
     */
    public function count($table = false, $field = false, $where = false)
    {
        if (!stristr($table, 'JOIN')) {
            $wrapper = '`';
            $prefix = $this->_prefix;
        } else {
            $wrapper = '';
            $prefix = '';
        }

        if (!empty($table)) {
            $allowed = $this->getFields($table);

            $field = (in_array($field, $allowed) && !is_numeric($field)) ? $field : '*';

            $this->_query = "SELECT COUNT($field) AS Count FROM $wrapper$prefix$table$wrapper ".$this->where($table, $where).';';
            $this->_execute();
            if ($this->_result && isset($this->_result[0]['Count'])) {
                return ((int)$this->_result[0]['Count'] > 0) ? (int)$this->_result[0]['Count'] : false;
            }
        }

        return false;
    }

    public function debug()
    {
        $ret = '[QUERY] - '.$this->_query."\n";
        return $ret;
    }

    /**
     * Delete from a table
     *
     * @param string $table
     * @param string $where
     * @param string $limit
     * @param bool $purge
     * @return bool
     */
    public function delete($table, $where, $limit = '', $purge = true)
    {
        if (!empty($limit)) {
            $limit = "LIMIT $limit";
        }
        $this->_query = "DELETE FROM `{$this->_prefix}$table` ".$this->where($table, $where)." $limit;";
        $this->_execute(false);
        $affected = ($this->affected() > 0);
        $this->_clearCacheNotice($purge, $affected, $table);
        return ($affected) ? true : false;
    }

    /**
     * Backup SQL
     *
     * @param bool $dropTables
     * @param bool $incStructure
     * @param bool $incRows
     *
     * @return string
     */
    public function doSQLBackup($dropTables = false, $incStructure = true, $incRows = true, $file_name, $compress = false, $all_tables = false)
    {
        $open_text = "-- --------------------------------------------------------\n-- CubeCart SQL Dump\n-- version ".CC_VERSION."\n-- http://www.cubecart.com\n-- \n-- Host: ".$GLOBALS['config']->get('config', 'dbhost')."\n-- Generation Time: ".strftime($GLOBALS['config']->get('config', 'time_format'), time())."\n-- Server version: ".$this->serverVersion()."\n-- PHP Version: ".phpversion()."\n-- \n-- Database: `".$GLOBALS['config']->get('config', 'dbdatabase')."`\n";
        
        $fp = fopen($file_name, 'w');
        fwrite($fp, $open_text);
        fclose($fp);

        $tables = $this->getRows(false, 'CubeCart_', $all_tables);

        foreach ($tables as $table) {
            $this->sqldumptable($file_name, $table, $dropTables, $incStructure, $incRows);
        }
        
        $fp = fopen($file_name, 'a+');
        $close_text = "-- --------------------------------------------------------\n-- CubeCart SQL Dump Complete\n-- --------------------------------------------------------";
        fwrite($fp, $close_text);
        fclose($fp);

        if ($compress) {
            if (file_exists($file_name)) {
                $zip = new ZipArchive();
                $filename = $file_name;
                if ($zip->open($filename.'.zip', ZipArchive::CREATE)!==true) {
                    $GLOBALS['main']->errorMessage($GLOBALS['language']->maintain['db_compress_fail']);
                    return false;
                } else {
                    $zip->addFile($filename);
                    $zip->close();
                    unlink($file_name);
                    return file_exists($file_name.'.zip');
                }
            } else {
                return false;
            }
        }
        return file_exists($file_name);
    }

    /**
     * Get DB engine
     *
     * @return string
     */
    public function getDbEngine()
    {
        return $this->_db_engine;
    }

    /**
     * Get DB debug info
     *
     * @return array
     */
    public function getDebug()
    {
        return array('error' => $this->_debugError, 'query' => $this->_debugQuery);
    }

    /**
     * Get all full text fields from a table
     *
     * @param string $table
     * @param string $prefix
     * @return array
     */
    public function getFulltextIndex($table = 'CubeCart_inventory', $prefix = false)
    {
        if (is_array($table)) {
            return false;
        }
        $fieldlist = array();
        $sql = "SHOW INDEX FROM `{$this->_prefix}$table`;";
        $result = $this->query($sql);
        if ($result) {
            foreach ($result as $index) {
                if ($index['Index_type'] == 'FULLTEXT' && $index['Key_name'] == 'fulltext') {
                    if ($prefix) {
                        $fieldlist[] = $prefix.'.'.$index['Column_name'];
                    } else {
                        $fieldlist[] = $index['Column_name'];
                    }
                }
            }
        }
        return $fieldlist;
    }

    /**
     * Get the number of rows found
     *
     * @return int
     */
    public function getFoundRows()
    {
        return $this->_found_rows;
    }

    /**
     * Get all rows from a table
     *
     * @param string $query
     * @return array
     */
    public function getRows($query = false, $native_prefix = '', $all_tables = false)
    {
        // Used in maintenance/backup and database, also in upgrade
        if (!$query) {
            $this->_query = 'SHOW tables';
        } else {
            $this->_query = $query;
        }

        $table_match = $this->_prefix.$native_prefix;

        $this->_query .= (empty($table_match) || $all_tables == true) ?  '' : " LIKE '".$table_match."%'";
        $this->_execute(false);
        $tableNames = $this->_result;
        foreach ($tableNames as $tableName) {
            sort($tableName);
            $this->_query = "SHOW TABLE STATUS LIKE '".$tableName[0]."'";
            $this->_execute(false);
            $tables[] = $this->_result[0];
        }

        return ($tables) ? $tables : false;
    }

    /**
     * Gets the size of the ft_min_word_len
     *
     * @return int
     */
    public function getSearchWordLen()
    {
        if (($query = $this->query("SHOW VARIABLES LIKE 'ft_min_word_len'")) !== false) {
            if (isset($query[0]['Value']) && is_numeric($query[0]['Value'])) {
                return (int)$query[0]['Value'];
            }
        }

        // Guess at 4 which is default in most cases
        return 4;
    }

    /**
     * Returns the query that was run.
     * Good for debug
     *
     * @return string Query
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Insert data into a table
     *
     * @param string $table
     * @param array $record
     * @param bool $quote
     * @param bool $purge
     * @return record id/false
     */
    public function insert($table, $record, $purge = true)
    {
        if (is_array($record)) {
            $allowed = $this->getFields($table);
            foreach ($record as $field => $value) {
                if (in_array($field, $allowed) && !is_numeric($field)) {
                    $fields[] = "`$field`";
                    $values[] = ($value==='NULL') ? 'NULL' : $this->sqlSafe($value, true);
                }
            }
            if (!empty($fields) && !empty($values)) {
                $this->_query = "INSERT INTO `{$this->_prefix}$table` (".implode(',', $fields).') VALUES ('.implode(',', $values).');';
                $this->_execute(false);
                $affected = ($this->affected() > 0);
                $this->_clearCacheNotice($purge, $affected, $table);
                $insert_id = ($this->insertid()) ? $this->insertid() : true;
                return ($affected) ? $insert_id : false;
            }
        }

        return false;
    }

    /**
     * Execute a misc query
     *
     * @param string $query
     * @return bool
     */
    public function misc($query, $cache = true)
    {
        $this->_query = $query;
        $this->_execute($cache);
        return $this->_result;
    }

    /**
     * GEt the number of rows
     *
     * @param string $query
     * @param bool $cache
     *
     * @return int/false
     */
    public function numrows($query = false, $cache = true)
    {
        $this->_query = $query;
        $this->_execute($cache);
        return (!empty($this->_result)) ? count($this->_result) : false;
    }

    /**
     * Create a pagination
     *
     * @param int $total_results
     * @param int $per_page
     * @param int $page
     * @param int $show
     * @param string $var_name
     * @param string $anchor
     * @param string $glue
     * @param bool $view_all
     * @return string/false
     */
    public function pagination($total_results = false, $per_page = 10, $page = 1, $show = 5, $var_name = 'page', $anchor = false, $glue = ' ', $view_all = true)
    {
        if ($page == 1) {
            $this->page_one[] = $var_name;
        }

        if (!$total_results && !is_null($this->_found_rows) && is_numeric($this->_found_rows)) {
            $total_results = $this->_found_rows;
        }

        $GLOBALS['smarty']->assign('TOTAL_RESULTS', $total_results);

        $glue = (!$glue) ? ' ' : $glue;
        // Lets do some maths...
        $total_pages = $per_page ? ceil($total_results/$per_page) : 0;
        $params = array();
        if ($total_pages > 1) {
            // Get the current query string variables
            $url_elements = parse_url(html_entity_decode($_SERVER['REQUEST_URI']));
            if (isset($url_elements['query']) && !empty($url_elements['query'])) {
                parse_str($url_elements['query'], $params);
                unset($params[$var_name], $params['print_hash']);
            }
            $anchor = ($anchor) ? "#$anchor" : '';

            if(ctype_digit($page)) {
                if ($page >= $show - 1) {
                    $params[$var_name] = 1;
                }
                if ($page > 1) {
                    $params[$var_name] = $page - 1;
                }
                if ($page < (int)$total_pages) {
                    $params[$var_name] = $page + 1;
                }
            }

            $data = array(
                'anchor'  => $anchor,
                'current'  => "{$url_elements['path']}?",
                'page'   => $page,
                'params'  => $params,
                'http_params' => http_build_query($params),
                'show'   => (int)$show,
                'total'   => (int)$total_pages,
                'var_name'  => $var_name,
                'view_all'  => (bool)$view_all,
                'per_page'  => (int)$per_page,
            );
            $GLOBALS['smarty']->assign($data);
            return $GLOBALS['smarty']->fetch('templates/element.paginate.php');
        }

        return false;
    }

    /**
     * Parse sql schema
     *
     * @param string $schema
     * @return bool
     */
    public function parseSchema($schema = false)
    {
        if (!empty($schema)) {
            $log = null;
            $queries = preg_split("/;\s?(#EOQ|[\n\r])/i", $schema, -1, PREG_SPLIT_NO_EMPTY);
            if (is_array($queries)) {
                $default_lang = (isset($_SESSION['setup']['long_lang_identifier']) && !empty($_SESSION['setup']['long_lang_identifier'])) ? $_SESSION['setup']['long_lang_identifier'] : 'en-GB';

                foreach ($queries as $i => $query) {
                    if (!empty($this->_prefix)) {
                        $query = str_replace(array('CubeCart_', '{%DEFAULT_EN-XX%}'), array($this->_prefix.'CubeCart_', $default_lang), $query);
                    }
                    $query = trim($query);
                    if (!empty($query)) {
                        $this->query($query, false, 0, false);
                    }
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Query DB
     *
     * @param string $query
     * @param int $maxRows
     * @param int $page
     * @param bool $cache
     * @return result/false
     */
    public function query($query, $maxRows = false, $page = 0, $cache = true)
    {
        if(empty($query)) { return false;}
        // For old fashioned 'hand written' queries
        $limit = '';

        if (is_numeric($maxRows)) {
            if ($page>0) {
                $limit = "LIMIT $maxRows OFFSET ".($page - 1) * $maxRows;
            } else {
                if (strtolower($page) == 'all') {
                    // Don't set a limit - show EVERYTHING
                } else {
                    $limit = "LIMIT $maxRows";
                }
            }
        }
        $this->_query = $query.' '.$limit;
        $this->_execute($cache);

        return (!$this->error()) ? $this->_result : false;
    }

    /**
     * SELECT query
     *
     * @param string $table
     * @param array $columns
     * @param string $where
     * @param string $order
     * @param int $maxRows
     * @param int $page
     * @param bool $cache
     * @return bool
     */
    public function select($table, $columns = false, $where = false, $order = false, $maxRows = false, $page = false, $cache = true)
    {
        $table_where = $table;

        if (!stristr($table, 'JOIN')) {
            // Build an SQL SELECT query the (almost) easy way
            $allowed = $this->getFields($table);
            $wrapper = '`';
            $prefix = $this->_prefix;
        } else {
            // Find the original table in JOIN set
            if (preg_match('#^`(.+)`[a-z ]+JOIN#i', $table, $match)) {
                $table_where = str_replace($this->_prefix, '', $match[1]);
            }
            $wrapper = '';
            $prefix = '';
        }

        if ($columns) {
            if (isset($allowed) && isset($columns) && is_array($allowed) && is_array($columns)) {
                foreach ($columns as $key => $field) {
                    if (in_array($field, $allowed) && !is_numeric($field)) {
                        if (!is_numeric($key) && in_array(strtoupper($key), array('DISTINCT'))) {
                            $group_by[] = $field;
                            $cols[]  = "$key `$field`";
                        } elseif (!is_numeric($key) && in_array(strtoupper($key), array('MIN', 'MAX', 'SUM'))) {
                            $cols[]  = "$key($field) AS {$key}_$field";
                        } else {
                            $cols[]  = "`$field`";
                        }
                    }
                }
            } else {
                $cols[] = $columns;
            }
        }

        $orderString = null;
        if ($order) {
            if (is_array($order)) {
                foreach ($order as $field => $sort) {
                    if (is_array($allowed)) {
                        if (in_array($field, $allowed)) {
                            $orderArray[] = "`$field` ".$this->sqlSafe($sort);
                        }
                    }
                }
                if (isset($orderArray) && is_array($orderArray)) {
                    $orderString = 'ORDER BY '.implode(', ', $orderArray);
                }
            } else {
                $orderString = 'ORDER BY '.str_ireplace('ORDER BY', '', $this->sqlSafe($order));
            }
        }

        if (!$columns || !isset($cols)) {
            $cols[] = '*';
        }

        $limit  = null;
        $calc_rows = null;
        $sql_cache = null;

        if (is_numeric($maxRows)) {
            $calc_rows = 'SQL_CALC_FOUND_ROWS';
            if ($page>0) {
                $limit = "LIMIT $maxRows OFFSET ".($page - 1) * $maxRows;
            } else {
                if (strtolower($page) == 'all') {
                    // Don't set a limit - show EVERYTHING
                } else {
                    $limit = "LIMIT $maxRows";
                }
            }
        }
        $group = (isset($group_by) && is_array($group_by)) ? 'GROUP BY '.implode(',', $group_by) : '';
        
        $parent_query = "SELECT $sql_cache $calc_rows ".implode(', ', $cols)." FROM $wrapper{$prefix}$table$wrapper ".$this->where($table_where, $where)." $group $orderString $limit;";
        $this->_query = $parent_query;

        $this->_execute($cache);

        if (is_array($this->_result) && count($this->_result) >= 1) {
            foreach ($this->_result as $row) {
                $output[] = $row;
            }
            // Added cleverness for auto pagination, without running a second query
            if (!is_null($calc_rows)) {
                $count_query = 'SELECT FOUND_ROWS() as Count;';
                if ($count = $this->_getCached($parent_query.$count_query)) {
                    $this->_found_rows = $count[0]['Count'];
                } elseif ($count = $this->misc($count_query, false)) { // Cache managed here not in DB->query
                    $this->_found_rows = $count[0]['Count'];
                    $this->_writeCache($count, $parent_query.$count_query);
                }
            }
            return ($output) ? $output : false;
        }
        return false;
    }

    /**
     * Dump SQL data
     *
     * @param string $tableData
     * @param bool $dropTables
     * @param bool $incStructure
     * @param bool $incRows
     * @return false
     */
    public function sqldumptable($file_name, $tableData, $dropTables = false, $incStructure = true, $incRows = true, $maxRows = 50, $page = 1)
    {
        $fp = fopen($file_name, 'a+');
        $tabledump = '';
        if ($dropTables && $page===1) {
            fwrite($fp, "-- --------------------------------------------------------\n\nDROP TABLE IF EXISTS `".$tableData['Name']."`; #EOQ\n\n");
        }
        if ($incStructure && $page===1) {
            $schema	= $this->query('SHOW CREATE TABLE `'.$tableData['Name'].'`');
            fwrite($fp, "-- --------------------------------------------------------\n\n-- \n-- Table structure for table `".$tableData['Name']."`\n--\n\n");
            fwrite($fp, $schema[0]['Create Table']);
            fwrite($fp, "; #EOQ\n\n");
        }
        if ($incRows) {
            ## get data
            $limit = $page>0 ? "LIMIT $maxRows OFFSET ".($page - 1) * $maxRows : "LIMIT $maxRows";

            $this->_query = "SELECT * FROM ".$tableData['Name']." ".$limit;
            $this->_execute(false);
            
            if ($this->_result) {
                if ($page===1) {
                    fwrite($fp, "--\n-- Dumping data for table `".$tableData['Name']."`\n--\n\n");
                }
                foreach ($this->_result as $row) {
                    fwrite($fp, "INSERT INTO `".$tableData['Name']."` VALUES(");
                    ## get each field's data
                    $comma = false;
                    foreach ($row as $key => $value) {
                        fwrite($fp, $comma ? ', ' : '');
                        fwrite($fp, $this->sqlSafe($value, true));
                        $comma = true;
                    }
                    fwrite($fp, "); #EOQ\n");
                }
                fclose($fp);
                $page++;
                $this->sqldumptable($file_name, $tableData, $dropTables, $incStructure, $incRows, $maxRows, $page);
            } elseif ($page===1) {
                fwrite($fp, "-- Table `".$tableData['Name']."` has no data\n\n");
                fclose($fp);
            }
        }
        return false;
    }

    /**
     * Strip slashes
     *
     * @param string $input
     * @return string
     */
    public function strip_slashes($input)
    {
        // Strip slashes, unless it's serialized data
        if (!preg_match('#^\w:\d+:\{(.+)\}$#su', $input)) {
            $input = stripslashes($input);
        }

        return $input;
    }

    /**
     * TRUNCATE table
     *
     * @param string/array $input
     * @return bool
     */
    public function truncate($input)
    {
        if (is_array($input)) {
            $result = true;
            foreach ($input as $table) {
                $this->_query = 'TRUNCATE `'.$this->_prefix.$table.'`; ';
                if (!$this->_execute(false)) {
                    $result = false;
                }
            }
        } else {
            $this->_query = 'TRUNCATE `'.$this->_prefix.$input.'`; ';
            $result = $this->_execute(false);
        }

        return $result;
    }

    /**
     * Update table
     *
     * @param string $table
     * @param array $record
     * @param string $where
     * @param bool $purge
     * @return bool
     */
    public function update($table, $record, $where = '', $purge = true, $skip_math_fields = array())
    {
        if (is_array($record)) {
            $allowed = $this->getFields($table);
            foreach ($record as $field => $value) {
                if (in_array($field, $allowed) && !is_numeric($field)) {
                    $number = substr($value, 1);
                    if ($skip_math_fields!== 'all' && !in_array($field, $skip_math_fields) && isset($value[0]) && is_numeric($number) && ($value[0] == '+' || $value[0] == '-')) {
                        $set[] = "`$field` = `$field` {$value[0]} ".$number;
                    } else {
                        $value = (in_array($value, $this->_allowed_exceptions, true)) ? $value : $this->sqlSafe($value, true);
                        $set[] = "`$field` = $value";
                    }
                }
            }
            if (!empty($set)) {
                $this->_query = "UPDATE `{$this->_prefix}$table` SET ".implode(',', $set).'  '.$this->where($table, $where).';';
                $result = $this->_execute(false);
                $affected = ($this->affected() > 0);
                $this->_clearCacheNotice($purge, $affected, $table);
                return (bool)$result;
            }
        }
        return false;
    }

    /**
     * Builds a WHERE string
     *
     * @param string $table
     * @param array $whereArray
     * @param string $label optional table alias used to disambiguate fields
     * @return string
     */
    public function where($table, $whereArray = null, $label = false)
    {
        if (!empty($whereArray)) {
            if (is_array($whereArray)) {
                $allowed = $this->getFields($table);
                foreach ($whereArray as $key => $value) {
                    unset($symbol);
                    if (is_array($value)) {
                        foreach ($value as $val) {
                            if (in_array($val, $allowed) && !is_numeric($val) || preg_match('/CONCAT/', $val)) {
                                if (isset($key[0]) && !ctype_alnum($key[0]) || $key[0]=='NULL' || is_null($key[0]) || $key[0]=='NOT NULL') {
                                    if (preg_match('#^([<>!~\+\-]=?)(.+)#', $key, $match)) {
                                        switch ($match[1]) {
                                        case '~':
                                            // Fuzzy searching
                                            $symbol = 'LIKE';
                                            $key = "%{$match[2]}%";
                                            break;
                                        default:
                                            $symbol = $match[1];
                                            $key = trim($match[2]);
                                        }
                                    }
                                }
                                
                                $val_ = preg_match('/CONCAT/', $val) ? $val : "`$val`";

                                if (strtoupper($key[0]) == 'NULL' || is_null($key[0])) {
                                    $symbol = 'IS NULL';
                                    $where[] = "$val_ $symbol";
                                } elseif (strtoupper($key[0])=='NOT NULL') {
                                    $symbol = 'IS NOT NULL';
                                    $where[] = "$val_ $symbol";
                                } else {
                                    $symbol = (isset($symbol)) ? $symbol : '=';
                                    $or[] = "$val_ $symbol ".$this->sqlSafe($key, true);
                                }
                            } else {
                                foreach ($value as $i => $val) {
                                    if (empty($val)) {
                                        unset($value[$i]);
                                    }
                                }
                                if (count($value)>0) {
                                    if ($key[0] == '!') {
                                        $modifier = 'NOT';
                                        $key  = substr($key, 1);
                                    } else {
                                        $modifier = '';
                                    }
                                    $or[] = "`$key` $modifier IN (".implode(',', $value).')';
                                }
                                break;
                            }
                        }
                        if (isset($or) && is_array($or)) {
                            $where[] = implode(' OR ', $or);
                            unset($or);
                        }
                    } else {
                        // Remove column label so that it can correctly check against table columns
                        $key = (is_string($key) && $label ? preg_replace("/^$label\./", '', $key, 1) : $key);
                        if (is_array($allowed) && in_array($key, $allowed) && !is_numeric($key)) {
                            if (isset($value) && !ctype_alnum($value) || $value=='NULL' || is_null($value) || $value=='NOT NULL') {
                                if (preg_match('#^([<>!~\+\-]=?)(.+)#', $value, $match)) {
                                    switch ($match[1]) {
                                    case '~':
                                        // Fuzzy searching
                                        $symbol = 'LIKE';
                                        $value = "%{$match[2]}%";
                                        break;
                                    default:
                                        $symbol = $match[1];
                                        $value = trim($match[2]);
                                    }
                                }
                            }

                            // Be sure to re-add column identifier if it was given; otherwise, use table name
                            $full_key = ($label ? $label : $this->_prefix.$table).".".$key;

                            if (strtoupper($value) == 'NULL' || is_null($value)) {
                                $symbol = 'IS NULL';
                                //$where[] = "`$key` $symbol";
                                $where[] = "$full_key $symbol";
                            } elseif (strtoupper($value)=='NOT NULL') {
                                $symbol = 'IS NOT NULL';
                                //$where[] = "`$key` $symbol";
                                $where[] = "$full_key $symbol";
                            } else {
                                $symbol = (isset($symbol)) ? $symbol : '=';
                                //$where[] = "`$key` $symbol ".$this->sqlSafe($value,true);
                                $where[] = "$full_key $symbol ".$this->sqlSafe($value, true);
                            }
                        } else {
                            trigger_error("`$key` is not allowed as a key in '$table' table!");
                        }
                    }
                }
                return (!empty($where)) ? 'WHERE '.implode(' AND ', $where) : false;
            } else {
                return 'WHERE '.trim($whereArray);
            }
        }

        return false;
    }

    //=====[ Private ]=======================================

    /**
     * Do we need to recommend a cache clear?
     */
    private function _clearCacheNotice($purge, $affected, $table)
    {
        if (CC_IN_ADMIN && $purge && $affected && method_exists($GLOBALS['session'], 'set') && !in_array($table, $this->cache_notice_tables_ingore)) {
            $GLOBALS['session']->set('CLEAR_CACHE', true);
        }
    }

    /**
     * Get cached query
     *
     * @param string $query
     * @return data/false
     */
    protected function _getCached($query)
    {
        $query_hash = 'sql.'.md5($query);
        $this->_cached = false;
        
        if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
            $this->_cached = true;
            return $GLOBALS['cache']->read($query_hash);
        }

        return false;
    }

    /**
     * Log SQL errors
     */
    protected function _logError()
    {
        $trace = debug_backtrace();
        Database::getInstance()->insert('CubeCart_system_error_log', array('message' => 'File: ['.basename($trace[2]['file']).'] Line: ['.$trace[2]['line'].'] "'.$this->_query.'" - '.$this->errorInfo(), 'time' => time()));
    }

    /**
     * Sql debug
     *
     * @return bool
     */
    protected function _sqlDebug($cache, $source)
    {
        if (isset($GLOBALS['debug']) && $GLOBALS['debug'] instanceof Debug) {
            $message = "{$this->_query} -- ({$this->_query_time} sec)";
            $GLOBALS['debug']->debugSQL('query', $message, $cache, $source);
            $this->_error = ($this->error()) ? $this->error().': '.$this->errorInfo() : false;
            $GLOBALS['debug']->debugSQL('error', $this->_error, $cache, $source);
        }

        return $this->error();
    }

    /**
     * Starts a timer for a query
     */
    protected function _startTimer()
    {
        $this->_query_time = microtime(true);
    }

    /**
     * Stops a timer
     */
    protected function _stopTimer()
    {
        $this->_query_time = microtime(true) - $this->_query_time;
    }

    /**
     * Write data to cached query
     *
     * @param mixed $data
     * @param string $query
     * @return bool
     */
    protected function _writeCache($data, $query)
    {
        $query_hash = md5($query);
        if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
            return $GLOBALS['cache']->write($data, 'sql.'.$query_hash);
        }
        return false;
    }
}
