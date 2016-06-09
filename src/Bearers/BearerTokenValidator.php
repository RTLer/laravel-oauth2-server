<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */
namespace RTLer\Oauth2\Bearer;

use Carbon\Carbon;
use League\OAuth2\Server\AuthorizationValidators\AuthorizationValidatorInterface;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class BearerTokenValidator implements AuthorizationValidatorInterface
{
    use CryptTrait;

    /**
     * @var \RTLer\Oauth2\Repositories\AccessTokenRepository
     */
    private $accessTokenRepository;

    /**
     * BearerTokenValidator constructor.
     *
     * @param \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface $accessTokenRepository
     */
    public function __construct(AccessTokenRepositoryInterface $accessTokenRepository)
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthorization(ServerRequestInterface $request)
    {
        if ($request->hasHeader('authorization') === false) {
            throw OAuthServerException::accessDenied('Missing "Authorization" header');
        }

        $header = $request->getHeader('authorization');
        $accessTokenId = trim($header[0]);

        try {
            $accessTokenEntity =
                $this->accessTokenRepository->findAccessToken($accessTokenId);

            // Check if token has been revoked
            if (is_null($accessTokenEntity)) {
                throw OAuthServerException::accessDenied('Access token has been revoked');
            }

            // Ensure access token hasn't expired
            if ($accessTokenEntity->getExpiryDateTime()->lt(Carbon::now())) {
                throw OAuthServerException::accessDenied('Access token is invalid');
            }

            // Return the request with additional attributes
            return $request
                ->withAttribute('oauth_access_token_id', $accessTokenEntity->getIdentifier())
                ->withAttribute('oauth_client_id', $accessTokenEntity->getClient()->getIdentifier())
                ->withAttribute('oauth_user_id', $accessTokenEntity->getUserIdentifier())
                ->withAttribute('oauth_scopes', $accessTokenEntity->getScopes());
        } catch (\InvalidArgumentException $exception) {
            // JWT couldn't be parsed so return the request as is
            throw OAuthServerException::accessDenied($exception->getMessage());
        }
    }
}
