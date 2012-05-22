<?php
// load Tonic library and our snippet resource
require_once 'lib/tonic.php';
require_once 'api/SnippetResource.php';

$request = new Request(array('baseUri' => '/api'));
try {
    $resource = $request->loadResource();
    $response = $resource->exec($request);

} catch (ResponseException $e) {
    //If we've caught an exception, then eleviate stress by playing pacman.
    header("Location: /error.php"); die();
}
$response->output();
