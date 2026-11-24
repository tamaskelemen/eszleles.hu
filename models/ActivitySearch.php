<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ActivitySearch represents the model behind the search form of `app\models\Activity`.
 */
class ActivitySearch extends Activity
{
    const PAGE_SIZE = 50;

    /**
     * @var string part of the name or the email address of the user
     */
    public $userName;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userName', 'type', 'ip', 'user_agent'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'userName' => 'Felhasználó',
        ]);
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
     * @param int|null $userId if set, only the activities of this user are listed
     *
     * @return ActiveDataProvider
     */
    public function search($params, $userId = null)
    {
        $query = Activity::find()->joinWith('user');

        if ($userId !== null) {
            $query->ofUser($userId);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => self::PAGE_SIZE,
            ],
            'sort' => [
                'attributes' => [
                    // the ids follow the order of creation
                    'created_at' => [
                        'asc' => ['activities.id' => SORT_ASC],
                        'desc' => ['activities.id' => SORT_DESC],
                    ],
                ],
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['activities.type' => $this->type])
            ->andFilterWhere(['ilike', 'activities.ip', $this->ip])
            ->andFilterWhere(['ilike', 'activities.user_agent', $this->user_agent])
            ->andFilterWhere([
                'or',
                ['ilike', 'users.name', $this->userName],
                ['ilike', 'users.email', $this->userName],
            ]);

        return $dataProvider;
    }
}
