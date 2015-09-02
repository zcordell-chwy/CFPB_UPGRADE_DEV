<?php

namespace PS\CPM;

use \RightNow\Connect\v1_2 as RNCPHP;
use \RightNow\CPM\v1 as RNCPM;

define("CUSTOM_SCRIPT", true);

require_once get_cfg_var("doc_root") . "/euf/application/production/optimized/libraries/PSLog-2.0.php";
require_once "cpm_interface.php"; //Required for CPMs broken into different files

/**
 * Class to implement the auditlog functionality in a CPM
 */
abstract class AuditLog implements CPMExtended{
  /**
   * An array of items on which to capture audit data.
   */
  protected static $_fieldList = array(
    'incident.StatusWithType.Status',
    'incident.c$letter',
    'incident.c$reprocess'
  );

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
    static::$_log->subtype("cfpbfi_incident")->source("cfpbfi_auditlog.php");
    try{
      //If this is create, then do this.
      if ($action == \RightNow\CPM\v1\ActionCreate) {
        static::_writeLogEntry($obj);
      }
      else if ($action == \RightNow\CPM\v1\ActionUpdate) {
        static::_writeLogEntry($obj);
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
    static::$_log->logToFile(true)->logThreshold(\PS\Log\v2\Severity::Debug)->subtype("cfpbfi_incident")->source("cfpbfi_auditlog.php");

    if (empty($object->ID)) {
      static::$_log->debug("Failed AuditLog Test Cases -- No incident ID");
      return false;
    }

    if ($action == \RightNow\CPM\v1\ActionCreate) {
      static::$_log->debug("AuditLog Create Test Cases");
      if (!static::_writeLogEntry($object)) {
        static::$_log->debug("Failed AuditLog Create Test Cases");
        return false;
      }
    }
    else if ($action == \RightNow\CPM\v1\ActionUpdate) {
        static::$_log->debug("AuditLog Update Test Cases");
        if (!static::_writeLogEntry($object)) {
          static::$_log->debug("Failed AuditLog Update Test Cases");
          return false;
        }
      }
    static::$_log->debug("Passed AuditLog Test Cases");

    return true;
  }

  /**
   * Examines the passed incident object and determines what data should be archived,
   * coerces the data into a usable format and ships it off to the database.  Each item
   * that has been modified, and corresponds to an item in the $fieldList array will be
   * saved as a new entry in the auditLog custom object.
   *
   * @param $incident The incident to examine for audit data
   */
  protected function _writeLogEntry(RNCPHP\Incident &$incident) {
    if(!empty($incident)){
      static::$_log->debug("Incident LookupName:" . $incident -> LookupName);
      static::$_log->debug("Incident Status:" . $incident -> StatusWithType -> Status -> LookupName);
      static::$_log->debug("Incident Queue:" . $incident -> Queue -> LookupName);
      static::$_log->debug("Incident AssisngedTo ID:" . $incident -> AssignedTo -> ID);
      static::$_log->debug("Incident object ref for debugging.", $incident);
    }

    $objResults = static::_getExistingEntries($incident);
    static::$_log->debug("Obj Results", null, $objResults);
    $newestEntries = static::_findNewestAuditEntries($objResults);
    static::$_log->debug("Newest Entries", null, $newestEntries);
    $newAuditItems = static::_determineAuditItemDelta($incident, $newestEntries);
    static::$_log->debug("Items to Save", null, $newAuditItems);
    return static::_saveAuditItems($newAuditItems);
  }

  /**
   *Searches for existing auditLog CBO entries that match the passed incident
   *
   * @param $incident
   * @return an array of raw audit log CBO's
   */
  private static function _getExistingEntries(RNCPHP\Incident &$incident) {
    //get existing audit log entries
    try {
      $auditItemFindClause = sprintf("auditLog.auditItem.incident_id = '%s'", $incident -> ID);
      $restrictions = array();

      foreach (static::$_fieldList as $key => $val) {
        $restrictions[] = sprintf("auditLog.auditItem.field_name = '%s'", $val);
      }
      $auditItemFindClause = sprintf("%s and (%s)", $auditItemFindClause, implode(' or ', $restrictions));

      $objResults = RNCPHP\auditLog\auditItem::find($auditItemFindClause);
      return $objResults;
    } catch (\Exception $e) {
      static::$_log->error(sprintf("File: (%s) Line (%d) - Exception Thrown.", $e->getFile(), $e->getLine()), null, $e->getMessage());
    }

    return false;
  }

  /**
   * Determines the most recent entry for each field_name passed.  Audit entries will be examined and separated
   * into groups, each group will contain all audit entries with the same field_name field.  An array will be returned
   * that contains the newest entry from each of these groups.
   *
   * @param $auditEntries an array of cbo audit items
   * @return array of cbo audit items, each with unique field_name field
   */
  private static function _findNewestAuditEntries(array $auditEntries) {
    $newestEntries = array();
    foreach ($auditEntries as $auditEntry) {
      if (!is_null($newestEntries[$auditEntry -> field_name])) {
        //use a temp var for readability
        $existingEntry = $newestEntries[$auditEntry -> field_name];
        if ($existingEntry -> updated_time < $auditEntry -> updated_time) {
          $newestEntries[$auditEntry -> field_name] = $auditEntry;
        }
      } else {
        $newestEntries[$auditEntry -> field_name] = $auditEntry;
      }
    }

    return $newestEntries;
  }

  /**
   * Determines what items to add to the audit log.  Gathers  each of the elements in the class
   * field list from the passed incident.  If the current field is present in the newestEntries array,
   * but unchanged, the field is discarded.  Otherwise the field is added to the newAuditItems array as a new
   * auditItem object, using data from $newestEntries if appropriate, and returned.
   *
   * @param $incident
   * @param $newestEntries array of cbo auditItems
   * @return array of auditItem php objects
   */
  private static function _determineAuditItemDelta(RNCPHP\Incident $incident, array $newestEntries) {
    try{
      static::$_log->debug("Begin determine field delta");
      $newAuditItems = array();
      foreach (static::$_fieldList as $key => $field) {
        if (strpos($field, '.c$') > 0) {
          $fieldArr = explode(".", str_replace('.c$', '.CustomFields->c.', $field));
        } else {
          $fieldArr = explode(".", $field);
        }

        array_shift($fieldArr);

        //get current value
        $incidentSubObj = $incident;
        foreach ($fieldArr as $fieldPart) {
          static::$_log->debug("FieldPart: " . $fieldPart);
          if ($fieldPart === 'CustomFields->c') {
            // when it's a CustomFields->c object,
            // for some reason $incidentSubObj->$fieldPart isset is null
            // lets just hardcode custom fields instead
            $incidentSubObj = $incidentSubObj->CustomFields->c;
          } else if (isset($incidentSubObj -> $fieldPart)) {
              $incidentSubObj = $incidentSubObj -> $fieldPart;
            } else {
            $incidentSubObj = null;
          }
        }
        if (is_null($incidentSubObj)) {
          continue ;
        }

        if ($incidentSubObj instanceof RNCPHP\NamedIDOptList) {
          $incidentSubObj = $incidentSubObj -> LookupName;
        }
        if ($incidentSubObj instanceof RNCPHP\NamedIDLabel) {
          $incidentSubObj = $incidentSubObj -> LookupName;
        }

        //determine what's changed and create audit items where appropriate
        $fieldData = new auditItem();
        $fieldData -> incident = $incident;
        $fieldData -> contact = $incident -> PrimaryContact;
        $fieldData -> updatedTime = time();
        $fieldData -> newVal = (string)$incidentSubObj;
        $fieldData -> assignedAcct = $incident -> AssignedTo -> Account;
        $fieldData -> fieldName = $field;

        if (!isset($newestEntries[$field])) {
          $newAuditItems[] = $fieldData;
        } else {
          $existingEntry = $newestEntries[$field];
          if ($existingEntry -> new_val != $incidentSubObj) {
            $fieldData -> previousUpdateTime = $existingEntry -> updated_time;
            $fieldData -> oldVal = $existingEntry -> new_val;
            $newAuditItems[] = $fieldData;
          }
        }

      }

      static::$_log->debug("Finish determine field delta");

      return $newAuditItems;
    } catch (Exception $e) {
      static::$_log->error(sprintf("File: (%s) Line (%d) - Exception Thrown.", $e->getFile(), $e->getLine()), null, $e->getMessage());
    }

    return false;
  }

  /**
   * Saves an array of auditItem objects to the db
   *
   * @param $newAuditItems
   * @return bool
   */
  private static function _saveAuditItems(array $newAuditItems) {

    //save updated data
    foreach ($newAuditItems as $field) {
      try {
        $auditItem = new RNCPHP\auditLog\auditItem();
        $auditItem -> field_name = $field -> fieldName;
        $auditItem -> assigned_acct = $field -> assignedAcct;
        $auditItem -> invest_id = $field -> invest;
        $auditItem -> incident_id = $field -> incident;
        $auditItem -> contact_id = $field -> contact;
        $auditItem -> prev_update_time = $field -> previousUpdateTime;
        $auditItem -> new_val = (strlen($field -> newVal) > 0) ? $field -> newVal : null;
        $auditItem -> old_val = (strlen($field -> oldVal) > 0) ? $field -> oldVal : null;
        $auditItem -> acct_id = $field -> acct;

        $auditId = $auditItem -> save(RNCPHP\RNObject::SuppressAll);
        static::$_log->debug("Saved audit item: " . $auditId);
      } catch (exception $e) {
        static::$_log->error(sprintf("File: (%s) Line (%d) - Exception Thrown.", $e->getFile(), $e->getLine()), null, $e->getMessage());
        return false;
      }
    }

    return true;
  }

}

/**
 * Audit item class
 */
class auditItem {
  public $id;
  public $contact;
  public $incident;
  public $invest;
  public $fieldName;
  public $oldVal;
  public $newVal;
  public $updatedTime;
  public $previousUpdateTime;
  public $acct;
  public $assignedAcct;
}
