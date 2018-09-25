<?php
//echo '<pre>';
//print_r($_SERVER);die;

require_once __DIR__.'/../lib/Users.php';
require_once __DIR__.'/../lib/Articles.php';
$pdo = require_once __DIR__.'/../lib/db.class.php';
class Restful
{
    /**
     * 用户资源
     * @var Users
     */
    private $_user;
    /**
     * 文章资源类
     * @var Articles
     */
    private $_article;
    /**
     * 请求方法名称
     * @var
     */
    private $_requestMethod;
    /**
     * 请求资源名称
     * @var
     */
    private $_resourceNmae;
    /**
     * 允许请求的资源
     * @var array
     */
    private $_allowResources = ['users','articles'];

    /**
     * 请求标识
     * @var
     */
    private $_id;

    /**
     * 常用的请求状态
     * @var array
     */
    private $_statusCodes = [
        200 => 'Ok',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Server Internal Error'
    ];

    /**
     * 允许请求的http请求方法
     * @var array
     */
    private $_allowRequestMethods = ['GET', 'POST', 'CREATE', 'PUT', 'DELETE'];

    /**
     * 构造方法
     * Restful constructor.
     * @param Users $users
     * @param Articles $articles
     */
    public function __construct(Users $users,Articles $articles)
    {
        $this->_user = $users;
        $this->_article = $articles;
    }

    /**
     * 运行方法
     */
    public function run()
    {
        try {
            $this->_setupRequestMethod();
            $this->_setupResource();
//            print_r([
//                'method' => $this->_requestMethod,
//                'param' => $this->_resourceNmae,
//                'id' => $this->_id
//            ]);
            if ($this->_resourceNmae == 'users') {
                return $this->_json($this->_handleUser());
            } else {
                return $this->_json($this->_handleArticle());
            }
        } catch (Exception $e) {
            $this->_json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 输出json
     * @param $array
     */
    private function _json($array, $code = 0)
    {
        if ($code >0 && $code != 200 && $code != 204) {
            header('HTTP/1.1 '.$code.' '.$this->_statusCodes[$code]);
        }
        header('Content-Type:application/json;charset=utf-8');
        echo json_encode($array, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * 初始化请求方法
     * @throws Exception
     */
    public function _setupRequestMethod()
    {
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($this->_requestMethod, $this->_allowRequestMethods)) {
            throw new Exception('请求方法不被允许', 405);
        }
    }

    /**
     * 初始化请求资源
     */
    public function _setupResource()
    {
//        $path = $_SERVER['PATH_INFO'];
        $path = $_SERVER['REQUEST_URI'];
        $path = explode('?', $path)[0];
        $params = explode('/', $path);
        $this->_resourceNmae = $params[2];
//        return $params;
        if (!in_array($this->_resourceNmae, $this->_allowResources)) {
            throw new Exception('请求资源不被允许', 400);
        }
        if (!empty($params[2])) {
            $this->_id = $params[2];
        }
    }

    /**
     * 请求用户
     * @return array
     * @throws Exception
     */
    private function _handleUser()
    {
        if ($this->_requestMethod != 'POST') {
            throw new Exception('请求方法不被允许', 400);
        }
        $body = $this->_getBodyParams();
        if (empty($body['username'])) {
            throw new Exception('用户名不能为空', 400);
        }
        if (empty($body['password'])) {
            throw new Exception('密码不能为空', 400);
        }
//        var_dump($this->_user->register($body['username'], $body['password']));
        return $this->_user->register($body['username'], $body['password']);
    }

    /**
     * 处理文章逻辑
     * @return array
     * @throws Exception
     */
    private function _handleArticle()
    {
        switch ($this->_requestMethod) {
            case 'POST':
                return $this->_handleArticleCreate();
            case 'PUT':
                return $this->handleArticleEdit();
            case 'DELETE':
                return $this->_handleArticleDelete();
            case 'GET':
                if (empty($this->_id)) {
                    return $this->_handleArticleView();
                } else {
                    return $this->_handleArticleList();
                }
            default:
                throw new Exception('请求方法不被允许',405);
        }
    }

    /**
     * 处理文章的创建
     * @return array
     * @throws Exception
     */
    private function _handleArticleCreate()
    {
        $body = $this->_getBodyParams();
        if (empty($body['title'])) {
            throw new Exception('文章标题不能为空', 400);
        }
        if (empty($body['content'])) {
            throw new Exception('文章内容不能为空', 400);
        }
        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        try {
            $article =  $this->_article->create($user['id'], $body['title'],$body['content']);
            return $article;
        } catch (Exception $e) {
            if (!in_array($e->getCode(),
                   [
                       ErrorCode::ARTICLE_TITLE_CONNOT_EMPTY,
                       ErrorCode::ARTICLE_CONTENT_CONNOT_EMPTY
                   ]
                ))
            {
                throw new Exception($e->getMessage(), 400);
            }
            throw new Exception($e->getMessage(), 500);
        }
    }

    /**
     * 用户认证
     * @param $PHP_AUTH_USER
     * @param $PHP_AUTH_PW
     * @return mixed
     * @throws Exception
     */
    private function _userLogin($PHP_AUTH_USER, $PHP_AUTH_PW)
    {
        try {
            return $this->_user->login($PHP_AUTH_USER, $PHP_AUTH_PW);
        } catch (Exception $e) {
            if (in_array($e->getCode(),
                [
                    ErrorCode::USERNAME_CONNOT_EMPTY,
                    ErrorCode::USERPASSWORD_CONNOT_EMPTY,
                    ErrorCode::USERNAME_OR_PASSWORD_INVALID
                ]))
            {
                throw new Exception($e->getMessage(), 400);
            }
            throw new Exception($e->getMessage(), 500);
        }
    }
    /**
     * 获取请求体
     * @return mixed
     * @throws Exception
     */
    private function _getBodyParams()
    {
        $data = file_get_contents("php://input");
        if (empty($data)) {
            throw new Exception('请求数据无效', 400);
        }
        return json_decode($data, true);
    }
}


$users = new Users($pdo);
$articles = new Articles($pdo);
$restful = new Restful($users, $articles);
$restful->run();