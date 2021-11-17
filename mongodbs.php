<?php
class Mongodbs {
    private $host = 'localhost'; // MongoDB Server IP or host name
    private $port = '27017'; // Port for accessing MongoDB - Default Installation port is 27017
    private $username = 'db-username'; //  MongoDB access username
    private $password = 'db-password'; // MongoDB access password
    private $db = 'database'; // MonogDB Database instance name
    private $connection;
    private $writeConcern = "";
    public function __construct($registry) {
        $manager = new MongoDB\Driver\Manager("mongodb://" . $this->username . ":" . $this->password . "@" . $this->host . ":" . $this->port . "/" . $this->db . "");
        $this->connection = $manager;
    }

    function connect() {
        $manager = new MongoDB\Driver\Manager("mongodb://" . $this->username . ":" . $this->password . "@" . $this->host . ":" . $this->port . "/" . $this->db . "");
        return $manager;
    }

    function getMongoWrite() {
        $bulk = new MongoDB\Driver\BulkWrite(['ordered' => true]);
        $this->writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        return $bulk;
    }

    function writeInsert($write, $data = array()) {
        if ($data) {
            $write->insert($data);
        }
    }

    function writeUpdate($write, $condtion = array(), $updatedata = array(), $options = array('upsert' => false, 'multi' => false)) {
        if ($updatedata) {
          try {
            $write->update($condtion, $updatedata, $options);
          } catch (MongoDB\Driver\Exception\BulkWriteException $e) {

              $result = $e->getWriteResult();
              $errors = [];
              foreach ($result->getWriteErrors() as $writeError) {
                throw new Exception("Error Index : ".$writeError->getIndex(). " | Error Code : ".$writeError->getCode(). " | Error Message : ".$writeError->getMessage());
              }
              return $result;
          } catch (MongoDB\Driver\Exception\Exception $e) {
              printf("Other error: %s\n", $e->getMessage());
              exit;
          }
        }
    }

    function writeUpdate1($write, $filter, $data) {
        if ($data) {
            $write->update($filter, $data);
        }
    }

    function writeDelete($write, $filter, $option= array()) {
        if ($filter) {
            $write->delete($filter, $option);
        }
    }

    function writeExecute($database, $bulk, $msg = true) {
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        try {
            $result = $this->connection->executeBulkWrite($database, $bulk, $writeConcern);
        } catch (MongoDB\Driver\Exception\BulkWriteException $e) {

            $result = $e->getWriteResult();
            foreach ($result->getWriteErrors() as $writeError) {
              throw new Exception("Error Index : ".$writeError->getIndex(). " | Error Code : ".$writeError->getCode(). " | Error Message : ".$writeError->getMessage());
            }
        } catch (MongoDB\Driver\Exception\Exception $e) {
            printf("Other error: %s\n", $e->getMessage());
            exit;
        }

        if ($msg) {
            //$rTxt = printf("Inserted %d document(s)\n", $result->getInsertedCount()) . " " . printf("Updated  %d document(s)\n", $result->getModifiedCount());
            //return $rTxt;
            return true;
        } else {
            return true;
        }
    }

    function query($filter, $options) {
        return $query = new MongoDB\Driver\Query($filter, $options);
    }

    function executeQuery($database, $query) {
        $results = $this->connection->executeQuery($database, $query);
        return $results;
    }

    function executeCommand($database, $command) {
      try {
          $results = $this->connection->executeCommand($database, $command);
          return $results;
      } catch(MongoDB\Driver\Exception $e) {
          return $e->getMessage();
      }
    }

    function command($command) {
        return $query = new MongoDB\Driver\Command($command);
    }

}
