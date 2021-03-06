<?php

namespace AmoCRM;

class Request
{
    const AUTH = 1;
    const INFO = 2;
    const GET = 3;
    const SET = 4;
    const GOALS = 5;

    const FORMAT_ARRAY = true;
    const FORMAT_OBJECT = false;

    public $request_type;
    public $post;
    public $url;
    public $type;
    public $action;
    public $params;
    public $format;

    private $if_modified_since;
    public $object;

    public function __construct($request_type = null, $params = null, $object = null)
    {
        $this->request_type = $request_type;
        $this->post = false;
        $this->params = $params;
        $this->object = $object;
        $this->format = self::FORMAT_OBJECT;

        switch ($request_type) {
            case Request::AUTH:
                $this->createAuthRequest();
                break;
            case Request::INFO:
                $this->createInfoRequest();
                break;
            case Request::GET:
                $this->createGetRequest();
                break;
            case Request::SET:
                $this->createPostRequest();
                break;

            case Request::GOALS:
                $this->createGetGoals();
                break;
        }
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function setIfModifiedSince($if_modified_since)
    {
        $this->if_modified_since = $if_modified_since;
    }

    public function getIfModifiedSince()
    {
        return empty($this->if_modified_since) ? false : $this->if_modified_since;
    }

    private function createGetGoals()
    {
        //https://lookvokrug.amocrm.ru/ajax/stats/goals/settings/
        $this->post = true;
        $this->url = '/ajax/stats/goals/settings/';
    }

    private function createAuthRequest()
    {
        $this->post = true;
        $this->url = '/private/api/auth.php?type=json';

        $this->params = [
            'USER_LOGIN' => $this->params->user,
            'USER_HASH' => $this->params->key
        ];
    }

    private function createInfoRequest()
    {
        $this->url = '/private/api/v2/json/accounts/current?with=users&free_users=Y';
    }

    private function createGetRequest()
    {
        $this->url = '/private/api/v2/json/' . $this->object[0] . '/' . $this->object[1];
        $this->url .= (count($this->params) ? '?' . http_build_query($this->params) : '');
    }

    private function createPostRequest()
    {
        if (!is_array($this->params)) {
            $this->params = [$this->params];
        }

        $key_name = $this->params[0]->key_name;
        $url_name = $this->params[0]->url_name;
        $id = $this->params[0]->id;

        $action = (isset($id)) ? 'update' : 'add';
        $params = [];
        $params['request'][$key_name][$action] = $this->params;

        $this->post = true;
        $this->type = $key_name;
        $this->action = $action;
        $this->url = '/private/api/v2/json/' . $url_name . '/set';
        $this->params = $params;
    }
}
