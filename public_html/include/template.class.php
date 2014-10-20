<?php
require_once ('./include/template.func.php');

class Template
{

    const DIR_SEP = DIRECTORY_SEPARATOR;

    protected static $_instance;

 
    protected $_options = array();

    public static function getInstance()
    {
        if (!self::$_instance instanceof self)
            self::$_instance = new self();
        return self::$_instance;
    }

    private function __construct()
    {
        $this->_options = array(
            'template_dir' => 'templates' . self::DIR_SEP, 
            'cache_dir' => 'templates' . self::DIR_SEP . 'cache' . self::DIR_SEP,
            'auto_update' => false, 
            'cache_lifetime' => 0, 
        );
    }
   
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value)
            $this->set($name, $value);
    }

    public function set($name, $value)
    {
        switch ($name) {
            case 'template_dir':
                $value = $this->_trimpath($value);
                if (!file_exists($value))
                    $this->_throwException("throwException \"$value\"");
                $this->_options['template_dir'] = $value;
                break;
            case 'cache_dir':
                $value = $this->_trimpath($value);
                if (!file_exists($value))
                    $this->_throwException("throwException \"$value\"");
                $this->_options['cache_dir'] = $value;
                break;
            case 'auto_update':
                $this->_options['auto_update'] = (boolean) $value;
                break;
            case 'cache_lifetime':
                $this->_options['cache_lifetime'] = (float) $value;
                break;
            default:
                $this->_throwException("throwException \"$name\"");
        }
    }
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

   
    public function getfile($file)
    {
        $cachefile = $this->_getCacheFile($file);
        if (!file_exists($cachefile))
            $this->cache($file);
        return $cachefile;
    }

    public function check($file, $md5data, $expireTime)
    {
        if ($this->_options['auto_update']
        && md5_file($this->_getTplFile($file)) != $md5data)
            $this->cache($file);
        if ($this->_options['cache_lifetime'] != 0
        && (time() - $expireTime >= $this->_options['cache_lifetime'] * 60))
            $this->cache($file);
    }

    public function cache($file)
    {
        $tplfile = $this->_getTplFile($file);

        if (!is_readable($tplfile)) {
            $this->_throwException("throwException \"$tplfile\" throwException");
        }
		
		$template = file_get_contents($tplfile);

		for($i = 1; $i<=3; $i++) {
			if(GlobalFunc::strexists($template, '{subtemplate')) {
				$template = preg_replace("/[\n\r\t]*\{subtemplate\s+([a-z0-9_]+)\}[\n\r\t]*/ies", "loadsubtemplate('\\1')", $template);
			}
		}        
		
        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
       
     
        $template = str_replace("{LF}", "<?=\"\\n\"?>", $template);

        
        $varRegexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)"
                    . "(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
        $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
        $template = preg_replace("/$varRegexp/es", "addquote('<?=\\1?>')", $template);
        $template = preg_replace("/\<\?\=\<\?\=$varRegexp\?\>\?\>/es", "addquote('<?=\\1?>')", $template);

        
        $template = preg_replace(
            "/[\n\r\t]*\{template\s+([a-z0-9_]+)\}[\n\r\t]*/is",
            "\r\n<? include(\$template->getfile('\\1')); ?>\r\n",
            $template
        );
        $template = preg_replace(
            "/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is",
            "\r\n<? include(\$template->getfile(\\1)); ?>\r\n",
            $template
         );

        
        $template = preg_replace(
            "/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies",
            "stripvtags('<? \\1 ?>','')",
            $template
        );
        $template = preg_replace(
            "/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies",
            "stripvtags('<? echo \\1; ?>','')",
            $template
        );
        $template = preg_replace(
            "/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/ies",
            "stripvtags('\\1<? } elseif(\\2) { ?>\\3','')",
            $template
        );
        $template = preg_replace(
            "/([\n\r\t]*)\{else\}([\n\r\t]*)/is",
            "\\1<? } else { ?>\\2",
            $template
        );

        
        $nest = 5;
        for ($i = 0; $i < $nest; $i++) {
            $template = preg_replace(
                "/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies",
                "stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\\3<? } } ?>')",
                $template
            );
            $template = preg_replace(
                "/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies",
                "stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\\4<? } } ?>')",
                $template
            );
            $template = preg_replace(
                "/([\n\r\t]*)\{if\s+(.+?)\}([\n\r]*)(.+?)([\n\r]*)\{\/if\}([\n\r\t]*)/ies",
                "stripvtags('\\1<? if(\\2) { ?>\\3','\\4\\5<? } ?>\\6')",
                $template
            );
        }

        $template = preg_replace(
            "/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/s",
            "<?=\\1?>",
            $template
        );

       
        $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

        $template = preg_replace(
            "/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e",
            "transamp('\\0')",
            $template
        );
        $template = preg_replace(
            "/\<script[^\>]*?src=\"(.+?)\".*?\>\s*\<\/script\>/ise",
            "stripscriptamp('\\1')",
            $template
        );
        $template = preg_replace(
            "/[\n\r\t]*\{block\s+([a-zA-Z0-9_]+)\}(.+?)\{\/block\}/ies",
            "stripblock('\\1', '\\2')",
            $template
        );

        $md5data = md5_file($tplfile);
        $expireTime = time();
        $template = "<? if (!class_exists('template')) die('Access Denied');"
                  . "\$template->getInstance()->check('$file', '$md5data', $expireTime);"
                  . "?>\r\n$template";

        $cachefile = $this->_getCacheFile($file);
        $makepath = $this->_makepath($cachefile);
        if ($makepath !== true)
            $this->_throwException("throwException \"$makepath\"");
        file_put_contents($cachefile, $template);
    }

    protected function _trimpath($path)
    {
        return str_replace(array('/', '\\', '//', '\\\\'), self::DIR_SEP, $path);
    }

    protected function _getTplFile($file)
    {
        return $this->_trimpath($this->_options['template_dir'] . self::DIR_SEP . $file);
    }

    protected function _getCacheFile($file)
    {
        $file = preg_replace('/\.[a-z0-9\-_]+$/i', '.cache.php', $file);
        return $this->_trimpath($this->_options['cache_dir'] . self::DIR_SEP . $file);
    }

    protected function _makepath($path)
    {
        $dirs = explode(self::DIR_SEP, dirname($this->_trimpath($path)));
        $tmp = '';
        foreach ($dirs as $dir) {
            $tmp .= $dir . self::DIR_SEP;
            if (!file_exists($tmp) && !@mkdir($tmp, 0777))
                return $tmp;
        }
        return true;
    }

    protected function _throwException($message)
    {
        throw new Exception($message);
    }

}



  
   $options = array(
       'template_dir' => "$tpldir", 
	   'cache_dir' => "$cachedir",
	   'auto_update' => $tplrefresh, 
	   'cache_lifetime' => 0, 
   );
   $template = Template::getInstance();
   $template->setOptions($options);



