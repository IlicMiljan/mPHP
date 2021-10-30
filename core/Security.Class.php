<?php
    namespace Core;

    class Security {

        public static function Input($String)  {
            $String = trim($String);
            $String = stripslashes($String);
            return htmlspecialchars($String);
        }
    }
