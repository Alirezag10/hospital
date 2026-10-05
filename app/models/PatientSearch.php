<?php
namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class PatientSearch extends Model
{
    public $name;
    public $national_code;

    public function rules()
    {
        return [[['name', 'national_code'], 'string']];
    }

    public function attributeLabels()
    {
        return ['name' => 'نام بیمار', 'national_code' => 'کد ملی'];
    }

    public function search($params)
    {
        $query = Patient::find();
        $provider = new ActiveDataProvider(['query' => $query, 'sort' => ['defaultOrder' => ['id' => SORT_DESC]]]);
        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $provider;
        }
        foreach (preg_split('/\s+/u', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
            $query->andWhere(['or', ['like', 'first_name', $part], ['like', 'last_name', $part]]);
        }
        $query->andFilterWhere(['like', 'national_code', $this->national_code]);
        return $provider;
    }
}
