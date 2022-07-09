<?php

class RequestDispatcher {

    public function __construct(
        protected Router $router
    ) {}

    public function dispatch(): void {
        try {
            if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET']))
                throw new NotImplementedException('Method '.$_SERVER['REQUEST_METHOD'].' not implemented');

            $route = $this->router->find(self::path());
            if ($route === null)
                throw new NotFoundException('Route not found');

            $route = preg_split('/ +/', $route);
            $handler_class = $route[0];
            if (($pos = strrpos($handler_class, '/')) !== false) {
                $class_name = substr($handler_class, $pos+1);
                $class_name = ucfirst(to_camel_case($class_name));
                $handler_class = str_replace('/', '\\', substr($handler_class, 0, $pos)).'\\'.$class_name;
            } else {
                $handler_class = ucfirst(to_camel_case($handler_class));
            }
            $handler_class = 'handler\\'.$handler_class;

            if (!class_exists($handler_class))
                throw new NotFoundException('Handler class "'.$handler_class.'" not found');

            $router_input = [];
            if (count($route) > 1) {
                for ($i = 1; $i < count($route); $i++) {
                    $var = $route[$i];
                    list($k, $v) = explode('=', $var);
                    $router_input[trim($k)] = trim($v);
                }
            }

            $skin = new Skin();
            $skin->static[] = '/css/common-bundle.css';
            $skin->static[] = '/js/common.js';

            /** @var RequestHandler $handler */
            $handler = new $handler_class($skin, LangData::getInstance(), $router_input);
            $resp = $handler->beforeDispatch();
            if ($resp instanceof Response) {
                $resp->send();
                return;
            }

            $resp = call_user_func([$handler, strtolower($_SERVER['REQUEST_METHOD'])]);
        } catch (NotFoundException $e) {
            $resp = $this->getErrorResponse($e, 'not_found');
        } catch (ForbiddenException $e) {
            $resp = $this->getErrorResponse($e, 'forbidden');
        } catch (NotImplementedException $e) {
            $resp = $this->getErrorResponse($e, 'not_implemented');
        } catch (UnauthorizedException $e) {
            $resp = $this->getErrorResponse($e, 'unauthorized');
        }
        $resp->send();
    }

    protected function getErrorResponse(Exception $e, string $render_function): Response {
        $ctx = new SkinContext('\\skin\\error');
        $html = call_user_func([$ctx, $render_function], $e->getMessage());
        return new Response($e->getCode(), $html);
    }

    public static function path(): string {
        $uri = $_SERVER['REQUEST_URI'];
        if (($pos = strpos($uri, '?')) !== false)
            $uri = substr($uri, 0, $pos);
        return $uri;
    }

}
