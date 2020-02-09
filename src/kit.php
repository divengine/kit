<?php

namespace divengine;

class kit {


	/**
	 * Show message in console
	 *
	 * @param        $msg
	 * @param string $icon
	 */
	static function message($msg, $icon = 'DIV', $date = true)
	{
		echo trim(($icon !== false ? "[$icon] " : "") . ($date ? date("h:i:s") : "") . " $msg")."\n";
	}

  static function copy($from, $to, $silent = false) {

	  if (!file_exists($from)) {
		  if (!$silent) message("File $from not exists");
	  } elseif (!is_file($from)) {
		  message("$from is a folder");
	  } else {
		  $fn = $to;

		  if (file_exists($to) && !is_file($to)) {
			  $fn = pathinfo($from, PATHINFO_FILENAME . (empty(PATHINFO_EXTENSION) ? "" : "." . PATHINFO_EXTENSION));
		  } elseif (file_exists($to) && is_file($to)) {
			  message("Destination file $to exists");
			  return;
		  }

		  message("Copying file to $fn");
		  copy($from, $fn);
	  }
  }

}