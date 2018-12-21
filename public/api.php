<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require '../vendor/autoload.php';

class MyDB extends SQLite3 {
    function __construct() {
        $this->open('../participants.db');
    }
}

$db = new MyDB();
if (!$db) {
    echo $db->lastErrorMsg();
    exit();
}

$app = new \Slim\App;

$app->get(
    '/api/participants',
    function (Request $request, Response $response, array $args) use ($db) {
        $sql = "SELECT * FROM participant";
        $ret = $db->query($sql);
        $participants = [];
        while ($participant = $ret->fetchArray(SQLITE3_ASSOC)) {
            $participants[] = $participant;
        }
        return $response->withJson($participants);
    }
);


$app->get(
    '/participants/{id}',
    function (Request $request, Response $response, array $args) use ($db) {
        $sql = "SELECT * FROM participant WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $args['id']);
        $ret = $stmt->execute();
        $participant = $ret->fetchArray(SQLITE3_ASSOC);
        if ($participant) {
            return $response->withJson($participant);
        } else {
            return $response->withStatus(404)->withJson(['error' => 'Such participant does not exist.']);
        }
    }
);


$app->post(
    '/api/participants',
    function (Request $request, Response $response, array $args) use ($db) {
        $requestData = $request->getParsedBody();
        if (!isset($requestData['firstname']) || !isset($requestData['lastname'])) {
            return $response->withStatus(400)->withJson(['error' => 'Firstname and lastname are required.']);
        }
        $sql = "INSERT INTO 'participant' (firstname, lastname) VALUES (:firstname, :lastname)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue('firstname', $requestData['firstname']);
        $stmt->bindValue('lastname', $requestData['lastname']);
        $stmt->execute();
        $newParticipantId = $db->lastInsertRowID();
        $requestData['id'] = $newParticipantId;
        return $response->withStatus(201)->withJson($requestData)->withHeader('Location', "/participants/$newParticipantId");
    }
);


$app->delete(
    '/api/participants/{id}',
    function (Request $request, Response $response, array $args) use ($db) {
        $sql = "DELETE FROM participant WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue('id', $args['id']);
        $stmt->execute();
        return $response->withStatus(204);
    }
);

$app->run();
