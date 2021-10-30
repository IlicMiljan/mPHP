<?php
namespace Core;

class Template {

    static $Blocks = array();
    static $CachePath = 'cache/';
    static $CacheEnabled = FALSE;

    static function View($File, $Data = array()) {
        $CachedFile = self::Cache($File);
        extract($Data, EXTR_SKIP);
        require $CachedFile;
    }

    static function Cache($File) {
        if (!file_exists(self::$CachePath)) {
            mkdir(self::$CachePath, 0744);
        }
        $CachedFile = self::$CachePath . str_replace(array('/', '.html'), array('_', ''), $File . '.php');
        if (!self::$CacheEnabled || !file_exists($CachedFile) || filemtime($CachedFile) < filemtime($File)) {
            $Code = self::IncludeFiles($File);
            $Code = self::CompileCode($Code);
            file_put_contents($CachedFile, '<?php class_exists(\'' . __CLASS__ . '\') or exit; ?>' . PHP_EOL . $Code);
        }
        return $CachedFile;
    }

    static function ClearCache() {
        foreach(glob(self::$CachePath . '*') as $File) {
            unlink($File);
        }
    }

    static function CompileCode($Code) {
        $Code = self::CompileBlock($Code);
        $Code = self::CompileYield($Code);
        $Code = self::CompileEscapedEchos($Code);
        $Code = self::CompileEchos($Code);
        $Code = self::CompilePHP($Code);
        return $Code;
    }

    static function IncludeFiles($File) {
        $Code = file_get_contents('views/' . $File);
        preg_match_all('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', $Code, $Matches, PREG_SET_ORDER);
        foreach ($Matches as $Value) {
            $Code = str_replace($Value[0], self::IncludeFiles($Value[2]), $Code);
        }
        return preg_replace('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', '', $Code);
    }

    static function CompilePHP($Code) {
        return preg_replace('~\{%\s*(.+?)\s*\%}~is', '<?php $1 ?>', $Code);
    }

    static function CompileEchos($Code) {
        return preg_replace('~\{{\s*(.+?)\s*\}}~is', '<?php echo $1 ?>', $Code);
    }

    static function CompileEscapedEchos($Code) {
        return preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $Code);
    }

    static function CompileBlock($Code) {
        preg_match_all('/{% ?block ?(.*?) ?%}(.*?){% ?endblock ?%}/is', $Code, $Matches, PREG_SET_ORDER);
        foreach ($Matches as $Value) {
            if (!array_key_exists($Value[1], self::$Blocks)) self::$Blocks[$Value[1]] = '';
            if (strpos($Value[2], '@parent') === false) {
                self::$Blocks[$Value[1]] = $Value[2];
            } else {
                self::$Blocks[$Value[1]] = str_replace('@parent', self::$Blocks[$Value[1]], $Value[2]);
            }
            $Code = str_replace($Value[0], '', $Code);
        }
        return $Code;
    }

    static function CompileYield($Code) {
        foreach(self::$Blocks as $Block => $Value) {
            $Code = preg_replace('/{% ?yield ?' . $Block . ' ?%}/', $Value, $Code);
        }
        return preg_replace('/{% ?yield ?(.*?) ?%}/i', '', $Code);
    }

}