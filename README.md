# Symfony JSON rest API [![Build Status](https://travis-ci.org/rezzza/symfony-json-rest-api.svg?branch=master)](https://travis-ci.org/rezzza/symfony-json-rest-api)

Symfony stuff to help building a JSON REST api :
- Support JSON payload
- Support LINK headers
- Handle exception as JSON response
- Possibility to validate json payload through json schema
- Possibility to map custom exception to http code

Here is a simple setup to start :
```xml
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <service id="json_body_listener.ui" class="Rezzza\SymfonyRestApiJson\JsonBodyListener">
            <argument type="service">
                <service class="Rezzza\SymfonyRestApiJson\PayloadValidator">
                    <argument type="service">
                        <service class="Rezzza\SymfonyRestApiJson\JsonSchemaTools" />
                    </argument>
                </service>
            </argument>
            <tag name="kernel.event_listener" event="kernel.request" method="onKernelRequest" priority="24" />
        </service>

        <service id="link_request_listener.ui" class="Rezzza\SymfonyRestApiJson\LinkRequestListener">
            <tag name="kernel.event_listener" event="kernel.request" method="onKernelRequest" priority="10" />
        </service>

        <service id="json_exception_handler.ui" class="Rezzza\SymfonyRestApiJson\JsonExceptionHandler">
            <argument type="service">
                <service class="Rezzza\SymfonyRestApiJson\ExceptionHttpCodeMap">
                    <argument>%exception_http_code_map%</argument>
                </service>
            </argument>
            <tag name="kernel.event_listener" event="kernel.exception" method="onKernelException" priority="32" />
        </service>

        <service id="json_exception_controller.ui" class="Rezzza\SymfonyRestApiJson\JsonExceptionController">
            <argument>%kernel.debug%</argument>
            <argument>%show_exception_token%</argument>
        </service>

        <service id="ui.event_listener.exception_listener" class="Symfony\Component\HttpKernel\EventListener\ExceptionListener">
            <tag name="kernel.event_subscriber" />
            <tag name="monolog.logger" channel="request" />
            <argument>json_exception_controller.ui:showException</argument>
            <argument type="service" id="logger" on-invalid="null" />
        </service>
    </services>
</container>
```
