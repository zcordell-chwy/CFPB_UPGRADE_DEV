<?php

namespace PS\CPM;

interface CPMExtended{
  /**
   * Method to process an object from a CPM. The object should be passed by reference from the CPM in Process Designer
   * @param int $run_mode Indicates if this is a test or production
   * @param int $action The action being performed
   * @param mixed $obj The RNCPHP object being manipulated
   * @param int $n_cycles
   * @return bool
   */
  public static function apply($run_mode, $action, &$obj, $n_cycles);
  /**
   * Method to process an object from a CPM. The object should be passed by reference from the CPM in Process Designer
   * @param string $action The action being performed
   * @param mixed $object The RNCPHP object being tested
   * @return bool
   */
  public static function validate($action, &$object);
}
