<?php
    namespace Core;

    class Router {

        private static $Router = array();
        private static $PathNotFound = null;
        private static $MethodNotAllowed = null;

        public static function Add($Expression, $Function, $Method = 'GET') {
            array_push(
                self::$Router, 
                array(
                    'Expression' => $Expression,
                    'Function' => $Function,
                    'Method' => $Method
                ));
        }

        public static function PathNotFound($Function) {
            self::$PathNotFound = $Function;
        }

        public static function MethodNotAllowed($Function) {
            self::$MethodNotAllowed = $Function;
        }

        public static function Run($BasePath = '/') {

            // Parse Current URL
            $ParsedURL = parse_url($_SERVER['REQUEST_URI']); //Parse URI

            if(isset($ParsedURL['path'])){
                $Path = $ParsedURL['path'];
            }else{
                $Path = '/';
            }

            // Get current request method
            $Method = $_SERVER['REQUEST_METHOD'];

            $PathMatchFound = false;

            $RouteMatchFound = false;

            foreach(self::$Router as $Route){

                // If the method matches check the path

                // Add basepath to matching string
                if($BasePath!=''&&$BasePath!='/'){
                    $Route['Expression'] = '('.$BasePath.')'.$Route['Expression'];
                }

                $Route['Expression'] = '^'.$Route['Expression'];

                // Add 'find string end' automatically
                $Route['Expression'] = $Route['Expression'].'$';
                
                if(preg_match('#'.$Route['Expression'].'#',$Path,$Matches)){
                    $PathMatchFound = true;

                    if(strtolower($Method) == strtolower($Route['Method'])){

                        array_shift($Matches);

                        if($BasePath!=''&&$BasePath!='/'){
                            array_shift($Matches);
                        }

                        call_user_func_array($Route['Function'], $Matches);

                        $RouteMatchFound = true;

                        break;
                    }
                }
            }

            // No matching route was found
            if(!$RouteMatchFound){

                // But a matching path exists
                if($PathMatchFound){
                    header("HTTP/1.0 405 Method Not Allowed");
                    if(self::$MethodNotAllowed){
                        call_user_func_array(self::$MethodNotAllowed, Array($Path,$Method));
                    }
                }else{
                    header("HTTP/1.0 404 Not Found");
                    if(self::$PathNotFound){
                        call_user_func_array(self::$PathNotFound, Array($Path));
                    }
                }

            }

        }

    }