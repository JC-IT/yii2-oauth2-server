<?php
declare(strict_types=1);

namespace JCIT\oauth2\controllers\authorize;

use JCIT\oauth2\exceptions\OAuthHttpException;
use JCIT\oauth2\Module;
use JCIT\oauth2\repositories\AccessTokenRepository;
use JCIT\oauth2\repositories\ScopeRepository;
use JCIT\oauth2\traits\PopulatePsrResponseTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use yii\base\Action;
use yii\base\Security;
use yii\web\Request;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\web\Session;
use yii\web\User;

class Authorize extends Action
{
    use PopulatePsrResponseTrait;

    const SESSION_AUTH_REQUEST = 'authRequest';
    const SESSION_AUTH_TOKEN = 'authToken';

    public string $viewFile = 'authorize';
    public string $formModelClass = \JCIT\oauth2\models\Authorize::class;

    public function __construct(
        $id,
        $controller,
        protected AccessTokenRepository $accessTokenRepository,
        protected ScopeRepository $scopeRepository,
        $config = []
    ) {
        parent::__construct($id, $controller, $config);
    }

    protected function approveRequest(
        Module $module,
        AuthorizationRequest $authorizationRequest,
        UserEntityInterface $userEntity,
        Response $response
    ): Response {
        $authorizationRequest->setUser($userEntity);
        $authorizationRequest->setAuthorizationApproved(true);

        $this->convertResponse(
            $module->getAuthorizationServer()->completeAuthorizationRequest($authorizationRequest, $module->getServerResponse()),
            $response
        );

        return $response;
    }

    protected function denyRequest(
        AuthorizationRequest $authorizationRequest,
        Request $request,
        Response $response
    ): Response {
        $redirectUri = $authorizationRequest->getRedirectUri();
        $clientRedirectUris = $authorizationRequest->getClient()->getRedirectUri();
        if (!in_array($redirectUri, $clientRedirectUris)) {
            $redirectUri = reset($clientRedirectUris);
        }

        $separator = $authorizationRequest->getGrantTypeId() === 'implicit' ? '#' : (strstr($redirectUri, '?') ? '&' : '?');

        return $response->redirect($redirectUri . $separator . 'error=access_denied&state=' . $request->getQueryParam('state'));
    }

    public function run(
        Request $request,
        Response $response,
        Session $session,
        Security $security,
        User $user,
    ) {
        /** @var Module $module */
        $module = $this->controller->module;
        $psrRequest = $module->getServerRequest();

        try {
            $authRequest = $session->get(self::SESSION_AUTH_REQUEST)
                ?? $module->getAuthorizationServer()->validateAuthorizationRequest($psrRequest);

            if ($user->isGuest) {
                return $user->loginRequired();
            }

            /** @var \JCIT\oauth2\models\Authorize $model */
            $model = new ($this->formModelClass)($authRequest, $user->identity);

            if ($request->isPost && $model->load($request->bodyParams) && $model->validate()) {
                $session->remove(self::SESSION_AUTH_REQUEST);
                $session->remove(self::SESSION_AUTH_TOKEN);
                if ($model->accept) {
                    return $this->approveRequest($module, $authRequest, $user->identity, $response);
                } else {
                    return $this->denyRequest($authRequest, $request, $response);
                }
            }

            $scopesToApprove = $this->scopeRepository->resolveForAuthorizationRequest($module, $authRequest);

            $accessToken = $this->accessTokenRepository->fetchValidForUser($user->identity, $authRequest->getClient());
            if ($accessToken) {
                foreach ($scopesToApprove as $key => $scope) {
                    if (in_array($scope->getIdentifier(), $accessToken->scopes)) {
                        unset($scopesToApprove[$key]);
                    }
                }
            }

            if (empty($scopesToApprove)) {
                return $this->approveRequest($module, $authRequest, $user->identity, $response);
            }

            $session->set(self::SESSION_AUTH_REQUEST, $authRequest);
            $session->set(self::SESSION_AUTH_TOKEN, $authToken = $security->generateRandomString());

            return $this->controller->render(
                $this->viewFile,
                [
                    'authToken' => $authToken,
                    'model' => $model,
                    'scopes' => $scopesToApprove,
                ]
            );
        } catch (OAuthServerException $exception) {
            throw new OAuthHttpException($exception);
        } catch (\Exception $exception) {
            throw new ServerErrorHttpException(
                'Unable to respond to authorization request.', 0,
                YII_DEBUG ? $exception : null
            );
        }
    }
}
