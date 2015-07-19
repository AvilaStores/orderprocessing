<?php

function avila_orders_autoload($className)
{
  $classPath = explode('_', $className);
  if ($classPath[0] != 'Avila') {
    return;
  }
  // Drop 'Orders', and maximum class file path depth in this project is 5.
  $classPath = array_slice($classPath, 1, 4);

  $filePath = dirname(__FILE__) . '/' . implode('/', $classPath) . '.php';
  if (file_exists($filePath)) {
    require_once($filePath);
  }
}

spl_autoload_register('avila_orders_autoload');
