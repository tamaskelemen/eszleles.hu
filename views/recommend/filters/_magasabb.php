<?php
/* @var $this yii\web\View */
/* @var $filter app\components\recommender\Filter */
/* @var $value bool */

echo $this->render('_checkbox', [
    'filter' => $filter,
    'value' => $value,
    'info' => 'Nem zár ki semmit, csak gyakrabban ajánl magasan álló objektumot, mert magasan kevesebb levegőn át nézünk: '
        . 'a zenitben (90°) állót körülbelül nyolcszor akkora eséllyel, mint egy 20° magasan lévőt. '
        . 'Hogy milyen alacsonyan álló jöhet még szóba, azt a „Legalább ilyen magasan” szűrő szabja meg.',
]);
