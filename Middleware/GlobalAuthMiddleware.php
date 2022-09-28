<?php

namespace gstudio_kernel\Middleware;

if (!defined('IN_DISCUZ')) {
  exit('Access Denied');
}

use Error;
use gstudio_kernel\Foundation\Lang;
use gstudio_kernel\Foundation\Output;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\Store;
use gstudio_kernel\Model\LoginsModel;
use gstudio_kernel\Platform\Discuzx\Member;
use gstudio_kernel\Service\AuthService;
use ReflectionMethod;

class GlobalAuthMiddleware
{
  private function verifyToken($request,  $strongCheck = true)
  {
    $token = $request->headers("Authorization") ?: $request->query("Authorization") ?: $request->body("Authorization");
    if ($strongCheck && (empty($token) || !$token)) {
      Response::error(401, "Auth:40101", Lang::value("kernel/auth/needLogin"), [], Lang::value("kernel/auth/emptyToken"));
    }
    if (empty($token)) {
      return;
    }
    if (!preg_match("/^Bearer (.+)$/", $token, $token)) {
      Response::error(401, "Auth:40102", Lang::value("kernel/auth/needLogin"), [], Lang::value("kernel/auth/headerTOKENParamError"));
    }
    $token = $token[1];
    if ($strongCheck) {
      if (empty($token) || !$token)
        Response::error(401, "Auth:40103", Lang::value("kernel/auth/needLogin"), [], Lang::value("kernel/auth/headerAuthorizationEmpty"));
    }
    if (empty($token)) {
      return null;
    }
    $ULM = new LoginsModel();
    $token = $ULM->getByToken($token);
    if ($token === null) {
      header("Authorization", "");
      if ($strongCheck) {
        Response::error(401, "Auth:40104", Lang::value("kernel/auth/needLogin"), [], Lang::value("kernel/auth/invalidToken"));
      }
    }
    if (!$token) {
      return null;
    }
    $expirationDate = $token['createdAt'] + $token['expiration'];
    $diffDay = round((time() - $token['createdAt']) / 86400);
    $expirationDay = round($token['expiration'] / 86400);
    if (time() > $expirationDate || $diffDay > $expirationDay) {
      Response::error(401, "Auth:40105", Lang::value("kernel/auth/loginExpired"), [], Lang::value("kernel/auth/expiredToken"));
    }
    header("Authorization:" . $token['token'] . "/" . $expirationDate, true);
    //* 如果token的有效期剩余20%，就自动刷新token
    if ($diffDay / $expirationDay > 0.8) {
      //* 自动刷新token
      $newToken = AuthService::generateToken($token['userId']);
      header("Authorization:" . $newToken['value'] . "/" . $newToken['expirationDate'], true);
      $ULM->deleteByToken($token['token']);
      $newToken['token'] = $newToken['value'];
      $token = $newToken;
    }

    Store::setApp([
      "auth" => $token
    ]);
  }
  public function handle($next, $request)
  {
    $router = $request->router;
    $isAdminVerify = false;
    if (!$router) {
      $next();
      return;
    }

    if (!class_exists($router['controller'])) {
      throw new Error("Router controller(" . $router['controller'] . ") not exists");
    }

    if (get_parent_class($router['controller']) === "gstudio_kernel\Foundation\Controller\AuthController") {

      //* 验证Formhash
      $router['controller']::verifyFormhash();

      $needCheckAdmin = true;
      $isAdminVerify = false;
      if (is_array($router['controller']::$AdminMethods)) {
        $methods = array_map(function ($item) {
          return strtolower($item);
        }, $router['controller']::$AdminMethods);
        if (count($methods) && !in_array(strtolower($request->method), $methods)) {
          $needCheckAdmin = false;
        }
      }

      if ($needCheckAdmin) {
        if (method_exists($router['controller'], "Admin")) {
          $adminMethodRM = new ReflectionMethod($router['controller'], "Admin");
          if ($adminMethodRM->isStatic() && $router['controller']::Admin()) {
            $isAdminVerify = true;
            $this->verifyToken($request);
            $router['controller']::verifyAdmin();
          }
        } else if ($router['controller']::$Admin) {
          $isAdminVerify = true;
          $this->verifyToken($request);
          $router['controller']::verifyAdmin();
        }
      }

      if ($isAdminVerify === false) {
        $needCheckAuth = true;
        if (is_array($router['controller']::$AuthMethods)) {
          $methods = array_map(function ($item) {
            return strtolower($item);
          }, $router['controller']::$AuthMethods);
          if (count($methods) && !in_array(strtolower($request->method), $methods)) {
            $needCheckAuth = false;
          }
        }

        if ($needCheckAuth) {
          if (method_exists($router['controller'], "Auth")) {
            $authMethodRM = new ReflectionMethod($router['controller'], "Auth");
            if ($authMethodRM->isStatic() && $router['controller']::Auth()) {
              $this->verifyToken($request);
              $router['controller']::verifyAuth();
            }
          } else if ($router['controller']::$Auth) {
            $this->verifyToken($request);
            $router['controller']::verifyAuth();
          } else {
            $this->verifyToken($request, false);
          }
        } else {
          $this->verifyToken($request, false);
        }
      }
    } else {
      if ($request->headers("Authorization")) {
        $this->verifyToken($request);
      }
    }

    $Auth = Store::getApp("auth");
    $UserInfo = null;
    if ($Auth) {
      $UserInfo = Member::get($Auth['userId']);
      include_once libfile("function/member");
      \setloginstatus($UserInfo, 0);
    }
    Store::setApp([
      "member" => $UserInfo
    ]);


    $next();
  }
}