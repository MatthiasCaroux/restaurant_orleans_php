<?php

class Autoloader {
   static function register() {
      spl_autoload_register(array(__CLASS__, 'autoload'));
   }

   static function autoload($fqcn) {
      // $fqcn contains the fully qualified class name like Model\Thread\Message
      // Replace backslashes with forward slashes and append .php
      $path = str_replace('\\', '/', $fqcn);
      require 'Classes/' . $path . '.php';

      // // Load the file from the specified path
      // // require_once('_inc/classes/' . $path);
      // try {
      //    require_once('Classes/Form/Type/' . $path);
      // } catch (\Throwable $th) {
      //    try {
      //       require_once('Classes/Messenger/' . $path);
      //    } catch (\Throwable $th) {
      //       try {
      //          require_once('Classes/' . $path);
      //       } catch (\Throwable $th) {
      //          echo 'Class not found';
      //          echo $th->getMessage();
      //          echo "<br>";
      //       }
      //    }
      // }
      // // require_once('Classes/Form/Types' . $path);
   }
}