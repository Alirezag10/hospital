<?php
namespace app\controllers;

use app\models\Admission;
use app\models\AdmissionSearch;
use app\models\Discharge;
use app\models\Doctor;
use app\models\Patient;
use Yii;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AdmissionController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new AdmissionSearch();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams),
        ]);
    }

    public function actionCalendar($month = null)
    {
        $month = is_string($month) && preg_match('/^\d{4}-\d{2}$/D', $month) ? $month : date('Y-m');
        $firstDay = \DateTimeImmutable::createFromFormat('!Y-m-d', $month . '-01');
        if ($firstDay === false || $firstDay->format('Y-m') !== $month) {
            $firstDay = new \DateTimeImmutable(date('Y-m-01'));
            $month = $firstDay->format('Y-m');
        }
        $nextMonth = $firstDay->modify('first day of next month');
        $admissions = Admission::find()->with('patient')
            ->where(['>=', 'admission_date', $firstDay->format('Y-m-d 00:00:00')])
            ->andWhere(['<', 'admission_date', $nextMonth->format('Y-m-d 00:00:00')])
            ->orderBy(['admission_date' => SORT_ASC])->all();

        $days = [];
        foreach ($admissions as $admission) {
            $days[substr($admission->admission_date, 0, 10)][] = $admission;
        }

        return $this->render('calendar', [
            'month' => $month,
            'firstDay' => $firstDay,
            'daysInMonth' => (int) $firstDay->format('t'),
            'startOffset' => ((int) $firstDay->format('N') + 1) % 7,
            'admissionsByDay' => $days,
            'previousMonth' => $firstDay->modify('-1 month')->format('Y-m'),
            'nextMonth' => $nextMonth->format('Y-m'),
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
        if ($this->request->isPost && $model->load($this->request->post())) {
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
            $query->andWhere(['or',
                ['like', 'first_name', $part],
                ['like', 'last_name', $part],
                ['like', 'national_code', $term],
            ]);
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

        $query = Admission::find()
            ->alias('a')
            ->select(['a.id', 'a.doctor_name', 'a.ward', 'p.first_name', 'p.last_name', 'p.national_code'])
            ->innerJoin(['p' => Patient::tableName()], 'p.id = a.patient_id')
            ->leftJoin(['d' => Discharge::tableName()], 'd.admission_id = a.id')
            ->where(['a.status' => 'admitted', 'd.id' => null])
            ->orderBy(['p.last_name' => SORT_ASC, 'p.first_name' => SORT_ASC]);

        foreach ($parts as $part) {
            $query->andWhere(['or',
                ['like', 'p.first_name', $part],
                ['like', 'p.last_name', $part],
                ['like', 'p.national_code', $term],
            ]);
        }

        return array_map(static fn(array $admission): array => [
            'id' => (int) $admission['id'],
            'label' => $admission['first_name'] . ' ' . $admission['last_name']
                . ' (کد ملی: ' . $admission['national_code'] . ')',
            'patientName' => $admission['first_name'] . ' ' . $admission['last_name'],
            'nationalCode' => $admission['national_code'],
            'doctor' => $admission['doctor_name'],
            'ward' => $admission['ward'],
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
        $admissionId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $model = $admissionId === false ? null : Admission::findOne(['id' => $admissionId]);
        if ($model === null) {
            throw new NotFoundHttpException('پذیرش پیدا نشد.');
        }
        return $this->render('view', ['model' => $model]);
    }
}
