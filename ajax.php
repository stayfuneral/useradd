<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if ($_POST) {

    $arFields = [
        'NAME' => htmlspecialchars($_POST['name']),
        'LAST_NAME' => htmlspecialchars($_POST['lastName']),
        'ACTIVE' => 'Y',
        'LOGIN' => preg_replace("/\@.+/", "", htmlspecialchars($_POST['email'])),
        'PASSWORD' => 'Qwerty#123',
        'CONFIRM_PASSWORD' => 'Qwerty#123',
        'EMAIL' => htmlspecialchars($_POST['email']),
        'UF_DEPARTMENT' => [$_POST['department']],
        'WORK_POSITION' => htmlspecialchars($_POST['position']),
        'PERSONAL_GENDER' => $_POST['gender'],
        'GROUP_ID' => [11],
        'LID' => 's1',
        'WORK_PHONE' => '8 (383) 209-06-90'
    ];
    if($_POST['ufHead'] == true) {
        $arFields['GROUP_ID'][] = 9;
    }
    $response = [];
    $user = new CUser;
    $ID = $user->Add($arFields);
    if (intval($ID) > 0) {
        if ($_POST['ufHead'] == true) {
            $dep = new CIBlockSection;
            $dep->Update($_POST['department'], ["UF_HEAD" => $ID]);
        }
        
        CModule::IncludeModule('tasks');
        $TSK = new CTasks;
        $desc = '<p>'.$arFields['NAME'] . ' ' . $arFields['LAST_NAME'] . ',</p>
            <p>Вам необходимо добавить в свой профиль следующие данные:</p>
            <ul>
		<li>Мобильный телефон</li>
		<li>Внутренний номер телефона</li>
		<li>Фото профиля</li>
		<li>Дата рождения</li>
		<li>IP-адрес (наведите курсор мыши на значок с глазом в правом нижнем углу возле часов) либо название сервера (например, Nika)</li>
            </ul>
            <p><a href="https://helpdesk.bitrix24.ru/open/5392179/">Инструкция по заполнению профиля</a></p>
            <p>Перед тем, как перейти на страницу редактирования профиля, возьмите данную задачу в работу, нажав на кнопку "Начать учёт моего времени". После выполнения задачи нажмите на кнопку "Завершить".</p>
            <p>Если будут какие-то вопросы, задавайте их в комментарии к данной задаче</p>';

        $taskParams = [
            'TITLE' => 'Заполнить профиль',
            'DESCRIPTION' => $desc,
            'CREATED_BY' => 329,
            'RESPONSIBLE_ID' => $ID,
            'AUDITORS' => [331],
            'DEADLINE' => ConvertTimeStamp(AddToTimeStamp(['DD' => 2], time()), "SHORT", "ru"),
            'ALLOW_CHANGE_DEADLINE' => false,
            'TASK_CONTROL' => true,
            'ALLOW_TIME_TRACKING' => true
        ];
        $taskId = $TSK->Add($taskParams);
        $message = '<p><b>Данные для входа:</b></p>'
                . '<p><a href="https://bx.bookingboard.ru">https://bx.bookingboard.ru</a><br>'
                . 'Логин: '.$arFields['LOGIN'].'<br>'
                . 'Пароль: '.$arFields['PASSWORD'].'</p>'
                . '<p>Не забудьте сменить пароль при первой авторизации.</p>';
        if(intval($taskId) > 0) {
            $response['taskId'] = $taskId;
        }
        
        CUser::SendUserInfo($ID, 's1', $message, true, 'USER_ADD');
        
        CModule::IncludeModule('iblock');
        $department = CIBlockSection::GetByID($_POST['department']);
        $dep = $department->GetNext(true, false);
        
        $notifyParams = [
            'BOT_ID' => 493,
            'CLIENT_ID' => 'mrBean',
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
        callCurl('https://bx.bookingboard.ru/bot/events.php', $notifyParams);
        $response['result'] = 'success';
        $response['ID'] = $ID;
        $response['NAME'] = $arFields['NAME'] . ' ' . $arFields['LAST_NAME'];
    } else {
        $response['result'] = 'error';
        $response['result_message'] = $user->LAST_ERROR;
    }
    echo json_encode($response);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");

