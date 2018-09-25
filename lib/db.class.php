<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9 0009
 * Time: 9:45
 */
$pdo = new PDO('mysql:host=localhost;dbname=restful;','root','root');
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);//统一数据类型
return $pdo;