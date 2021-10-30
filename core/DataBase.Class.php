<?php
  namespace Core;

  use PDO;
  use PDOException;

class DataBase {
    private $Host      = DB_HOST;
    private $Port      = DB_PORT;
    private $User      = DB_USER;
    private $Pass      = DB_PASS;
    private $DB_Name   = DB_NAME;

    private $DBH;
    private $STMT;
    private $Error;

    public function __construct() {
      // Set DSN
      $DSN = 'mysql:host=' . $this->Host . ';port=' . $this->Port .  ';dbname=' . $this->DB_Name;
      // Set options
      $Options = array(
          PDO::ATTR_EMULATE_PREPARES => TRUE,
          PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION
      );
      // Create a new PDO instanace
      try{
          $this->DBH = new PDO($DSN, $this->User, $this->Pass, $Options);
      }
      // Catch any errors
      catch(PDOException $e){
          $this->Error = $e->getMessage();
      } echo $this->Error;
    }

    public function Query($Query) {
      $this->STMT = $this->DBH->prepare($Query);
    }

    public function Bind($Param, $Value, $Type = NULL) {
      if (is_null($Type)) {
        switch (TRUE) {
          case is_int($Value):
            $Type = PDO::PARAM_INT;
          break;
          case is_bool($Value):
            $Type = PDO::PARAM_BOOL;
          break;
          case is_null($Value):
            $Type = PDO::PARAM_NULL;
          break;
          default:
            $Type = PDO::PARAM_STR;
        }
      }
      $this->STMT->bindValue($Param, $Value, $Type);
    }

    public function Execute() {
      return $this->STMT->execute();
    }

    public function ResultSet() {
      $this->execute();
      return $this->STMT->fetchAll();
    }

    public function Single() {
      $this->execute();
      return $this->STMT->fetch(PDO::FETCH_ASSOC);
    }

    public function RowCount() {
      return $this->STMT->rowCount();
    }

    public function LastID() {
      return $this->DBH->lastInsertId();
    }

    public function StartTransaction() {
      return $this->DBH->beginTransaction();
    }

    public function EndTransaction() {
      return $this->DBH->commit();
    }

    public function CancelTransaction() {
      return $this->DBH->rollBack();
    }

    public function DebugParams() {
      return $this->STMT->debugDumpParams();
    }

  }

