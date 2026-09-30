<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class AdmissionSearch extends Admission
{
    public $patientName;
    public $doctorName;
    public $wardName;
    public $dateFrom;
    public $dateTo;

    public function rules()
    {
        return [
            [
                ['id', 'patient_id', 'doctor_id', 'ward_id'],
                'integer',
            ],
            [
                [
                    'patientName',
                    'doctorName',
                    'wardName',
                    'admission_date',
                    'created_at',
                ],
                'safe',
            ],
            [
                ['status'],
                'in',
                'range' => ['admitted', 'discharged'],
            ],
            [
                ['dateFrom', 'dateTo'],
                'date',
                'format' => 'php:Y-m-d',
                'message' => 'تاریخ معتبر وارد کنید.',
            ],
            [
                ['dateTo'],
                'validateDateRange',
            ],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'patientName' => 'نام بیمار',
            'doctorName' => 'پزشک',
            'wardName' => 'بخش',
            'dateFrom' => 'پذیرش از تاریخ',
            'dateTo' => 'پذیرش تا تاریخ',
        ]);
    }

    public function validateDateRange($attribute, $params)
    {
        if (
            $this->hasErrors('dateFrom')
            || $this->hasErrors('dateTo')
            || $this->dateFrom === null
            || $this->dateFrom === ''
            || $this->dateTo === null
            || $this->dateTo === ''
        ) {
            return;
        }

        if ($this->dateTo < $this->dateFrom) {
            $this->addError(
                $attribute,
                'تاریخ پایان نباید قبل از تاریخ شروع باشد.'
            );
        }
    }

    public function search($params)
    {
        $query = Admission::find()->joinWith([
            'patient',
            'doctor',
            'wardModel',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id' => [
                        'asc' => ['admissions.id' => SORT_ASC],
                        'desc' => ['admissions.id' => SORT_DESC],
                    ],
                    'patientName' => [
                        'asc' => [
                            'patients.last_name' => SORT_ASC,
                            'patients.first_name' => SORT_ASC,
                        ],
                        'desc' => [
                            'patients.last_name' => SORT_DESC,
                            'patients.first_name' => SORT_DESC,
                        ],
                    ],
                    'doctorName' => [
                        'asc' => ['doctors.name' => SORT_ASC],
                        'desc' => ['doctors.name' => SORT_DESC],
                    ],
                    'wardName' => [
                        'asc' => ['wards.name' => SORT_ASC],
                        'desc' => ['wards.name' => SORT_DESC],
                    ],
                    'admission_date' => [
                        'asc' => ['admissions.admission_date' => SORT_ASC],
                        'desc' => ['admissions.admission_date' => SORT_DESC],
                    ],
                    'created_at' => [
                        'asc' => ['admissions.created_at' => SORT_ASC],
                        'desc' => ['admissions.created_at' => SORT_DESC],
                    ],
                    'status' => [
                        'asc' => ['admissions.status' => SORT_ASC],
                        'desc' => ['admissions.status' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'admissions.id' => $this->id,
            'admissions.patient_id' => $this->patient_id,
            'admissions.doctor_id' => $this->doctor_id,
            'admissions.ward_id' => $this->ward_id,
            'admissions.status' => $this->status,
        ]);

        // جست‌وجوی نام، نام خانوادگی یا ترکیب آن‌ها.
        $nameParts = preg_split(
            '/\s+/u',
            trim((string) $this->patientName),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        foreach ($nameParts ?: [] as $part) {
            $query->andWhere([
                'or',
                ['like', 'patients.first_name', $part],
                ['like', 'patients.last_name', $part],
            ]);
        }

        $query
            ->andFilterWhere([
                'like',
                'doctors.name',
                $this->doctorName,
            ])
            ->andFilterWhere([
                'like',
                'wards.name',
                $this->wardName,
            ])
            ->andFilterWhere([
                'like',
                'admissions.admission_date',
                $this->admission_date,
            ])
            ->andFilterWhere([
                'like',
                'admissions.created_at',
                $this->created_at,
            ]);

        if ($this->dateFrom !== null && $this->dateFrom !== '') {
            $query->andWhere([
                '>=',
                'admissions.admission_date',
                $this->dateFrom,
            ]);
        }

        if ($this->dateTo !== null && $this->dateTo !== '') {
            // شامل تمام ساعات روز پایان بازه.
            $nextDay = (new \DateTimeImmutable($this->dateTo))
                ->modify('+1 day')
                ->format('Y-m-d');

            $query->andWhere([
                '<',
                'admissions.admission_date',
                $nextDay,
            ]);
        }

        return $dataProvider;
    }
}