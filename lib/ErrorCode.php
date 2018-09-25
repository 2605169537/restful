<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9 0009
 * Time: 16:21
 */
class ErrorCode
{
    const USERNAME_EXISTS=1;//用户名已存在
    const USERPASSWORD_CONNOT_EMPTY = 2;//密码不能为空
    const USERNAME_CONNOT_EMPTY = 3;//用户名不能为空
    const REGISTER_FAIL = 4; //注册失败
    const USERNAME_OR_PASSWORD_INVALID = 5;//用户或密码错误
    const ARTICLE_TITLE_CONNOT_EMPTY = 6;//文章标题不能为空
    const ARTICLE_CONTENT_CONNOT_EMPTY = 7;//文章内容不能为空
//    const USER_ID_CONNOT_EMPTY = 8;//用户ID不能为空
    const ARTICLE_CREATE_FAIL = 9;//文章创建失败
    const ARTICLE_ID_CONNOT_EMPTY = 10;//文章ID不能为空
    const ARTICLE_NOT_EXISTS = 11;//文章不存在
    const ARTICLE_EDIT_FAIL = 12;//文章编辑失败
    const PERMISSION_DENIED = 13;//你无权操作
    const ARTICLE_DELETE_FAIL = 14;//文章删除失败
    const PAGE_SIZE_TO_BIG = 15;//分页数据太大

}