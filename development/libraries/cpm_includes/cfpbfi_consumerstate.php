<?php

namespace PS\CPM;

define("CUSTOM_SCRIPT", true);

require_once get_cfg_var("doc_root") . "/euf/application/production/optimized/libraries/PSLog-2.0.php";
require_once "cpm_interface.php"; //Required for CPMs broken into different files

abstract class ConsumerState implements CPMExtended{
  /**
   * @var $_log Stores a logger instance
   */
  protected static $_log;

  /**
   * Method to process an object from a CPM. The object should be passed by reference from the CPM in Process Designer
   * @param string $run_mode Indicates if this is a test or production
   * @param string $action The action being performed
   * @param mixed $obj The RNCPHP object being manipulated
   * @param int $n_cycles
   * @return bool
   */
  public static function apply($run_mode, $action, &$obj, $n_cycles) {
    static::$_log = new \PS\Log\v2\log(\PS\Log\v2\Type::CPM);
    static::$_log->subtype("cfpbfi_incident")->source("cfpbfi_consumerstate.php");

    try{
      //If this is create, then do this.
      if ($action == \RightNow\CPM\v1\ActionCreate) {
        static::_setConsumerState($run_mode, $action, $obj, $n_cycles);
      }
      else if ($action == \RightNow\CPM\v1\ActionUpdate) {
        static::_setConsumerState($run_mode, $action, $obj, $n_cycles);
      }

    } catch(\Exception $e) {
      static::$_log->error(sprintf("File: (%s) Line (%d) - Exception Thrown.", $e->getFile(), $e->getLine()), null, $e->getMessage());
      return false;
    }
    return true;
  }
  /**
   * Method to process an object from a CPM. The object should be passed by reference from the CPM in Process Designer
   * @param string $action The action being performed
   * @param mixed $object The RNCPHP object being tested
   * @return bool
   */
  public static function validate($action, &$object) {
    //Log to file in tests since db gets rolled back.
    static::$_log = new \PS\Log\v2\log(\PS\Log\v2\Type::CPM);
    static::$_log->logToFile(true)->logThreshold(\PS\Log\v2\Severity::Debug)->subtype("cfpbfi_consumerstate.php")->source("vangent1_auditlog.php");

    if (empty($object->ID)) {
      static::$_log->debug("Failed SetConsumerState Test Cases -- No incident ID");
      return false;
    }

    if ($action == \RightNow\CPM\v1\ActionCreate) {
      static::$_log->debug("SetConsumerState Create Test Cases");
      if (!static::_setConsumerState($object)) {
        static::$_log->debug("Failed SetConsumerState Create Test Cases");
        return false;
      }
    }
    else if ($action == \RightNow\CPM\v1\ActionUpdate) {
        static::$_log->debug("SetConsumerState Update Test Cases");
        if (!static::_setConsumerState($object)) {
          static::$_log->debug("Failed SetConsumerState Update Test Cases");
          return false;
        }
      }

    static::$_log->debug("Passed SetConsumerState Test Cases");

    return true;
  }

  /**
   * This method will run functional that should happen on create
   * @param RNCPHP\Incident $incident The incident to update
   * @return bool
   */
  protected static function _setConsumerState(&$incident) {
    try{
      if ( isset( $incident->CustomFields->c->ccbill_state->LookupName ) && $incident->CustomFields->c->ccbill_state->LookupName != '' ) {
        $incident->CustomFields->c->consumer_state = $incident->CustomFields->c->ccbill_state->LookupName;
      }
      else if ( isset( $incident->CustomFields->c->onbehalf_state->LookupName ) && $incident->CustomFields->c->onbehalf_state != '' ) {
          $incident->CustomFields->c->consumer_state = $incident->CustomFields->c->onbehalf_state->LookupName;
        }
      else if ( isset( $incident->CustomFields->c->ccmail_state->LookupName ) && $incident->CustomFields->c->ccmail_state != '' ) {
          $incident->CustomFields->c->consumer_state = $incident->CustomFields->c->ccmail_state->LookupName;
        }
      else {
        $contact = $incident->PrimaryContact;
        if (!empty($contact->Address->StateOrProvince->LookupName)) {
          $incident->CustomFields->c->consumer_state = $contact->Address->StateOrProvince->LookupName;
        }
      }

      return true;
    } catch (\Exception $e) {
      static::$_log->error(sprintf("File: (%s) Line (%d) - Exception Thrown.", $e->getFile(), $e->getLine()), null, $e->getMessage());
    }

    return false;
  }
}