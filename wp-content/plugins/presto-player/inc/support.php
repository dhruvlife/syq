<?php

if (!function_exists('presto_player')) :
    function presto_player($id)
    {
        return do_shortcode('[presto_player id=' . $id . ']');
    }
endif;


/**
 * Just in case Ray is not installed 
 * but forgot to remove it.
 */
if(!function_exists('ray')) {
  class Presto_Ray_Dummy_Class {
      function __call($funName, $arguments) {
          return new Presto_Ray_Dummy_Class();
      }

      static function ray(...$args) {
          return new Presto_Ray_Dummy_Class();
      }
      
      function __get($propertyName) {
          return null;
      }

      function __set($property, $value) {
      }
  }

  function ray(...$args) {   
      return Presto_Ray_Dummy_Class::ray(...$args);
  }
}
