<?php
namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class PatientSearch extends Model
{
    public $name;
    public $national_code;
    public $mobile;

    public function rules()
    {
        return [
            [['name'], 'trim', 'skipOnArray' => true],
            [['national_code'], 'filter', 'filter' => [Patient::class, 'normalizeDigits'], 'skipOnArray' => true],
            [['mobile'], 'filter', 'filter' => [Patient::class, 'normalizeMobile'], 'skipOnArray' => true],
            [['name'], 'string', 'max' => 200],
            [['national_code'], 'match', 'pattern' => '/^[0-9]{1,10}$/D', 'message' => 'کد ملی را با حداکثر ۱۰ رقم وارد کنید.'],
            [['mobile'], 'match', 'pattern' => '/^[0-9]{0,11}$/D', 'message' => 'شماره موبایل معتبر وارد کنید.'],
        ];
    }

    public function attributeLabels()
    {
        return ['name' => 'نام بیمار', 'national_code' => 'کد ملی', 'mobile' => 'موبایل'];
    }

    public function search($params)
    {
        $query = Patient::find();
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['defaultPageSize' => 10, 'pageSizeLimit' => [10, 50]],
        ]);
        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $provider;
        }
        foreach (preg_split('/\s+/u', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
            $query->andWhere(['or', ['like', 'first_name', $part], ['like', 'last_name', $part]]);
        }
        $query->andFilterWhere(['like', 'national_code', $this->national_code]);
        $query->andFilterWhere(['like', 'mobile', $this->mobile]);
        return $provider;
    }
}
