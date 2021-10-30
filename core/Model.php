<?php
  namespace Core;

  class Model {
      protected $DB;

      /**
       * Create DB Instance
       * @return void
       */
      public function __construct()
      {
          $this->DB = new DataBase();
      }
  }