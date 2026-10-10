<?php
namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/** Filters the admission list without changing any records. */
class AdmissionSearch extends Model
{
    public $name;
    public $national_code;
    public $mobile;
    public $status;

    public function rules()
    {
        return [
            [['name', 'status'], 'trim', 'skipOnArray' => true],
            [['national_code'], 'filter', 'filter' => [Patient::class, 'normalizeDigits'], 'skipOnArray' => true],
            [['mobile'], 'filter', 'filter' => [Patient::class, 'normalizeMobile'], 'skipOnArray' => true],
            [['name'], 'string', 'max' => 200],
            [['national_code'], 'match', 'pattern' => '/^[0-9]{1,10}$/D', 'message' => 'کد ملی را با حداکثر ۱۰ رقم وارد کنید.'],
            [['mobile'], 'match', 'pattern' => '/^[0-9]{0,11}$/D', 'message' => 'شماره موبایل معتبر وارد کنید.'],
            [['status'], 'in', 'range' => ['admitted', 'discharged']],
        ];
    }

    public function attributeLabels()
    {
        return ['name' => 'نام بیمار', 'national_code' => 'کد ملی', 'mobile' => 'موبایل', 'status' => 'وضعیت پذیرش'];
    }

    public function search($params)
    {
        $query = Admission::find()->with('patient');
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

        $query->andFilterWhere(['status' => $this->status]);
        if ((string) $this->name !== '' || (string) $this->national_code !== '' || (string) $this->mobile !== '') {
            $patients = Patient::find()->select('id');
            foreach (preg_split('/\s+/u', (string) $this->name, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                $patients->andWhere(['or', ['like', 'first_name', $part], ['like', 'last_name', $part]]);
            }
            $patients->andFilterWhere(['like', 'national_code', $this->national_code]);
            $patients->andFilterWhere(['like', 'mobile', $this->mobile]);
            $query->andWhere(['in', 'patient_id', $patients]);
        }
        return $provider;
    }
}
