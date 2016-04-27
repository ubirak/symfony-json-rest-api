# Symfony JSON rest API [![Build Status](https://travis-ci.org/rezzza/symfony-json-rest-api.svg?branch=master)](https://travis-ci.org/rezzza/symfony-json-rest-api)

Symfony stuff to help building a json rest api

We will find :
* [JsonBodyListener](JsonBodyListener.php) to convert raw JSON from body of your requets into request attributes
* [LinkRequestListener](LinkRequestListener.php) to validate and transform link header into request attributes
* [JsonExceptionHandler](JsonExceptionHandler.php) to transform exception into JsonResponse
