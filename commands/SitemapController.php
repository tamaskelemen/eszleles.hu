<?php
namespace app\commands;

use app\components\Helper;
use app\models\Observe;
use \XMLWriter;
use yii\console\Controller;
class SitemapController extends Controller
{
    public $file = __DIR__ . '/../web/sitemap.xml';
    public function actionIndex()
    {

        $this->createFile();
        $xml = new XMLWriter();
        $xml->openURI($this->file);

        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->endAttribute();

        foreach (Observe::getAllTypes() as $type) {
            $xml->startElement('url');

            $xml->startElement('loc');
            $category = lcfirst(Helper::replaceUnaccent($type));
            $url = \Yii::$app->params['homeUrl'] . "/" . $category . "/";
            $xml->text($url);
            $xml->endElement(); //loc

            $xml->startElement('changefreq');
            $xml->text('daily');
            $xml->endElement();

            $xml->startElement('priority');
            $xml->text('1.0');
            $xml->endElement();

            $xml->endElement(); // url
        }

        foreach ($this->getObservations() as $observation) {
            $xml->startElement('url');

            $xml->startElement('loc');
            $category = lcfirst(Helper::replaceUnaccent(Observe::getTypeName($observation->type)));
            $url = \Yii::$app->params['homeUrl'] . "/" . $category. "/" . $observation->id;
            $xml->text($url);
            $xml->endElement();

            $xml->endElement();
        }

        $xml->endElement(); //urlset

        $xml->flush();
    }

    private function createFile()
    {
        if (!is_file($this->file)) {
            file_put_contents($this->file,'');
        }
    }

    public function getObservations() {
        $observations = Observe::find()->all();
        return $observations;
    }
}