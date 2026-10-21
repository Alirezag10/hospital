<?php
namespace app\controllers;

use app\components\RoleController;
use app\components\FormInput;
use app\models\MedicalItem;
use app\models\Patient;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MedicalItemController extends RoleController
{
    public function actionIndex()
    {
        $q = $this->request->get('q','');
        $q = is_string($q) ? mb_substr(trim($q),0,100) : '';
        $kind = $this->request->get('kind','');
        $kind = in_array($kind,['medicine','supply'],true) ? $kind : '';
        $query = MedicalItem::find()->andFilterWhere(['kind'=>$kind])->andFilterWhere(['like','title',$q]);
        return $this->render('index', ['q'=>$q,'kind'=>$kind,'dataProvider'=>new ActiveDataProvider([
            'query'=>$query,'sort'=>['defaultOrder'=>['title'=>SORT_ASC]],'pagination'=>['defaultPageSize'=>10,'pageSizeLimit'=>[10,50]],
        ])]);
    }
    public function actionCreate() { return $this->form(new MedicalItem(['active'=>1,'kind'=>'medicine'])); }
    public function actionUpdate($id)
    {
        $id = filter_var($id,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $model = $id === false ? null : MedicalItem::findOne($id);
        if ($model === null) { throw new NotFoundHttpException('قلم پیدا نشد.'); }
        return $this->form($model);
    }
    private function form(MedicalItem $model)
    {
        if ($this->request->isPost && FormInput::load($model,$this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success','فهرست دارو و مصرفی به‌روزرسانی شد.');
            return $this->redirect(['index']);
        }
        return $this->render('form',compact('model'));
    }
    public function actionSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = $this->request->get('q',''); $kind = $this->request->get('kind','');
        if (!is_string($q) || !in_array($kind,['medicine','supply'],true)) { return []; }
        $q = mb_substr(Patient::normalizeDigits(trim($q)),0,100);
        if (mb_strlen($q)<2) { return []; }
        $rows = MedicalItem::find()->where(['active'=>1,'kind'=>$kind])->andWhere(['like','title',$q])
            ->orderBy(['title'=>SORT_ASC])->limit(20)->all();
        return array_map(static fn($m)=>['id'=>$m->id,'title'=>$m->title,'kind'=>$m->kind,'price'=>$m->unit_price],$rows);
    }
}
