<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9 0009
 * Time: 9:45
 */
require_once __DIR__.'/ErrorCode.php';
class Users
{
    /**
     * 数据库连接句柄
     * @var
     */
    private $_db;

    /**
     * 存入数据库连接句柄
     * Users constructor.
     * @param $db
     */
    public function __construct($db)
    {
        $this->_db = $db;
    }

    /**
     * 用户登录
     * @param $username
     * @param $password
     */
    public function login($username, $password)
    {
        if (empty($username)) {
            throw new Exception('用户名不能为空', ErrorCode::USERNAME_CONNOT_EMPTY);
        }
        if (empty($password)) {
            throw new Exception('密码不能为空', ErrorCode::PASSWORD_CONNOT_EMPTY);
        }
        $sql = 'SELECT * FROM users WHERE `username`=:username AND `password`=:password';
        $stmt = $this->_db->prepare($sql);
        $password = $this->_md5($password);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($user)) {
            throw new Exception('用户或密码错误', ErrorCode::USERNAME_OR_PASSWORD_INVALID);
        }
        unset($user['password']);
        return $user;
    }

    /**
     * 用户注册
     * @param $username
     * @param $password
     * @return array
     * @throws Exception
     */
    public function register($username, $password)
    {
       if (empty($username)) {
           throw new Exception('用户名不能为空', ErrorCode::USERNAME_CONNOT_EMPTY);
       }
       if (empty($password)) {
           throw new Exception('密码不能为空', ErrorCode::PASSWORD_CONNOT_EMPTY);
       }
       if ($this->_isUsernameExists($username)) {
           throw new Exception('用户名已存在', ErrorCode::USERNAME_EXISTS);
       }
       $sql = 'INSERT INTO users(`username`,`password`,`created_at`) VALUES(:username,:password,:created_at)';
       $stmt = $this->_db->prepare($sql);
       $password = $this->_md5($password);
       $created_at = time();
       $stmt->bindParam(':username', $username);
       $stmt->bindParam(':password', $password);
       $stmt->bindParam(':created_at', $created_at);
       if (!$stmt->execute()) {
           throw new Exception('用户注册失败', ErrorCode::REGISTER_FAIL);
       }
       return [
           'userId' => $this->_db->lastInsertId(),
           'username' => $username,
           'password' => $password
       ];

    }

    /**
     * 返回MD5加密后的密码
     * @param $string
     * @param string $key
     * @return string
     */
    private function _md5($string, $key='api')
    {
        return md5($string.$key);
    }

    /**
     * 检测用户是否存在
     * @param $username
     * @return bool
     */
    private function _isUsernameExists($username)
    {
        $sql = 'SELECT * FROM `users` WHERE `username`=:username';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($result);
    }

}