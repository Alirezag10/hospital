<?php
namespace app\controllers;

use app\components\JalaliDate;
use app\components\FormInput;
use app\models\Admission;
use app\models\AdmissionSearch;
use app\models\Discharge;
use app\models\Doctor;
use app\models\Patient;
use Yii;
use yii\db\Expression;
use app\components\RoleController as Controller;
use app\components\Access;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdmissionController extends Controller
{
    public function actionIndex($date = null, $discharged_on = null)
    {
        foreach (['date' => $date, 'discharged_on' => $discharged_on] as $parameter => $value) {
            if ($value === null || ($parameter === 'date' && $value === 'unknown')) {
                continue;
            }
            $parsed = is_string($value) ? \DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
            if ($parsed === false || $parsed->format('Y-m-d') !== $value) {
                throw new NotFoundHttpException('تاریخ انتخاب‌شده معتبر نیست.');
            }
        }
        $searchModel = new AdmissionSearch(['status' => $discharged_on !== null ? 'discharged' : null]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams, $date, $discharged_on),
            'date' => $date,
            'dischargedOn' => $discharged_on,
        ]);
    }

    public function actionCalendar($month = null)
    {
        $today = date('Y-m-d');
        [$currentYear, $currentMonth] = JalaliDate::fromGregorian((int) date('Y'), (int) date('n'), (int) date('j'));
        $thisMonth = sprintf('%04d-%02d', $currentYear, $currentMonth);
        if (is_string($month) && preg_match('/^(1[2-5]\d{2}|1600)-(0[1-9]|1[0-2])$/D', $month, $matches)) {
            $currentYear = (int) $matches[1];
            $currentMonth = (int) $matches[2];
        }
        $firstDay = JalaliDate::toGregorian($currentYear, $currentMonth, 1);
        $nextYear = $currentMonth === 12 ? $currentYear + 1 : $currentYear;
        $nextMonthNumber = $currentMonth === 12 ? 1 : $currentMonth + 1;
        $nextMonth = JalaliDate::toGregorian($nextYear, $nextMonthNumber, 1);
        $counts = Admission::find()
            ->select(['day' => new Expression('DATE(admission_date)'), 'amount' => new Expression('COUNT(*)')])
            ->where(['>=', 'admission_date', $firstDay->format('Y-m-d 00:00:00')])
            ->andWhere(['<', 'admission_date', $nextMonth->format('Y-m-d 00:00:00')])
            ->groupBy(new Expression('DATE(admission_date)'))
            ->asArray()->all();

        $days = [];
        $dayCounts = [];
        $previewIds = [];
        foreach ($counts as $row) {
            $dayCounts[$row['day']] = (int) $row['amount'];
            $previewIds[$row['day']] = Admission::find()->select('id')
                ->where(['>=', 'admission_date', $row['day'] . ' 00:00:00'])
                ->andWhere(['<', 'admission_date', (new \DateTimeImmutable($row['day']))->modify('+1 day')->format('Y-m-d 00:00:00')])
                ->orderBy(['admission_date' => SORT_ASC, 'id' => SORT_ASC])
                ->limit(3)->column();
        }
        $ids = array_merge(...array_values($previewIds ?: [[]]));
        $previews = $ids === [] ? [] : Admission::find()->with('patient')->where(['id' => $ids])->indexBy('id')->all();
        foreach ($previewIds as $day => $dayIds) {
            $days[$day] = array_map(static fn($id) => $previews[$id], $dayIds);
        }

        return $this->render('calendar', [
            'month' => sprintf('%04d-%02d', $currentYear, $currentMonth),
            'today' => $today,
            'currentMonth' => $thisMonth,
            'firstDay' => $firstDay,
            'daysInMonth' => (int) $firstDay->diff($nextMonth)->days,
            'startOffset' => ((int) $firstDay->format('N') + 1) % 7,
            'admissionsByDay' => $days,
            'dayCounts' => $dayCounts,
            'unknownCount' => (int) Admission::find()->where(['admission_date' => null])->count(),
            'previousMonth' => sprintf('%04d-%02d', $currentMonth === 1 ? $currentYear - 1 : $currentYear, $currentMonth === 1 ? 12 : $currentMonth - 1),
            'nextMonth' => sprintf('%04d-%02d', $nextYear, $nextMonthNumber),
        ]);
    }

    public function actionCreate($patient_id = null)
    {
        $model = new Admission();
        $patientId = null;
        if ($patient_id !== null) {
            $patientId = filter_var($patient_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($patientId === false || Patient::findOne(['id' => $patientId]) === null) {
                throw new NotFoundHttpException('بیمار انتخاب‌شده پیدا نشد.');
            }
            $model->patient_id = $patientId;
        }
        if ($this->request->isPost && FormInput::load($model, $this->request->post())) {
            if ($patientId !== null) {
                $model->patient_id = $patientId;
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'پذیرش بیمار با موفقیت ثبت شد.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('create', [
            'model' => $model,
            'patientLocked' => $patientId !== null,
        ]);
    }

    public function actionSearchPatients()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $term = $this->request->get('q', '');
        if (!is_string($term)) {
            return [];
        }

        $term = mb_substr(Patient::normalizeDigits(trim($term)), 0, 100);
        if (mb_strlen($term) < 2) {
            return [];
        }

        $parts = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false || $parts === []) {
            return [];
        }

        $query = Patient::find()
            ->select(['id', 'first_name', 'last_name', 'national_code'])
            ->orderBy(['last_name' => SORT_ASC, 'first_name' => SORT_ASC]);

        foreach ($parts as $part) {
            $query->andWhere(ctype_digit($part)
                ? (strlen($part) === 10 ? ['national_code' => $part] : ['like', 'national_code', $part . '%', false])
                : ['or', ['like', 'first_name', $part], ['like', 'last_name', $part]]);
        }

        $patients = $query->limit(10)->asArray()->all();

        return array_map(static function (array $patient): array {
            return [
                'id' => (int) $patient['id'],
                'label' => $patient['first_name'] . ' ' . $patient['last_name'] . ' (کد ملی: ' . $patient['national_code'] . ')',
            ];
        }, $patients);
    }

    public function actionSearchOpenAdmissions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $term = $this->searchTerm();
        if ($term === null) {
            return [];
        }

        $parts = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false || $parts === []) {
            return [];
        }

        $query = Access::admissions('a')
            ->select(['a.id', 'a.admission_date', 'a.doctor_name', 'a.ward', 'p.first_name', 'p.last_name', 'p.national_code'])
            ->innerJoin(['p' => Patient::tableName()], 'p.id = a.patient_id')
            ->leftJoin(['d' => Discharge::tableName()], 'd.admission_id = a.id')
            ->andWhere(['a.status' => 'admitted', 'd.id' => null])
            ->orderBy(['p.last_name' => SORT_ASC, 'p.first_name' => SORT_ASC]);

        foreach ($parts as $part) {
            $query->andWhere(ctype_digit($part)
                ? (strlen($part) === 10 ? ['p.national_code' => $part] : ['like', 'p.national_code', $part . '%', false])
                : ['or', ['like', 'p.first_name', $part], ['like', 'p.last_name', $part]]);
        }

        return array_map(static fn(array $admission): array => [
            'id' => (int) $admission['id'],
            'label' => $admission['first_name'] . ' ' . $admission['last_name']
                . ' (کد ملی: ' . $admission['national_code'] . ')',
            'patientName' => $admission['first_name'] . ' ' . $admission['last_name'],
            'nationalCode' => $admission['national_code'],
            'doctor' => $admission['doctor_name'],
            'ward' => $admission['ward'],
            'detail' => 'پرونده ' . $admission['id'] . ' · ' . Yii::$app->formatter->asDatetime($admission['admission_date'])
                . ' · ' . $admission['ward'] . ' · ' . $admission['doctor_name'],
        ], $query->limit(10)->asArray()->all());
    }

    public function actionSearchDoctors()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $term = $this->searchTerm();
        if ($term === null) {
            return [];
        }

        $term = mb_strtolower($term, 'UTF-8');
        $doctors = Doctor::find()
            ->select(['id', 'name', 'specialty'])
            ->where(['or',
                ['like', new Expression('LOWER([[name]])'), $term],
                ['like', new Expression('LOWER([[specialty]])'), $term],
            ])
            ->orderBy(['name' => SORT_ASC])
            ->limit(10)
            ->asArray()
            ->all();

        return array_map(static fn(array $doctor): array => [
            'id' => (int) $doctor['id'],
            'label' => $doctor['name'] . ' (' . $doctor['specialty'] . ')',
        ], $doctors);
    }

    private function searchTerm(): ?string
    {
        $term = $this->request->get('q', '');
        if (!is_string($term)) {
            return null;
        }

        $term = mb_substr(Patient::normalizeDigits(trim($term)), 0, 100);
        return mb_strlen($term) >= 2 ? $term : null;
    }

    public function actionView($id)
    {
        $model = Access::admission($id);
        return $this->render('view', ['model' => $model]);
    }
}
