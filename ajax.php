<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if ($_POST) {

    $arFields = [
        'NAME' => htmlspecialchars($_POST['name']),
        'LAST_NAME' => htmlspecialchars($_POST['lastName']),
        'ACTIVE' => 'Y',
        'LOGIN' => preg_replace("/\@.+/", "", htmlspecialchars($_POST['email'])),
        'PASSWORD' => '******',
        'CONFIRM_PASSWORD' => '******',
        'EMAIL' => htmlspecialchars($_POST['email']),
        'UF_DEPARTMENT' => [$_POST['department']],
        'WORK_POSITION' => htmlspecialchars($_POST['position']),
        'PERSONAL_GENDER' => $_POST['gender'],
        'GROUP_ID' => [11],
        'LID' => 's1',
        'WORK_PHONE' => '8 (XXX) XXX-XX-XX'
    ];
    if($_POST['ufHead'] == true) {
        $arFields['GROUP_ID'][] = 9;
    }
    $response = [];

    // create new user
    $user = new CUser;
    $ID = $user->Add($arFields);
    
    if (intval($ID) > 0) {
        if ($_POST['ufHead'] == true) { // change department
            $dep = new CIBlockSection;
            $dep->Update($_POST['department'], ["UF_HEAD" => $ID]);
        }
        
        
        // create task for new user
        CModule::IncludeModule('tasks');
        $TSK = new CTasks;
        $desc = $arFields['NAME'] . ' ' . $arFields['LAST_NAME'] . ',
Вам необходимо добавить в свой профиль следующие данные:
[LIST]
    [*]Мобильный телефон
    [*]Внутренний номер телефона
    [*]Фото профиля
    [*]Дата рождения
[/LIST]
[URL=https://helpdesk.bitrix24.ru/open/5392179/]Инструкция по заполнению профиля[/URL]

Перед тем, как перейти на страницу редактирования профиля, возьмите данную задачу в работу, нажав на кнопку [B]"Начать учёт моего времени"[/B]. После выполнения задачи нажмите на кнопку [B]"Завершить"[/B].

В случае возникновения каких-либо вопросов, вы можете задать их в комментариях к данной задаче.';

        $taskParams = [
            'TITLE' => 'Заполнить профиль',
            'DESCRIPTION' => $desc,
            'CREATED_BY' => $hrId,
            'RESPONSIBLE_ID' => $ID,
            'AUDITORS' => [331],
            'DEADLINE' => ConvertTimeStamp(AddToTimeStamp(['DD' => 2], time()), "SHORT", "ru"),
            'ALLOW_CHANGE_DEADLINE' => false,
            'TASK_CONTROL' => true,
            'ALLOW_TIME_TRACKING' => true,
            'DESCRIPTION_IN_BBCODE' => 'Y'
        ];
        $taskId = $TSK->Add($taskParams);
        $message = '<p><b>Данные для входа:</b></p>'
                . '<p><a href="https://bitrix24.ru">https://bitrix24.ru</a><br>'
                . 'Логин: '.$arFields['LOGIN'].'<br>'
                . 'Пароль: '.$arFields['PASSWORD'].'</p>'
                . '<p>Не забудьте сменить пароль при первой авторизации.</p>';
        if(intval($taskId) > 0) {
            $response['taskId'] = $taskId;
        }
        
        // send registration info to new user's email
        CUser::SendUserInfo($ID, 's1', $message, true, 'USER_ADD');
        
        CModule::IncludeModule('iblock');
        $department = CIBlockSection::GetByID($_POST['department']);
        $dep = $department->GetNext(true, false);
        
        // request to bot for notification
        $notifyParams = [
            'BOT_ID' => $botId,
            'CLIENT_ID' => $botClientId,
            'event' => 'ONAFTERUSERADD',
            'data' => [
                'USER' => [
                    'ID' => $ID,
                    'FULL_NAME' => $arFields['NAME'].' '.$arFields['LAST_NAME'],
                    'NAME' => $arFields['NAME'],
                    'LAST_NAME' => $arFields['LAST_NAME'],
                    'UF_DEPARTMENT_ID' => $_POST['department'],
                    'UF_DEPARTMENT_NAME' => $dep['NAME'],
                    'WORK_POSITION' => $arFields['WORK_POSITION'],
                    'TASK_ID' => $taskId
                ]
            ]
        ];
        callCurl('https://domain.ru/bot/handler.php', $notifyParams);
        //success response
        $response['result'] = 'success';
        $response['ID'] = $ID;
        $response['NAME'] = $arFields['NAME'] . ' ' . $arFields['LAST_NAME'];
    } else { // error response
        $response['result'] = 'error';
        $response['result_message'] = $user->LAST_ERROR;
    }
    echo json_encode($response);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");

