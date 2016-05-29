<?php

namespace app\controllers;

use Yii;
use yii\console\Application;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\web\HttpException;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Run action migrate up or down in web interface.
     * @return mixed
     * @throws HttpException
     */
    public function actionMigrate()
    {
        $action = Yii::$app->request->get('action');
        if(is_null($action)) {
            throw new BadRequestHttpException("Action parameter required");
        } else if($action !== 'up' && $action !== 'down') {
            throw new HttpException("Action not found");
        }

        $oldApp = Yii::$app;
        new Application([
            'id'            => 'yii-web-console',
            'basePath'      => '@app',
            'bootstrap' => ['log'],
            'components'    => [
                'db' => $oldApp->db,
                'cache' => [
                    'class' => 'yii\caching\FileCache',
                ],
                'log' => [
                    'targets' => [
                        [
                            'class' => 'yii\log\FileTarget',
                            'levels' => ['error', 'warning'],
                        ],
                    ],
                ],
            ],
        ]);
        Yii::setAlias('@migrations', '@app/migrations/');
        $migrateAction = 'migrate/' . $action;
        Yii::$app->runAction($migrateAction, ['migrationPath' => '@migrations', 'interactive' => false]);
        Yii::$app = $oldApp;
    }
}
