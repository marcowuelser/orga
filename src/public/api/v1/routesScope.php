<?php
declare(strict_types=1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

function injectRoutesScope(\Slim\App $app, array $config)
{
    $container = $app->getContainer();
    $authOn = $config["authenticationOn"];

    // Authorization
    $requireUser = new UserAuthorizationMiddleware(
        $container->get('auth'),
        $authOn ? UserRoleFlag::RoleUser : 0);

    $requireScope = new ScopeAuthorizationMiddleware(
        $container->get('auth'),
        $container->get('scope'),
        $container->get('db'),
        $container->get('logger'));

    $app->get('/scope', function (Request $request, Response $response)
    {
        $this->logger->addInfo("Get scope data");
        $userMapper = new UserMapper($this->db, $this->logger);
        $gameMapper = new GameMapper($this->db, $this->logger);
        $playerMapper = new PlayerMapper($this->db, $this->logger);
        $characterMapper = new CharacterMapper($this->db, $this->logger);

        $scope = $this->scope->getScope();
        $reference = $this->scope->getReferenceId();

        $data = array();
        $data["scope"] = $scope;
        $data["reference"] = $reference;
        $data["user"] = $userMapper->selectById($this->scope->getUserId());

        if ($scope == ScopeEnum::ScopeUser)
        {
            // no more data
            $data["game"] = null;
            $data["player"] = null;
            $data["character"] = null;
        }
        if ($scope == ScopeEnum::ScopeGame)
        {
            $data["game"] = $gameMapper->selectById($this->scope->getGameId());
            $data["player"] = $playerMapper->selectById($this->scope->getPlayerId());
            $data["character"] = null;
        }
        if ($scope == ScopeEnum::ScopeCharacter)
        {
            $data["game"] = $gameMapper->selectById($this->scope->getGameId());
            $data["player"] = $playerMapper->selectById($this->scope->getPlayerId());
            $data["character"] = $characterMapper->selectById($this->scope->getCharacterId());
        }

        return responseWithJson($response, $data);
    })->add($requireUser)->add($requireScope);

}

?>
