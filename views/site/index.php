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

$this->registerMetaTag(["property" => "og:image", "content" => "/pictures/eszleloret.jpg" ])
?>
<div class="site-index">

    <div id="carouselEx" class="carousel slide" data-ride="carousel" data-interval="10000">
        <ol class="carousel-indicators">
            <li data-target="#carouselEx" data-slide-to="0" class="active"></li>
            <li data-target="#carouselEx" data-slide-to="1"></li>
            <li data-target="#carouselEx" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner"  >
            <div class="carousel-title">
                <div class="title-wrapper">
                    <h1>Észlelés.hu</h1>
                    <p class="lead">Csillagászati megfigyelések gyűjtőhelye.</p>
                    <?php if (Yii::$app->user->isGuest) { ?>
                        <p><a class="btn btn-lg btn-success" href="<?= Url::toRoute('/site/signup') ?>">Regisztrálok</a></p>
                        <p>vagy ha már regisztráltál, <a class="" href="<?= Url::toRoute('/site/login') ?>">lépj be</a>.</p>
                    <?php } ?>
                </div>
            </div>
            <div class="carousel-item active">
                <img class="d-block w-100" style="object-position: center bottom; object-fit: cover;" src="/pictures/eszleloret.jpg" alt="First slide" ">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="/pictures/hold_schmallrafi.jpg" alt="VCSE Camp slide">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="/pictures/uridafoss.jpg" alt="Northern Lights">
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselEx" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselEx" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>


    <div class="body-content container">
        <h2>Egy korszerű észlelésfeltöltő</h2>

        <div class="row mb-5">
            <div class="col-12">
                <p>
                    Az amatőrcsillagászat fizetés és tiszteletdíj nélkül végzett csillagászat.
                    Egy-egy mélyég-objektum vagy üstökös megtekintése, látványának, alakjának,
                    részleteinek megtekintése például morfológiai vizsgálatnak számít. Ki sportként,
                    ki kikapcsolódásként, ki élvezetből nézegeti az égitesteket különbző távcsövekkel,
                    vagy éppen le is fotózza őket.
                </p>
                <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#restText" aria-expanded="false" aria-controls="restText">
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

        <h2 class="pt-4">Legújabb feltöltések</h2>

        <div class="row">
            <?php
            foreach ($latestObs as $obs) {
                echo $this->render('../_common-items/_latestItem', ['model' => $obs]);
            }
            ?>
        </div>

        <hr>
            <h2 class="pt-4">Friss hozzászólások</h2>

            <div>
                <?php
                    echo ListView::widget([
                        'dataProvider' => $commentData,
                        'itemView' => '_comment',
                        'emptyText' => '<i>Még nincs hozzászólás</i>',
                        'summary' => '',
                    ])
                ?>
            </div>
        <hr>

        <h2 class="pb-4">Hírek</h2>
        <div class="row mb-5">
            <div class="col-12">
                <h5>VEGA észlelések feltöltve</h5>
                <p><i>2021-05-20</i></p>

                <p>
                    A <?= Html::a('VEGA', 'http://vcse.hu/vega-lista/') ?> a Vega Csillagászati egyesület folyóirata, amelyben évtizedek óta publikálva vannak az
                    amatőrcsillagászati megfigyelések. Ezeket összegyűjtve, rendezett formában elérhetővé tettük
                    weboldalunkon.
                </p>
                <p>
                    A VEGA-ban közel 30 évre visszamenőleg találhatunk amatőrcsillagász megfigyeléseket.
                    Most végre lehetőség nyílik hosszú idő távlatából újraolvasni
                    egy-egy adott csillaghalmaz, üstökös, vagy épp nóva megfigyelést.
                    Érdekes lehet felfedezni, az idő folyamán milyen változások
                    látszanak - akár a természet változása, akár a távcsövek, eszközök
                    fejlődése miatt.
                </p>
            </div>
            <hr>
            <div class="col-12">
                <h5>Elindult az észlelésfeltöltő!</h5>
                <p><i>2021-05-19</i></p>

                <p>
                    A <?= Html::a('Vega Csillagászati Egyesület', 'http://vcse.hu') ?> elindította a saját észlelésfeltöltőjét,
                    nem csak a tagjainak. Bárki ingyenesen regisztrálhat, és feltöltheti
                    akár képes, akár szöveges észleléseit, asztrofotóit. Célunk, hogy
                    hosszútávra és szervezetten, kereshető formában megőrizzük a
                    magyar amatőrcsillagász társadalom hosszú évek
                    alatt gyűjtött megfigyeléseit.
                </p>
                <p>
                    Folyamatosan fejlesztjük az oldalt.<br>
                    Az oldalon szabadidőnkben, bármilyen profit nélkül dolgozunk,
                    de a terveink listája hosszú. Ahogy időnk engedi, egyre több
                    funkcióval szeretnénk ellátni oldalunk (amatőr)csillagász társaink
                    örömére
                </p>
            </div>
        </div>

    </div>
</div>
