<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Добавление нового пользователя");

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss("https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css");
Asset::getInstance()->addJs('/useradd/script.js');
\Bitrix\Main\UI\Extension::load("ui.buttons");
CModule::IncludeModule('iblock');
$arFilter = ['IBLOCK_ID' => 1];
$arSelect = ['ID', 'NAME', 'LEFT_MARGIN', 'DEPTH_LEVEL'];
$tree = CIBlockSection::GetList(['left_margin' => 'asc', 'depth_level' => 'asc'], $arFilter, $arSelect);
$list = [];
while ($section = $tree->GetNext(true, false)) {
    switch ($section['DEPTH_LEVEL']) {
        case 1:
            $list[$section['ID']] = $section['NAME'];
            break;
        case 2:
            $list[$section['ID']] = '.' . $section['NAME'];
            break;
        case 3:
            $list[$section['ID']] = '..' . $section['NAME'];
            break;
        case 4:
            $list[$section['ID']] = '...' . $section['NAME'];
            break;
        case 5:
            $list[$section['ID']] = '....' . $section['NAME'];
            break;
    }
}
$arGender = [
    'M' => 'Мужской',
    'F' => 'Женский'
];
?>

<div class="row mt-1 mb-1">
    <div class="col-md-2">Имя</div>
    <div class="col-md-2">
        <input class="form-control" id="name" type="text">
    </div>
</div>
<div class="row mt-1 mb-1">
    <div class="col-md-2">Фамилия</div>
    <div class="col-md-2">
        <input class="form-control" id="lastName" type="text">
    </div>
</div>
<div class="row mt-1 mb-1">
    <div class="col-md-2">E-mail</div>
    <div class="col-md-2">
        <input class="form-control" id="email" type="email">
    </div>
</div>
<div class="row mt-1 mb-1">
    <div class="col-md-2">Пол</div>
    <div class="col-md-2">
        <select class="form-control" id="gender" name="" id="">
            <option value=""></option>
            <?php foreach ($arGender as $key => $value) { ?>   
                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
<?php } ?>
        </select>
    </div>
</div>
<div class="row mt-1 mb-1">
    <div class="col-md-2">Отдел</div>
    <div class="col-md-2">
        <select class="form-control" id="department" name="" id="">
            <option value=""></option>
            <?php foreach ($list as $depId => $depName) { ?>    
                <option value="<?php echo $depId; ?>"><?php echo $depName; ?></option>
<?php } ?>
        </select>
    </div>
</div>
<div class="row mt-1 mb-1">
    <div class="col-md-2">Должность</div>
    <div class="col-md-2">
        <input class="form-control" id="position" type="text">
    </div>
</div>
<div class="row mt-1 mb-1">
    <div class="col-md-2">Руководитель</div>
    <div class="col-md-2">
        <input id="ufHead" type="checkbox">
    </div>
</div>
<div class="row mt-1 mb-1">
    <div class="col-md-2"></div>
    <div class="col-md-2">
        <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg">Добавить</button>
    </div>
</div>
<div class="row">
    <div id="result" class="col-12 mt-2 mb-2 mx-auto"></div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>