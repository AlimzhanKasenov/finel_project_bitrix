<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'bitrix:mobile.crm.activities.list',
	'',
	array(
		'UID' => 'mobile_crm_activity_list',
		'ACTIVITY_SHOW_URL_TEMPLATE' => '/mobile/crm/activities/view.php?activity_id=#activity_id#',
		'ACTIVITY_CREATE_URL_TEMPLATE' => '/mobile/crm/activities/edit.php?owner_type=#owner_type#&owner_id=#owner_id#&type_id=#type_id#',
		'ACTIVITY_EDIT_URL_TEMPLATE' => '/mobile/crm/activities/edit.php?activity_id=#activity_id#',
		'TASK_SHOW_URL_TEMPLATE' => '/mobile/tasks/snmrouter/index.php?routePage=view&USER_ID=#user_id#&TASK_ID=#task_id#',
		'USER_PROFILE_URL_TEMPLATE' => '/mobile/users/?user_id=#user_id#'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
