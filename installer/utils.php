<?php


/**
 * Use PHP5 autoload for dynamic class loading
 * (copy from program/incllude/iniset.php)
 */
function __autoload($classname)
{
  $filename = preg_replace(
      array('/MDB2_(.+)/', '/Mail_(.+)/', '/^html_.+/', '/^utf8$/'),
      array('MDB2/\\1', 'Mail/\\1', 'html', 'utf8.class'),
      $classname
  );
  include_once $filename. '.php';
}


/**
 * Shortcut function for htmlentities()
 *
 * @param string String to quote
 * @return string The html-encoded string
 */
function Q($string)
{
  return htmlentities($string, ENT_COMPAT, 'UTF-8');
}


/**
 * Fake rinternal error handler to catch errors
 */
function raise_error($p)
{
  $rci = rcube_install::get_instance();
  $rci->raise_error($p);
}


