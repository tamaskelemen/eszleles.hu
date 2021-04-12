<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ObserveSearch represents the model behind the search form of `frontend\models\Observe`.
 */
class ObserveSearch extends Observe
{
    public $observer;
    public $fromDate;
    public $toDate;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'seeing', 'transparency', 'observer_id'], 'integer'],
            [['observer', 'object_name', 'telescope', 'camera', 'description', 'location', 'mechanics', 'type'], 'string'],
            [['date', 'fromDate', 'toDate'], 'date', 'format' => 'yyyy-MM-dd'],
            [['uploaded_at', 'edited_at'], 'date'],
            [[ 'meteor_membership', 'brightness', 'color'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'fromDate' => 'Mikortól',
                'toDate' => 'Meddig',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Observe::find()->with('image', 'thumbnail', 'comments', 'observer');

        // add conditions that should always apply here
        $query->joinWith(['observer']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => [
                    'uploaded_at' => SORT_DESC,
                ]
            ],
        ]);

        $dataProvider->sort->attributes['observer'] = [
            // The tables are the ones our relation are configured to
            // in my case they are prefixed with "tbl_"
            'asc' => ['users.name' => SORT_ASC],
            'desc' => ['users.name' => SORT_DESC],
        ];
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'observes.id' => $this->id,
            'seeing' => $this->seeing,
            'transparency' => $this->transparency,
            'date' => $this->date,
            'observer_id' => $this->observer_id,
            'uploaded_at' => $this->uploaded_at,
            'edited_at' => $this->edited_at,
            'moon_phase' => $this->moon_phase,
            'meteor_membership' => $this->meteor_membership,
            'brightness' => $this->brightness,
            'color' => $this->color,
        ]);

        $query->andFilterWhere(['ilike', 'object_name', $this->object_name])
            ->andFilterWhere(['ilike', 'catalog_number', $this->catalog_number])
            ->andFilterWhere(['ilike', 'constellation', $this->constellation])
            ->andFilterWhere(['ilike', 'object_type', $this->object_type])
            ->andFilterWhere(['ilike', 'type', $this->type])
            ->andFilterWhere(['ilike', 'telescope', $this->telescope])
            ->andFilterWhere(['ilike', 'camera', $this->camera])
            ->andFilterWhere(['ilike', 'mechanics', $this->mechanics])
            ->andFilterWhere(['ilike', 'location', $this->location])
            ->andFilterWhere(['ilike', 'source', $this->source])
            ->andFilterWhere(['ilike', 'description', $this->description])
            ->andFilterWhere(['like', 'users.name', $this->observer])
            ->andFilterWhere(['>=', 'date', $this->fromDate])
            ->andFilterWhere(['<=', 'date', $this->toDate]);

        return $dataProvider;
    }
}
