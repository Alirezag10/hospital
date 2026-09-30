<?php

namespace app\controllers;

use app\models\AdmissionService;
use app\models\AdmissionServiceSearch;
use app\models\Service;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class AdmissionServiceController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new AdmissionServiceSearch();
        $dataProvider = $searchModel->search(
            $this->request->queryParams
        );

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new AdmissionService();
        $model->loadDefaultValues();

        if (
            $this->request->isPost
            && $model->load($this->request->post())
            && $this->saveService($model, $this->request->post())
        ) {
            return $this->redirect([
                'view',
                'id' => $model->id,
            ]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (
            $this->request->isPost
            && $model->load($this->request->post())
            && $this->saveService($model, $this->request->post())
        ) {
            return $this->redirect([
                'view',
                'id' => $model->id,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    private function saveService(AdmissionService $model, array $post)
    {
        if (!$model->validate(['admission_id', 'service_id'])) {
            return false;
        }

        $insert = $model->isNewRecord;
        $targetAdmissionId = (int) $model->admission_id;

        $sourceAdmissionId = $insert
            ? null
            : (int) $model->getOldAttribute('admission_id');

        $admissionIds = [$targetAdmissionId];

        if (!$insert) {
            $admissionIds[] = $sourceAdmissionId;
        }

        $admissionIds = array_unique($admissionIds);
        sort($admissionIds, SORT_NUMERIC);

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            foreach ($admissionIds as $admissionId) {
                if (!$this->lockOpenAdmission($admissionId)) {
                    $model->addError(
                        'admission_id',
                        'ثبت یا تغییر خدمات فقط برای پذیرش بستری و ترخیص‌نشده مجاز است.'
                    );

                    $transaction->rollBack();
                    return false;
                }
            }

            if (!$insert) {
                $row = $db->createCommand(
                    'SELECT *
                     FROM admission_services
                     WHERE id = :id
                     FOR UPDATE',
                    [':id' => $model->id]
                )->queryOne();

                if ($row === false) {
                    throw new NotFoundHttpException(
                        'خدمت پذیرش پیدا نشد.'
                    );
                }

                if (
                    (int) $row['admission_id']
                    !== $sourceAdmissionId
                ) {
                    $model->addError(
                        'admission_id',
                        'این خدمت در درخواست دیگری تغییر کرده است؛ صفحه را دوباره باز کنید.'
                    );

                    $transaction->rollBack();
                    return false;
                }

                $model->setAttributes($row, false);
                $model->setOldAttributes($row);
                $model->load($post);

                if (!$model->validate(['admission_id', 'service_id'])) {
                    $transaction->rollBack();
                    return false;
                }
            }

            $serviceChanged = $insert
                || (string) $model->service_id
                    !== (string) $model->getOldAttribute('service_id');

            if ($serviceChanged) {
                $service = Service::findOne([
                    'id' => $model->service_id,
                    'active' => 1,
                ]);

                if ($service === null) {
                    $model->addError(
                        'service_id',
                        'یک خدمت فعال انتخاب کنید.'
                    );

                    $transaction->rollBack();
                    return false;
                }

                $model->unit_price = $service->price;
            } else {
                $model->unit_price = $model->getOldAttribute(
                    'unit_price'
                );
            }

            if ($insert) {
                $model->created_at = $db->createCommand(
                    'SELECT NOW()'
                )->queryScalar();
            }

            if (!$model->save()) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;
        } catch (\Throwable $error) {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }

            throw $error;
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $admissionId = (int) $model->admission_id;

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();

        try {
            if (!$this->lockOpenAdmission($admissionId)) {
                throw new ForbiddenHttpException(
                    'خدمات پذیرش ترخیص‌شده قابل حذف نیست.'
                );
            }

            $row = $db->createCommand(
                'SELECT *
                 FROM admission_services
                 WHERE id = :id
                 FOR UPDATE',
                [':id' => $model->id]
            )->queryOne();

            if ($row === false) {
                throw new NotFoundHttpException(
                    'خدمت پذیرش پیدا نشد.'
                );
            }

            if ((int) $row['admission_id'] !== $admissionId) {
                throw new ForbiddenHttpException(
                    'پذیرش این خدمت تغییر کرده است؛ صفحه را دوباره باز کنید.'
                );
            }

            $model->setAttributes($row, false);
            $model->setOldAttributes($row);

            if ($model->delete() !== 1) {
                throw new \RuntimeException(
                    'خدمت پذیرش حذف نشد.'
                );
            }

            $transaction->commit();
        } catch (\Throwable $error) {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }

            throw $error;
        }

        return $this->redirect(['index']);
    }

    private function lockOpenAdmission($id)
    {
        $db = Yii::$app->db;

        $row = $db->createCommand(
            'SELECT id, status
             FROM admissions
             WHERE id = :id
             FOR UPDATE',
            [':id' => $id]
        )->queryOne();

        if ($row === false || $row['status'] !== 'admitted') {
            return false;
        }

        $dischargeId = $db->createCommand(
            'SELECT id
             FROM discharges
             WHERE admission_id = :id
             FOR UPDATE',
            [':id' => $id]
        )->queryScalar();

        return $dischargeId === false;
    }

    protected function findModel($id)
    {
        $model = AdmissionService::findOne(['id' => $id]);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException(
            'خدمت پذیرش پیدا نشد.'
        );
    }
}