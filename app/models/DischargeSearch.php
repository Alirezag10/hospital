<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Discharge;

/**
 * DischargeSearch represents the model behind the search form of `app\models\Discharge`.
 */
class DischargeSearch extends Discharge
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'admission_id', 'total_amount'], 'integer'],
            [['discharge_date', 'description', 'created_at'], 'safe'],
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Discharge::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'admission_id' => $this->admission_id,
            'total_amount' => $this->total_amount,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        \app\helpers\DateFilter::apply($query, 'discharge_date', $this->discharge_date);
        \app\helpers\DateFilter::apply($query, 'created_at', $this->created_at);

        return $dataProvider;
    }
}
