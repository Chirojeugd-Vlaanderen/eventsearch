<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return [
  0 => [
    'name' => 'CRM_Eventsearch_Form_Report_ParticipantStats',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'Participant Stats',
      'description' => 'Participant Stats',
      'class_name' => 'CRM_Eventsearch_Form_Report_ParticipantStats',
      'report_url' => 'be.chiro.civi.eventsearch/participantstats',
      'component' => 'CiviEvent',
    ],
  ],
];