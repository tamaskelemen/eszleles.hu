<?php

/* @var $this yii\web\View */
/* @var $latestObs Observe[] */
/* @var $commentData Comment[] */

use app\models\Comment;
use app\models\Observe;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

$this->title = 'VCSE Észlelések';

$this->registerMetaTag(["property" => "og:image", "content" => "/pictures/eszleloret.jpg" ]);

// a linked news post opens in full
$this->registerJs("$('.news article').filter(function () { return '#' + this.id === location.hash; }).find('.collapse').collapse('show');");
?>
<section class="hero">
    <picture>
        <source type="image/webp" srcset="/pictures/eszleloret-960.webp 960w, /pictures/eszleloret-1920.webp 1920w" sizes="100vw">
        <img src="/pictures/eszleloret.jpg" alt="Távcsövek vörös fényben az észlelőréten, fölöttük a Tejút" fetchpriority="high">
    </picture>

    <div class="container hero__content">
        <div class="hero__block">
            <h1>Észlelés.hu</h1>
            <p class="hero__lead">Csillagászati megfigyelések gyűjtőhelye.</p>

            <?= Html::beginForm(['/observe/search'], 'get', ['class' => 'hero__search', 'role' => 'search']) ?>
                <label class="sr-only" for="hero-search">Objektum neve</label>
                <input class="form-control" id="hero-search" type="search" name="ObserveSearch[object_name]" placeholder="Objektum, pl. M42 vagy Jupiter">
                <button class="btn btn-primary" type="submit">Keresés</button>
            <?= Html::endForm() ?>

            <?php if (Yii::$app->user->isGuest) { ?>
                <p class="hero__login">
                    <a href="<?= Url::toRoute('/site/signup') ?>">Regisztrálok</a>,
                    vagy ha már regisztráltál, <a href="<?= Url::toRoute('/site/login') ?>">lépj be</a>.
                </p>
            <?php } ?>
        </div>
    </div>
</section>

