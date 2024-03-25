<?php

declare(strict_types=1);

namespace atmaliance\yii2_keycloak\controllers;

use atmaliance\yii2_keycloak\exceptions\KeycloakException;
use atmaliance\yii2_keycloak\exceptions\KeycloakCallbackException;
use atmaliance\yii2_keycloak\models\dto\KeycloakCallbackActionDTO;
use atmaliance\yii2_keycloak\models\KeycloakAuth;
use atmaliance\yii2_keycloak\models\KeycloakFetcher;
use atmaliance\yii2_keycloak\models\KeycloakState;
use atmaliance\yii2_keycloak\models\KeycloakUrl;
use atmaliance\yii2_keycloak\models\serializer\Normalizer;
use atmaliance\yii2_keycloak\models\validator\KeycloakCallbackActionValidator;
use Throwable;
use Yii;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

class AuthController extends Controller
{
    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if ($action->id === 'logout') {
            return parent::beforeAction($action);
        }

        if (!Yii::$app->user->isGuest || Yii::$app->keycloakService->getToken()->isInit()) {
            $this->redirect(Url::home(true))->send();

            return false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @param $action
     * @param $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        if ($action->id === 'logout') {
            Yii::$app->keycloakService->getToken()->forget();
        }

        return parent::afterAction($action, $result);
    }

    /**
     * @return Response
     * @throws HttpException
     * Redirect to login
     */
    public function actionLogin(): Response
    {
        try {
            KeycloakState::init();

            return $this->redirect((new KeycloakUrl())->getLoginUrl());
        } catch (Throwable $exception) {
            Yii::error(sprintf('[Keycloak Controller Error] %s', $exception->getMessage()));
        }

        throw new HttpException(503, 'Не удалось начать процесс авторизации');
    }

    /**
     * @return Response
     * @throws HttpException
     * Redirect to logout
     */
    public function actionLogout(): Response
    {
        try {
            if (Yii::$app->user->enableSession) {
                Yii::$app->user->logout();
            }

            return $this->redirect((new KeycloakUrl())->getLogoutUrl());
        } catch (Throwable $exception) {
            Yii::error(sprintf('[Keycloak Controller Error] %s', $exception->getMessage()));
        }

        throw new HttpException(503, 'Не удалось выйти из системы');
    }

    /**
     * @return Response
     * @throws HttpException
     * Redirect to register
     */
    public function actionRegister(): Response
    {
        try {
            return $this->redirect((new KeycloakUrl())->getRegisterUrl());
        } catch (Throwable $exception) {
            Yii::error(sprintf('[Keycloak Controller Error] %s', $exception->getMessage()));
        }

        throw new HttpException(503, 'Не удалось начать процесс регистрации');
    }

    /**
     * @return Response
     * @throws HttpException
     * Keycloak callback page
     */
    public function actionCallback(): Response
    {
        try {
            /** @var KeycloakCallbackActionDTO $keycloakCallbackActionDTO */
            $keycloakCallbackActionDTO = (new Normalizer())->denormalize(Yii::$app->request->get(), KeycloakCallbackActionDTO::class);
            $keycloakCallbackActionValidator = new KeycloakCallbackActionValidator();

            if (!$keycloakCallbackActionValidator->validate($keycloakCallbackActionDTO)) {
                throw new KeycloakCallbackException(print_r($keycloakCallbackActionValidator->getErrors(), true));
            }

            $keycloakToken = Yii::$app->keycloakService->getToken()->init((new KeycloakFetcher())->getTokenInformation($keycloakCallbackActionDTO->getCode()));

            (new KeycloakAuth())->authenticate($keycloakToken);
            return $this->redirect(Url::to(Yii::$app->user->getReturnUrl(Url::home(true)), true));
        } catch (Throwable $exception) {
            Yii::error(sprintf('[Keycloak Controller Error] %s', $exception->getMessage()));
        }

        Yii::$app->keycloakService->getToken()->forget();

        throw new KeycloakException(503, 'Не удалось авторизоваться');
    }
}