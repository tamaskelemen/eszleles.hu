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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'seeing', 'transparency', 'observer_id'], 'integer'],
            [['date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['uploaded_at', 'edited_at'], 'date'],
            [['object_name', 'catalog_number', 'constellation', 'object_type', 'telescope', 'camera', 'location', 'source', 'description'], 'safe'],
            [['observer', 'type'], 'safe'],
        ];
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
        $query = Observe::find()->with('image');

        // add conditions that should always apply here
        $query->joinWith(['observer']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
        ]);

        $query->andFilterWhere(['ilike', 'object_name', $this->object_name])
            ->andFilterWhere(['ilike', 'catalog_number', $this->catalog_number])
            ->andFilterWhere(['ilike', 'constellation', $this->constellation])
            ->andFilterWhere(['ilike', 'object_type', $this->object_type])
            ->andFilterWhere(['ilike', 'type', $this->type])
            ->andFilterWhere(['ilike', 'telescope', $this->telescope])
            ->andFilterWhere(['ilike', 'camera', $this->camera])
            ->andFilterWhere(['ilike', 'location', $this->location])
            ->andFilterWhere(['ilike', 'source', $this->source])
            ->andFilterWhere(['ilike', 'description', $this->description])
            ->andFilterWhere(['like', 'users.name', $this->observer]);


        return $dataProvider;
    }
}
