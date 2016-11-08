<?php
/*
 +--------------------------------------------------------------------+
 | be.chiro.civi.eventsearch                                          |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
 | Adaptions for event list Copyright Chirojeugd-Vlaanderen vzw 2016  |
 | Licensed to CiviCRM under the Academic Free License version 3.0.   |
 +--------------------------------------------------------------------+
 | This extension is free software; you can copy, modify, and         |
 | distribute it under the terms of the GNU Affero General Public     |
 | License Version 3, 19 November 2007. and the CiviCRM Licensing     |
 | Exception.                                                         |
 |                                                                    |
 | This extension is distributed in the hope that it will be useful,  |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of     |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2016
 * $Id$
 *
 */
class CRM_Eventsearch_Form_Report_ParticipantStats extends CRM_Report_Form_Event {

  protected $_summary = NULL;

  protected $_add2groupSupported = FALSE;

  protected $_customGroupExtends = array(
    'Event',
  );
  public $_drilldownReport = array('event/income' => 'Link to Detail Report');


  /**
   * Constructor
   */
  public function __construct() {
    $this->_columns = array(
      'civicrm_event' => array(
        'dao' => 'CRM_Event_DAO_Event',
        'fields' => array(
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'title' => array(
            'title' => ts('Event Title'),
            'required' => TRUE,
          ),
          'event_type_id' => array(
            'title' => ts('Event Type'),
            'required' => TRUE,
          ),
          'event_start_date' => array(
            'title' => ts('Event Start Date'),
          ),
          'event_end_date' => array('title' => ts('Event End Date')),
        ),
        'filters' => array(
          'id' => array(
            'title' => ts('Event'),
            'operatorType' => CRM_Report_Form::OP_ENTITYREF,
            'type' => CRM_Utils_Type::T_INT,
            'attributes' => array('select' => array('minimumInputLength' => 0)),
          ),
          'event_type_id' => array(
            'name' => 'event_type_id',
            'title' => ts('Event Type'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Core_OptionGroup::values('event_type'),
          ),
          'event_start_date' => array(
            'title' => 'Event Start Date',
            'operatorType' => CRM_Report_Form::OP_DATE,
          ),
          'event_end_date' => array(
            'title' => 'Event End Date',
            'operatorType' => CRM_Report_Form::OP_DATE,
          ),
        ),
        'order_bys' => array(
          'title' => array('title' => ts('Event Title')),
          'event_start_date' => NULL,
          'event_end_date' => NULL,
        ),
        'group_bys' => array(
          'id' => array(
            'title' => ts('Event ID'),
            'default' => TRUE,
          ),
        ),
      ),
      'civicrm_participant' => array(
        'dao' => 'CRM_Event_DAO_Participant',
      ),
    );
    parent::__construct();
  }

  public function preProcess() {
    parent::preProcess();
  }

  public function select() {
    $select = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) ||
            !empty($this->_params['fields'][$fieldName])
          ) {
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
          }
        }
      }
    }

    $this->_select = 'SELECT ' . implode(', ', $select);
  }

  public function from() {
    $this->_from = " FROM civicrm_event {$this->_aliases['civicrm_event']}
      LEFT OUTER JOIN civicrm_participant {$this->_aliases['civicrm_participant']}
        ON {$this->_aliases['civicrm_event']}.id = {$this->_aliases['civicrm_participant']}.event_id";
  }

  public function where() {
    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('type', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            if ($relative || $from || $to) {
              $clause = $this->dateClause($field['name'], $relative, $from, $to, $field['type']);
            }
          }
          else {
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }
    $clauses[] = "{$this->_aliases['civicrm_event']}.is_template = 0";
    $this->_where = 'WHERE  ' . implode(' AND ', $clauses);
  }

  /**
   * Build header for table.
   */
  public function buildColumnHeaders() {
    $this->_columnHeaders = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) ||
            !empty($this->_params['fields'][$fieldName])
          ) {

            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
          }
        }
      }
    }
  }

  public function postProcess() {

    $this->beginPostProcess();

    $this->buildColumnHeaders();

    $sql = $this->buildQuery(TRUE);

    $dao = CRM_Core_DAO::executeQuery($sql);

    $this->setPager();

    $rows = array();
    $count = 0;
    while ($dao->fetch()) {
      $row = array();
      foreach ($this->_columnHeaders as $key => $value) {
        if (($key == 'civicrm_event_start_date') ||
          ($key == 'civicrm_event_end_date')
        ) {
          //get event start date and end date in custom datetime format
          $row[$key] = CRM_Utils_Date::customFormat($dao->$key);
        }
        else {
          if (isset($dao->$key)) {
            $row[$key] = $dao->$key;
          }
        }
      }
      $rows[] = $row;
    }
    $this->formatDisplay($rows, FALSE);
    unset($this->_columnHeaders['civicrm_event_id']);

    $this->doTemplateAssignment($rows);

    $this->endPostProcess($rows);
  }

  /**
   * Alter display of rows.
   *
   * Iterate through the rows retrieved via SQL and make changes for display purposes,
   * such as rendering contacts as links.
   *
   * @param array $rows
   *   Rows generated by SQL, with an array for each row.
   */
  public function alterDisplay(&$rows) {

    if (is_array($rows)) {
      $eventType = CRM_Core_OptionGroup::values('event_type');

      foreach ($rows as $rowNum => $row) {
        if (empty($row['civicrm_event_title'])) {
          $rows[$rowNum]['civicrm_event_title'] = '???';
        }

        $url = CRM_Utils_System::url("civicrm/event/info", 'id=' . $row['civicrm_event_id'], $this->_absoluteUrl);
        $rows[$rowNum]['civicrm_event_title_link'] = $url;
        $rows[$rowNum]['civicrm_event_title_hover'] = ts('Details bivak');

        // handle event type
        if (array_key_exists('civicrm_event_event_type_id', $row)) {
          if ($value = $row['civicrm_event_event_type_id']) {
            $rows[$rowNum]['civicrm_event_event_type_id'] = $eventType[$value];
          }
        }
      }
    }
  }

}
