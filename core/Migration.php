<?php

    namespace Migrations;

    use Core\DataBase;

    class Migration {
        protected $DB = NULL;

        /**
         * Create DB Instance
         * @return void
         */
        public function __construct()
        {
            $this->DB = new DataBase();
        }
    }