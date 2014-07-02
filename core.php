<?php
// Polyfill gettext library
if (!function_exists("_")) {
  function _($a) { return $a; }
}

class ImageOps {
  static $ops = array();
  static $aliases = array();
  static $libraries = array();

  var $libraries_initialized = array();
  var $error = null;

  static function add_library($name, $support, $ops) {
    // Any
    $supporting = explode(",", $support);

    foreach ($supporting as $ext) {
      !isset(self::$ops[$ext]) and (self::$ops[$ext] = array());

      foreach ($ops as $name => $fun) {
        self::$ops[$s][$name] = array("lib" => $name, "fun" => $fun);
      }
    }

    self::$libraries[$name] = $ops;
  }

  static function add_alias($to, $from) {
    self::$aliases[$from] = $to;
  }

  function __construct($path) {
    // Deduct extension from file path
    list($ext,) = explode(".", strrev($path));
    $ext = $ext ? strrev($path) : "";

    // Maybe it's an alias? Think: JPG -> JPEG
    if (isset (self::$aliases[$ext])) {
      $ext = self::$aliases[$ext];
    }

    // Set the variables
    $this->ext = $ext;

    $this->file = $path;
  }

  function __call($name, $args) {
    $op = null;    

    // Check if OP exists, or else
    isset (self::$ops[$this->ext][$name]) and ($op = self::$ops[$this->ext][$name]);
    isset (self::$ops["*"       ][$name]) and ($op = self::$ops["*"       ][$name]);

    // Operation may not exist in any library; in this case drop an error
    if (!$op) {
      $this->error = sprintf(_("Operation %s doesn't exist in any library!"), $name);
      return false;
    }

    // Try to initialize a library if needed
    if (!isset ($this->libraries_initialized[$op['lib']]) {
      if (isset(self::$libraries[$op['lib']]['initialize'])) {
        $this->error = null;
        if (!self::$libraries[$op['lib']]['initialize']($this)) {
          $this->error or ($this->error = sprintf(_("Failed to initialize library %s!"), $op['lib']));
          return false;
        }
      }
      $this->libraries_initialized[$op['lib']] = true;
    }

    // Add self-reference at the beginning of the array
    array_unshift($args, $this);

    // Call the op
    return call_user_func_array($op['fun'], $args);
  }
}
