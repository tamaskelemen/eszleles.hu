<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $id string */
/* @var $options array */
?>
<?= Html::beginTag('div', $options) ?>
    <label for="<?= $id ?>-search">Azonosított objektumok</label>
    <?= $this->render('//_common-items/_infoTip', [
        'label' => 'Mik azok az azonosított objektumok?',
        'text' => 'Azok az égi objektumok, amelyekről az észlelés szól. Ezek alapján kerül fel az égtérképre, és az adatlapján is '
            . 'megjelennek. A felismerés automatikus, de csak az objektum neve alapján működik (pl. M 42, NGC 7000, Rozetta-köd), '
            . 'a képet nem elemezzük (nincs platesolving). Ami hiányzik, azt a keresővel kézzel is hozzáadhatod, a tévesen '
            . 'felismert objektumot pedig az × gombbal eltávolíthatod.',
    ]) ?>

    <ul class="sky-objects__list" aria-live="polite"></ul>
    <p class="sky-objects__empty">Nem ismertünk fel objektumot. Keresd meg és add hozzá, ha szeretnéd, hogy megjelenjen az égtérképen.</p>

    <div class="sky-objects__search">
        <input type="text" id="<?= $id ?>-search" class="form-control" autocomplete="off" maxlength="100"
               placeholder="Objektum hozzáadása, pl. M 42 vagy Rozetta-köd"
        >
        <div class="dropdown-menu"></div>
    </div>
<?= Html::endTag('div') ?>
