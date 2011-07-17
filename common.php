<?php
/**
 * Core Bootstrap
 *
 * This file contains all common system functions and View and Controller classes.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

/**
 * Record memory usage and timestamp and then return difference next run (and restart)
 *
 * @return array
 */
function benchmark()
{
	static $time, $memory;
	$result = array((microtime(true) - $time), (memory_get_usage() - $memory));
	$time = microtime(true);
	$memory = memory_get_usage();
	return $result;
}


/**
 * System registry for storing global objects and services
 *
 * @return object
 */
function registry()
{
	static $service;
	return $service ? $service : ($service = new Service);
}


/**
 * Set a message to show to the user (error, warning, success, or message).
 *
 * @param string $type of message
 * @param string $value the message to store
 */
function message($type = NULL, $value = NULL)
{
	static $message = array();

	$h = '';

	if($value)
	{
		$message[$type][] = $value;
	}
	elseif($type)
	{
		if(isset($message[$type]))
		{
			foreach($message[$type] as $value)
			{
				$h .= "<div class = \"$type\">$value</div>";
			}
		}
	}
	else
	{
		foreach($message as $type => $data)
		{
			foreach($data as $value)
			{
				$h .= "<div class = \"$type\">$value</div>";
			}
		}
	}

	return $h;
}


/**
 * Attach (or remove) multiple callbacks to an event and trigger those callbacks when that event is called.
 *
 * @param string $k the name of the event to run
 * @param mixed $v the optional value to pass to each callback
 * @param mixed $callback the method or function to call - FALSE to remove all callbacks for event
 */
function event($key, $value = NULL, $callback = NULL)
{
	static $events;

	// Adding or removing a callback?
	if($callback !== NULL)
	{
		if($callback)
		{
			$events[$key][] = $callback;
		}
		else
		{
			unset($events[$key]);
		}
	}
	elseif(isset($events[$key])) // Fire a callback
	{
		foreach($events[$key] as $function)
		{
			$value = call_user_func($function, $value);
		}
		return $value;
	}
}


/**
 * Fetch a config value from a module configuration file
 *
 * @param string $key the config key name
 * @param string $module the module name
 * @return mixed
 */
function config($key, $module = 'App')
{
	static $config = array();

	if(empty($config[$module]))
	{
		$config[$module] = new \Core\Config('config', $module);
		//require(SP . $module . DIRECTORY_SEPARATOR . 'config' . EXT);
		//$c[$module] = $config;
	}

	//return ($key ? $c[$module][$key] : $c[$module]);
	return ($key ? $config[$module]->$key : $config[$module]);
}


/**
 * Fetch the language text for the given line.
 *
 * @param string $key the language key name
 * @param string $module the module name
 * @return string
 */
function lang($key, $module = 'App')
{
	return \Core\Lang::get($key, $module);
}


/**
 * Automatically load the given class
 *
 * @param string $class name
 */
function __autoload($className)
{
	$className = ltrim($className, '\\');
	$fileName  = '';
	$namespace = '';

	if ($lastNsPos = strripos($className, '\\'))
	{
		$namespace = substr($className, 0, $lastNsPos);
		$className = substr($className, $lastNsPos + 1);
		$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}

	$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

	require SP . $fileName;
}


/**
 * Return an HTML safe dump of the given variable(s) surrounded by "pre" tags.
 * You can pass any number of variables (of any type) to this function.
 *
 * @param mixed
 * @return string
 */
function dump()
{
	$string = '';
	foreach(func_get_args() as $value)
	{
		$string .= '<pre>' . h($value === NULL ? 'NULL' : (is_scalar($value) ? $value : print_r($value, TRUE))) . "</pre>\n";
	}
	return $string;
}


/**
 * Safely fetch a $_POST value, defaulting to the value provided if the key is
 * not found.
 *
 * @param string $key name
 * @param mixed $default value if key is not found
 * @param boolean $string TRUE to require string type
 * @return mixed
 */
function post($key, $default = NULL, $string = FALSE)
{
	if(isset($_POST[$key]))
	{
		return $string ? str($_POST[$key], $default) : $_POST[$key];
	}
	return $default;
}


/**
 * Safely fetch a $_GET value, defaulting to the value provided if the key is
 * not found.
 *
 * @param string $key name
 * @param mixed $default value if key is not found
 * @param boolean $string TRUE to require string type
 * @return mixed
 */
function get($key, $default = NULL, $string = FALSE)
{
	if(isset($_GET[$key]))
	{
		return $string ? str($_GET[$key], $default) : $_GET[$key];
	}
	return $default;
}


/**
 * @Depreciated, use getenv()
 *
 * Safely fetch a $_SERVER value, defaulting to the value provided if the key is
 * not found.
 *
 * @param string $k the key name
 * @param mixed $d the default value if key is not found
 * @return mixed
 *
function server($k, $d = NULL)
{
	return isset($_SERVER[$k]) ? $_SERVER[$k] : $d;
}
*/

/**
 * Safely fetch a $_SESSION value, defaulting to the value provided if the key is
 * not found.
 *
 * @param string $k the post key
 * @param mixed $d the default value if key is not found
 * @return mixed
 */
function session($k, $d = NULL)
{
	return isset($_SESSION[$k]) ? $_SESSION[$k] : $d;
}