<div class="site-index">
    <section class="container section">
        <div class="section__head">
            <h2>Legújabb feltöltések</h2>
            <a href="<?= Url::toRoute('/observe/index') ?>">Összes észlelés</a>
        </div>

        <div class="obs-grid">
            <?php foreach ($latestObs as $obs) { ?>
                <div class="obs-cell">
                    <?= $this->render('../_common-items/_listItem', ['model' => $obs]) ?>
                </div>
            <?php } ?>
        </div>
    </section>

    <section class="container section">
        <h2>Böngéssz típus szerint</h2>
        <div class="eyepieces">
            <?php foreach (Observe::getAllTypes() as $type => $label) { ?>
                <a class="eyepiece" href="<?= Url::toRoute("/{$type}/index") ?>">
                    <span class="eyepiece__field"><?= $this->render('../_common-items/_typeIcon', ['type' => $type]) ?></span>
                    <?= $label ?>
                </a>
            <?php } ?>
        </div>
    </section>

    <section class="moon-band">
        <picture>
            <source type="image/webp" srcset="/pictures/hold_schmallrafi-640.webp 640w, /pictures/hold_schmallrafi-1280.webp 1280w" sizes="(min-width: 992px) 60vw, 100vw">
            <img src="/pictures/hold_schmallrafi.jpg" alt="Holdsarló hamuszürke fénnyel, fátyolfelhők mögött" loading="lazy">
        </picture>
        <div class="container">
            <div class="moon-band__text">
                <h2>Egy korszerű észlelésfeltöltő</h2>
                <p>
                    Az amatőrcsillagászat fizetés és tiszteletdíj nélkül végzett csillagászat.
                    Egy-egy mélyég-objektum vagy üstökös megtekintése, látványának, alakjának,
                    részleteinek megtekintése például morfológiai vizsgálatnak számít. Ki sportként,
                    ki kikapcsolódásként, ki élvezetből nézegeti az égitesteket különbző távcsövekkel,
                    vagy éppen le is fotózza őket.
                </p>
                <button class="btn btn-outline" type="button" data-toggle="collapse" data-target="#restText" aria-expanded="false" aria-controls="restText">
                    Mutass többet...
                </button>
                <div class="collapse mt-4" id="restText">
                    <p>
                        A csillagászat régi és folyamatosan fennálló problémája a keletkező
                        adatok olyan tárolása, ami sokaknak – lehetőleg mindenkinek – hozzáférhető,
                        könnyen kereshető, felhasználható. Az amatőrcsillagászatban egyes észlelések
                        később tudományos célokra hasznosítva lesznek, pl. változócsillagászati
                        megfigyelések, meteormegfigyelések stb. Ezeket másképp kell tárolni,
                        mint a leíró jellegű észleléseket. Leíró jellegűek pl. a mélyég-objektumok,
                        üstökösök, bolygók, holdkráterek kinézetének, szín- és fényességviszonyainak,
                        látható jellegzetességeinek szöveges, rajzos vagy fotografikus megörökítése.
                        Sokszor vagyunk kíváncsiak arra, a másik mit lát a távcsövével, mennyi részletet,
                        milyen halvány objektumot, hogyan tud megörökíteni egy-egy jellegzetességet.
                        Érdekes számunkra, melyik távcsővel egy adott égitest hogyan néz ki,
                        és ugyanolyan műszerrel ki milyen eltérésekkel észlel.
                    </p>

                    <p>
                        Ugyancsak érdekes megőrzni az észleléseket az utókornak, mutatni amatőrcsillagászati
                        aktivitásunkat, és inspirálni egymást a jelenben további megfigyeléseket végezni,
                        az időt hasznosan eltölteni.
                    </p>

                    <p>
                        Magyarországon és külföldön is több adatbázis létezik, némelyik általános,
                        más csak bizonyos fajta észlelésekre koncentrál. (Ilyen pl. hazánkban az
                        MCSE észlelésfeltöltője vagy a MAFE észlelőrétje.) Ezek összekapcsolása
                        elméletben, technikailag lehetséges. Minket az inspirált a saját adatbázisunk
                        létrehozására, hogy a legkorszerűbb és a kor igényeinek megfelelő kinézettel
                        (design-nal) és kereshetőséggel, könnyű feltöltéssel bíró adatbázisunk legyen.
                    </p>

                    <p>
                        Minden kedves tagtársunknak és barátunknak figyelmébe ajánljuk észlelési
                        adatbázisunkat, ahová feltölthetik saját észleléseiket, legyen az szöveges,
                        rajzos vagy fotografikus megfigyelés. Az <a href="/">eszleles.hu</a> használatának
                        feltétele csak egy ingyenes regisztráció. Az adatbázisba mindenki
                        feltöltheti észlelését, és megnézheti a másét, összehasonlításokat tehet.
                    </p>

                    <p>
                        A <?= Html::a('VEGA', 'http://vcse.hu/vega-lista/') ?> észlelőrovatait is a jövőben erre az oldalra alapozva kívánjuk összeállítani.
                    </p>

                    <p>
                        A regisztráció után lehetőség van – de nem kötelező! - rövid
                        bemutatkozást és elérhetőséget (pl. E-mail címet, facebook oldalt stb)
                        megadni. Fel lehet iratkozni hírlevélre is.
                    </p>

                    <p>
                        Az <a href="/">eszleles.hu</a> nem üresen indul. Máris több mint 700 észlelést
                        tartalmaz – elsősorban mélyégészlelést -, amik a VEGA korábbi számaiban jelentek
                        meg, vagy amiket a VCSE-hez eljutattak a megelező években.
                        A legtöbb észlelés Varga Györgytől származik eddig.. Hasznos negatív
                        észleléseket is fel lehet tölteni, pl. ha jó égen egy nyílthalmaz nem látszik
                        egy adott műszerrel. Az adatbázis előnye, hogy míg a VEGA számait
                        ugyan élvezettel lehet lapozgatni kézbe véve a régi lapszámokat
                        vagy a neten olvasgatva, ott mégis számról számra elszórva találhatók
                        meg az észlelések itt-ott. Az adatbázisban viszont egyszerre látjuk őket,
                        így kevesebb munkával több észlelés van egyszerre a szemünk előtt.
                    </p>

                    <p>
                        Az adatbázis természetesen hasznos lehet célpontok válogatására
                        is, ha valaki szeretné tudni, mit lenne érdemes a saját
                        távcsövével megfigyelni. Például lehetséges a saját távcsövünkkel
                        egyező, vagy ahhoz hasonló átmérőjű, fényerejű távcsővel m
                        egfigyelt objektumokat eőkeresni, és aztán azokat magunk is megfigyelhetjük.
                    </p>

                    <p>
                        Reméljük, barátaink, tagtársiank, amatőrcsillaász ismerőseink
                        hasznosnak és használhatónak találják az adatbázist, és a jövőben
                        használni is fogják, mind észlelések feltöltése, mind korábbi
                        észlelések tanulmányozása szempontjából.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="container section home-columns">
        <section>
            <h2>Friss hozzászólások</h2>
            <?= ListView::widget([
                'dataProvider' => $commentData,
                'itemView' => '_comment',
                'emptyText' => 'Még nincs hozzászólás',
                'summary' => '',
            ]) ?>
        </section>

        <section class="news" id="hirek">
            <h2><a class="anchor-link" href="#hirek">Hírek</a></h2>
            <article id="egterkep">
                <h3><a class="anchor-link" href="#egterkep">Égtérkép</a></h3>
                <p class="news__date">2026-11-25</p>

                <p>
                    Az égboltra raktuk az észleléseket! <?= Html::a('Nézd meg te is', ['/sky-map/index']) ?>, hogy a
                    közösségünk mekkora részét fedte le megfigyeléseivel az égboltnak. Egy kis statisztika, mely idővel
                    csak bővülni, szépülni fog.
                </p>
                <p>
                    Természetesen, ha valakit a saját statisztikája érdekel, úgy a saját
                    <?= Html::a('profilján', Yii::$app->user->isGuest ? ['/site/login'] : ['/user/profile', 'id' => Yii::$app->user->getId()]) ?> megteheti. Jó kihívás minden csillagképben csak egy valamit megfigyelni...
                </p>
                <a class="news__image" href="<?= Url::toRoute('/sky-map/index') ?>">
                    <picture>
                        <source type="image/webp" srcset="/pictures/egterkep-640.webp 640w, /pictures/egterkep-1280.webp 1280w" sizes="(min-width: 992px) 50vw, 100vw">
                        <img src="/pictures/egterkep.jpg" alt="Az égtérkép a közösség észleléseivel" width="1280" height="815" loading="lazy">
                    </picture>
                </a>
            </article>
            <article id="megujulas">
                <h3><a class="anchor-link" href="#megujulas">Megújulás</a></h3>
                <p class="news__date">2026-11-24</p>

                <p>
                    Sok idő eltelt, mióta rendes törődést kapott ez az oldal. Stabilan növekszik az észlelők száma,
                    ezzel arányosan, mind mennyiségben, mind minőségben gyarapodik az észlelések gyűjteménye.
                </p>
                <div class="collapse" id="megujulas-tovabb">
                    <p>
                        Megérett az idő, hogy az elavult rendszerünk megújuljon! Új külső, új tartalom.
                        Lássuk, mi is változott!
                    </p>
                    <ul>
                        <li>Új design, ami egy picit közelebb került így a trendekhez,</li>
                        <li>Éjszakai mód. Most már sokkal szemkímélőbb lesz az éjszakai, távcső melletti használat,</li>
                        <li>
                            Értesítések: ha új hozzászólás érkezik egy kép alá (akár válaszként), értesítjük róla azt,
                            akit ilyenkor értesíteni kell,
                        </li>
                        <li>
                            „Korábbi eszközök” funkció feltöltésnél: Ha ugyanazt a felszerelést használtad, mint egy korábbi 
                            észlelésednél, már nem kell újra kitöltened az összes mezőt.
                        </li>
                        <li>
                            Képek módosítása: egy újabb, másféle képkidolgozás, vagy komplett csere. Az észlelés
                            módosításánál új képet lehet feltölteni, lecserélve az előzőt. A korábbi verziók elérhetőek,
                            így akár évekkel később visszatérve, kidolgozva ugyanazt a nyers képet meg tudjuk mutatni
                            a képeink evolúcióját,
                        </li>
                        <li>Pár technikai apróság, amiről jobb nem is beszélni.</li>
                    </ul>
                    <p>
                        További fejlesztések várhatóak, amint idő engedi. Noha igyekszünk lépést tartani a technológiával,
                        néhány, motorháztető alatti apróság a rendíthetetlen AI ellenére is marad, ugyanis az oldal
                        fejlesztőjének ez egy nosztalgiaprojektje is egyben.
                    </p>
                </div>
                <button class="btn btn-outline news__toggle collapsed" type="button" data-toggle="collapse" data-target="#megujulas-tovabb" aria-expanded="false" aria-controls="megujulas-tovabb">
                    <span class="news__more">Mutass többet...</span>
                    <span class="news__less">Mutass kevesebbet</span>
                </button>
            </article>
            <article id="vega-eszlelesek-feltoltve">
                <h3><a class="anchor-link" href="#vega-eszlelesek-feltoltve">VEGA észlelések feltöltve</a></h3>
                <p class="news__date">2021-05-20</p>

                <p>
                    A <?= Html::a('VEGA', 'http://vcse.hu/vega-lista/') ?> a Vega Csillagászati egyesület folyóirata, amelyben évtizedek óta publikálva vannak az
                    amatőrcsillagászati megfigyelések. Ezeket összegyűjtve, rendezett formában elérhetővé tettük
                    weboldalunkon.
                </p>
                <div class="collapse" id="vega-eszlelesek-feltoltve-tovabb">
                    <p>
                        A VEGA-ban közel 30 évre visszamenőleg találhatunk amatőrcsillagász megfigyeléseket.
                        Most végre lehetőség nyílik hosszú idő távlatából újraolvasni
                        egy-egy adott csillaghalmaz, üstökös, vagy épp nóva megfigyelést.
                        Érdekes lehet felfedezni, az idő folyamán milyen változások
                        látszanak - akár a természet változása, akár a távcsövek, eszközök
                        fejlődése miatt.
                    </p>
                </div>
                <button class="btn btn-outline news__toggle collapsed" type="button" data-toggle="collapse" data-target="#vega-eszlelesek-feltoltve-tovabb" aria-expanded="false" aria-controls="vega-eszlelesek-feltoltve-tovabb">
                    <span class="news__more">Mutass többet...</span>
                    <span class="news__less">Mutass kevesebbet</span>
                </button>
            </article>
            <article id="elindult-az-eszlelesfeltolto">
                <h3><a class="anchor-link" href="#elindult-az-eszlelesfeltolto">Elindult az észlelésfeltöltő!</a></h3>
                <p class="news__date">2021-05-19</p>

                <p>
                    A <?= Html::a('Vega Csillagászati Egyesület', 'http://vcse.hu') ?> elindította a saját észlelésfeltöltőjét,
                    nem csak a tagjainak. Bárki ingyenesen regisztrálhat, és feltöltheti
                    akár képes, akár szöveges észleléseit, asztrofotóit. Célunk, hogy
                    hosszútávra és szervezetten, kereshető formában megőrizzük a
                    magyar amatőrcsillagász társadalom hosszú évek
                    alatt gyűjtött megfigyeléseit.
                </p>
                <div class="collapse" id="elindult-az-eszlelesfeltolto-tovabb">
                    <p>
                        Folyamatosan fejlesztjük az oldalt.<br>
                        Az oldalon szabadidőnkben, bármilyen profit nélkül dolgozunk,
                        de a terveink listája hosszú. Ahogy időnk engedi, egyre több
                        funkcióval szeretnénk ellátni oldalunk (amatőr)csillagász társaink
                        örömére
                    </p>
                </div>
                <button class="btn btn-outline news__toggle collapsed" type="button" data-toggle="collapse" data-target="#elindult-az-eszlelesfeltolto-tovabb" aria-expanded="false" aria-controls="elindult-az-eszlelesfeltolto-tovabb">
                    <span class="news__more">Mutass többet...</span>
                    <span class="news__less">Mutass kevesebbet</span>
                </button>
            </article>
        </section>
    </div>

    <section class="aurora-band">
        <picture>
            <source type="image/webp" srcset="/pictures/uridafoss-800.webp 800w, /pictures/uridafoss-1600.webp 1600w" sizes="100vw">
            <img src="/pictures/uridafoss.jpg" alt="Sarki fény egy befagyott folyó fölött" loading="lazy">
        </picture>
        <div class="container">
            <h2>Te mit láttál az égen?</h2>
            <p>Rajzot, fotót és szöveges leírást is feltölthetsz.</p>
            <?php if (Yii::$app->user->isGuest) { ?>
                <a class="btn btn-primary btn-lg" href="<?= Url::toRoute('/site/signup') ?>">Regisztrálok</a>
            <?php } else { ?>
                <a class="btn btn-primary btn-lg" href="<?= Url::toRoute('/deepsky/create') ?>">Mélyég-észlelés feltöltése</a>
            <?php } ?>
        </div>
    </section>
</div>
