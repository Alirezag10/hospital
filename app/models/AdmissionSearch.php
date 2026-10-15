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

    public function search($params, ?string $date = null, ?string $dischargedOn = null)
    {
        $query = Admission::find()->with('patient');
        if ($date === 'unknown') {
            $query->andWhere(['admission_date' => null]);
        } elseif ($date !== null) {
            $query->andWhere(['>=', 'admission_date', $date . ' 00:00:00'])
                ->andWhere(['<', 'admission_date', (new \DateTimeImmutable($date))->modify('+1 day')->format('Y-m-d 00:00:00')]);
        }
        if ($dischargedOn !== null) {
            $query->with('discharge');
            $discharges = Discharge::find()->select('admission_id')
                ->where(['>=', 'discharge_date', $dischargedOn . ' 00:00:00'])
                ->andWhere(['<', 'discharge_date', (new \DateTimeImmutable($dischargedOn))->modify('+1 day')->format('Y-m-d 00:00:00')]);
            $query->andWhere(['status' => 'discharged'])->andWhere(['in', 'id', $discharges]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => ['defaultPageSize' => 10, 'pageSizeLimit' => [10, 50]],
        ]);
        $this->load($params);
        if (!$this->validate()) {
            // Keep validation errors, but do not pass arrays/objects to HTML inputs.
            foreach ($this->attributes as $attribute => $value) {
                if ($value !== null && !is_scalar($value)) {
                    $this->$attribute = null;
                }
            }
            $query->where('0=1');
            return $provider;
        }

        $query->andFilterWhere(['status' => $this->status]);
        if ((string) $this->name !== '' || (string) $this->national_code !== '' || (string) $this->mobile !== '') {
            $patients = Patient::find()->select('id');
            foreach (preg_split('/\s+/u', (string) $this->name, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                $patients->andWhere(['or', ['like', 'first_name', $part], ['like', 'last_name', $part]]);
            }
            if ((string) $this->national_code !== '') {
                $patients->andWhere(strlen((string) $this->national_code) === 10
                    ? ['national_code' => $this->national_code]
                    : ['like', 'national_code', $this->national_code . '%', false]);
            }
            $patients->andFilterWhere(['like', 'mobile', $this->mobile]);
            $query->andWhere(['in', 'patient_id', $patients]);
        }
        return $provider;
    }
}