/**
 * Create a random 32 character MD5 token
 *
 * @return string
 */
function token()
{
	return md5(str_shuffle(chr(mt_rand(32, 126)) . uniqid() . microtime(TRUE)));
}


/**
 * Write to the log file
 *
 * @param string $m the message to save
 * @return bool
 */
function log_message($message)
{
	$path = SP . 'App'. DIRECTORY_SEPARATOR . 'Log' . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';

	// Append date and IP to log message
	return error_log(date('H:i:s ') . getenv('REMOTE_ADDR') . " $message\n", 3, $path);
}


/**
 * Send a HTTP header redirect using "location" or "refresh".
 *
 * @param string $uri the URI string
 * @param int $c the HTTP status code
 * @param string $method either location or redirect
 */
function redirect($uri = '', $code = 302, $method = 'location')
{
	$uri = site_url($uri);
	header($method == 'refresh' ? "Refresh:0;url = $uri" : "Location: $uri", TRUE, $code);
}


/**
 * Type cast a scalar variable into an a valid integer between the given min/max values.
 * If the value is not a valid numeric value then min will be returned.
 *
 * @param int $int the value to convert
 * @param int $min the lowest value allowed
 * @param int $max the heighest value allowed
 * @return int|null
 */
function int($int, $min = NULL, $max = NULL)
{
	$int = (is_int($int) OR ctype_digit($int)) ? (int) $int : $min;

	if($min !== NULL AND $int < $min)
	{
		$int = $min;
	}

	if($max !== NULL AND $int > $max)
	{
		$int = $max;
	}

	return $int;
}


/**
 * Type cast the given variable into a string - on fail return default.
 *
 * @param mixed $string the value to convert
 * @param string $default the default value to assign
 * @return string
 */
function str($str, $default = '')
{
	return is_scalar($str) ? (string) $str : $default;
}


/**
 * Return the full URL to a path on this site or another.
 *
 * @param string $uri may contain another sites TLD
 * @return string
 */
function site_url($uri = NULL)
{
	return (strpos($uri, '://') === FALSE ? \Core\URL::get() : '') . ltrim($uri, '/');
}


/**
 * Return the full URL to the theme folder
 *
 * @param boolean $include_domain to add the TLD
 * @return string
 */
function current_url($include_domain = FALSE)
{
	return $include_domain ? \Core\URL::get(TRUE) : config('site_url') . \Core\URL::path();
}


/**
 * Convert a string from one encoding to another encoding
 * and remove invalid bytes sequences.
 *
 * @param string $string to convert
 * @param string $to encoding you want the string in
 * @param string $from encoding that string is in
 * @return string
 */
function encode($string, $to = 'UTF-8', $from = 'UTF-8')
{
	// ASCII is already valid UTF-8
	if($to == 'UTF-8' AND is_ascii($string))
	{
		return $string;
	}

	// Convert the string
	return @iconv($from, $to . '//TRANSLIT//IGNORE', $string);
}


/**
 * Tests whether a string contains only 7bit ASCII characters.
 *
 * @param string $string to check
 * @return bool
 */
function is_ascii($string)
{
	return ! preg_match('/[^\x00-\x7F]/S', $string);
}


/**
 * Encode a string so it is safe to pass through the URL
 *
 * @param string $string to encode
 * @return string
 */
function base64_url_encode($string = NULL)
{
	return strtr(base64_encode($string), '+/=', '-_~');
}


/**
 * Decode a string passed through the URL
 *
 * @param string $string to decode
 * @return string
 */
function base64_url_decode($string = NULL)
{
	return base64_decode(strtr($string, '-_~', '+/='));
}


/**
 * Convert special characters to HTML safe entities.
 *
 * @param string $string to encode
 * @return string
 */
function h($string)
{
	return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
}


/**
 * Filter a valid UTF-8 string so that it contains only words, numbers,
 * dashes, underscores, periods, and spaces - all of which are safe
 * characters to use in file names, URI, XML, JSON, and (X)HTML.
 *
 * @param string $string to clean
 * @param bool $spaces TRUE to allow spaces
 * @return string
 */
function sanitize($string, $spaces = TRUE)
{
	$search = array(
		'/[^\w\-\. ]+/u',			// Remove non safe characters
		'/\s\s+/',					// Remove extra whitespace
		'/\.\.+/', '/--+/', '/__+/'	// Remove duplicate symbols
	);

	$string = preg_replace($search, array(' ', ' ', '.', '-', '_'), $string);

	if( ! $spaces)
	{
		$string = preg_replace('/--+/', '-', str_replace(' ', '-', $string));
	}

	return trim($string, '-._ ');
}


/**
 * Create a SEO friendly URL string from a valid UTF-8 string.
 *
 * @param string $string to filter
 * @return string
 */
function sanitize_url($string)
{
	return urlencode(mb_strtolower(sanitize($string, FALSE)));
}


/**
 * Filter a valid UTF-8 string to be file name safe.
 *
 * @param string $string to filter
 * @return string
 */
function sanitize_filename($string)
{
	return sanitize($string, FALSE);
}


/**
 * Return a SQLite/MySQL/PostgreSQL datetime string
 *
 * @param int $timestamp
 */
function sql_date($timestamp = NULL)
{
	return date('Y-m-d H:i:s', $timestamp ?: time());
}

// End