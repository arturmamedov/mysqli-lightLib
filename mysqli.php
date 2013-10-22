<?php
/**
 * @version 1
 * @author Artur Mamedov <arturmamedov1993@gmail.com>
 * 
 * Class for basic work with mysqli library 
 * discussed in videolessons(italian lang): http://www.obiv.it/video-giornale/7-programmazione-oggetti-php-mysqli-class.html
 * and video course(italian lang):  http://www.obiv.it/video-corso/oop-mvc-php.html
 */
class Mysqli_Database {
    /**
     * Current query
     * @var string
     */
    private $query;
    /**
     * Mysql improved libraries object
     * @var object 
     */
    private $mysqli;
    /**
     * Result of query perform
     * @var mixed 
     */
    private $result;

    
    public function __construct($host, $user, $password, $database, $charset = 'utf8', $port = null, $socket = null){
        // connect in constructor
        $this->connect($host, $user, $password, $database, $charset, $port, $socket);
    }

    public function __destruct(){
        $this->disconnect();
    }

    
    /**
     * Method for create connection with DB
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param string $charset
     * @param string $port
     * @param string $socket
     * 
     * @return boolean
     */
    public function connect($host, $user, $password, $database, $charset, $port = null, $socket = null){
        // creare la connessione
        $this->mysqli = new mysqli($host, $user, $password, $database, $port, $socket);

        if(mysqli_connect_error()){
            return array('status' => false, 'error' => $mysqli_connect_error);
        } else {
            $this->mysqli->set_charset($charset);
        }

        return true;
    }	

    /**
     * Close connection with DB
     */
    public function disconnect(){
        $this->mysqli->close();
        return true;
    }

    /**
     * Prepare query for execution
     * 
     * @param string $query
     * 
     * @return boolean
     */
    public function prepare($query){
        $this->query = trim($query); // trim cosi se ci sono spazi vuoti e basta il controllo in query() sare true
        return true;
    }

    /**
     * Execute prepared query 
     * 
     * @return boolean (false on error)
     */
    public function query(){
        if(strlen($this->query) == 0)
            throw new Exception('Mysqli_Database - query can\'t be empty');
            
        $this->result = $this->mysqli->query($this->query);
        
        if(!$this->result)
            throw new Exception('Mysqli_Database - MySQLi error: '.$this->mysqli->error);
        
        return true;
    }

    /**
     * For fetch result data
     * 
     * @param string $type ('object', 'allAssoc', 'assoc', 'array')
     * 
     * @return mixed (fetched result) 
     * 
     * 'assoc', 'object' and 'array': type fetch only one row
     * 'allAssoc': fetch all row how associative array
     */
    public function fetch($type = 'object'){
        switch($type){
            case 'allAssoc':
                while($res = $this->result->fetch_assoc()){
                        $row[] = $res;
                }
                break;
            case 'assoc':
                $row = $this->result->fetch_assoc();
                break;
            case 'object':
                $row = $this->result->fetch_object();
                break;
            case 'array':
                $row = $this->result->fetch_array();
                break;	
        }
        $this->result->close();

        return $row;
    }
    
    /**
     * Insert data into db
     * or string and array:
     * ex: $this->insert("INSERT INTO users (user_id, first_name, last_name) VALUES (NULL , '%s', '%s', ", array($this->first_name, $this->last_name);
     * are formatted with vspitnf and escaped by $this->escape
     * 
     * or only query string ex: $this->insert("INSERT INTO users (user_id, first_name, last_name) VALUES (NULL , 'Artur', 'Mamedov'")
     * 
     * 
     * @param string $query
     * @param array $data (are escaped if isset)
     * 
     * @return boolean/int  false if an error occur, last insert id if all ok
     */
    public function insert($query, $data = null){
        if(count($data) > 0){
            // escape all passed data values
            foreach($data as $d)
                $params[] = $this->escape($d);
            
            $this->prepare(vsprintf($query, $params));
        } else
            $this->prepare($query);
        
        if(!$this->query())
            return false;
        
        return $this->mysqli->insert_id;
    }

    /**
     * Escape passed data for prevent injection
     * 
     * @param string $data
     * 
     * @return string escaped $data 
     */
    public function escape($data){
        return $this->mysqli->real_escape_string($data);
    }
}
?>
