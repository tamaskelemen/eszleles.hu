<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * IdentificationSearch represents the model behind the admin list of identified and not identified observations.
 */
class IdentificationSearch extends Model
{
    const PAGE_SIZE = 50;

    const FILTER_REVIEW = 'review';
    const FILTER_NONE = 'none';
    const FILTER_IDENTIFIED = 'identified';
    const FILTER_DISMISSED = 'dismissed';
    const FILTER_ALL = 'all';

    public $filter = self::FILTER_REVIEW;
    public $type;
    public $object_name;

    /** @var string part of the name of the observer */
    public $observerName;

    /** @var string part of the name of an identified object */
    public $skyObject;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['filter'], 'in', 'range' => array_keys(self::getAllFilters())],
            [['type'], 'in', 'range' => array_keys(Observe::getAllTypes())],
            [['object_name', 'observerName', 'skyObject'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Típus',
            'object_name' => 'Észlelés',
            'observerName' => 'Észlelő',
            'skyObject' => 'Azonosított objektum',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function formName()
    {
        return '';
    }

    /**
     * @return array
     */
    public static function getAllFilters()
    {
        return [
            self::FILTER_REVIEW => 'Jóváhagyásra vár',
            self::FILTER_NONE => 'Azonosítatlan',
            self::FILTER_IDENTIFIED => 'Azonosított',
            self::FILTER_DISMISSED => 'Nincs objektuma',
            self::FILTER_ALL => 'Összes',
        ];
    }

    /**
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Observe::find()->with('thumbnail', 'skyObjectLinks')->joinWith('observer');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => self::PAGE_SIZE,
            ],
            'sort' => [
                'attributes' => [
                    'object_name' => [
                        'asc' => ['observations.object_name' => SORT_ASC],
                        'desc' => ['observations.object_name' => SORT_DESC],
                    ],
                    'id' => [
                        'asc' => ['observations.id' => SORT_ASC],
                        'desc' => ['observations.id' => SORT_DESC],
                    ],
                ],
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }

        $this->applyFilter($query, $this->filter);

        $query->andFilterWhere(['observations.type' => $this->type])
            ->andFilterWhere(['ilike', 'observations.object_name', $this->object_name])
            ->andFilterWhere(['ilike', 'users.name', $this->observerName]);

        if (trim((string)$this->skyObject) !== '') {
            $query->andWhere(['exists', $this->linkQuery()
                ->innerJoin('sky_objects', 'sky_objects.id = l.sky_object_id')
                ->andWhere(['!=', 'l.status', ObservationSkyObject::STATUS_REJECTED])
                ->andWhere([
                    'or',
                    ['ilike', 'sky_objects.name', $this->skyObject],
                    ['ilike', 'sky_objects.common_name', $this->skyObject],
                    ['ilike', 'l.designation', $this->skyObject],
                    ['ilike', new \yii\db\Expression("CONCAT('M ', sky_objects.messier)"), $this->skyObject],
                ])
            ]);
        }

        return $dataProvider;
    }

    /**
     * @return array number of observations indexed by filter
     */
    public static function getCounts()
    {
        $counts = [];

        foreach (array_keys(self::getAllFilters()) as $filter) {
            $query = Observe::find();
            (new self())->applyFilter($query, $filter);
            $counts[$filter] = (int)$query->count();
        }

        return $counts;
    }

    private function applyFilter(ObservesQuery $query, $filter)
    {
        $visible = $this->linkQuery()->andWhere(['l.status' => ObservationSkyObject::VISIBLE_STATUSES]);
        $review = $this->linkQuery()->andWhere(['l.status' => ObservationSkyObject::STATUS_REVIEW]);

        switch ($filter) {
            case self::FILTER_REVIEW:
                $query->andWhere(['exists', $review]);
                break;
            case self::FILTER_NONE:
                // the other types are not expected to have an object
                $query->andWhere(['observations.type' => Observe::TYPE_DEEP_SKY, 'observations.has_no_sky_object' => false])
                    ->andWhere(['not exists', $visible])
                    ->andWhere(['not exists', $review]);
                break;
            case self::FILTER_IDENTIFIED:
                $query->andWhere(['exists', $visible]);
                break;
            case self::FILTER_DISMISSED:
                $query->andWhere(['observations.has_no_sky_object' => true]);
                break;
        }
    }

    private function linkQuery()
    {
        return (new Query())->select('l.id')->from(['l' => 'observation_sky_objects'])->where('l.observation_id = observations.id');
    }
}
