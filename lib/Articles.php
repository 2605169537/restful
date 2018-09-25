<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9 0009
 * Time: 9:46
 */
require_once __DIR__.'/ErrorCode.php';

class Articles
{
    private $_db;

    public function __construct($db)
    {
        $this->_db = $db;
    }

    /**
     * 创建文章
     * @param $userId
     * @param $title
     * @param $content
     * @return array
     * @throws Exception
     */
    public function create($userId, $title, $content)
    {
        if (empty($title)) {
            throw new Exception('文章标题不能为空', ErrorCode::ARTICLE_TITLE_CONNOT_EMPTY);
        }
        if (empty($content)) {
            throw new Exception('文章内容不能为空', ErrorCode::ARTICLE_CONTENT_CONNOT_EMPTY);
        }
        $sql = 'INSERT INTO articles(`user_id`,`title`,`content`,`created_at`) VALUES(:user_id,:title,:content,:created_at)';
        $stmt = $this->_db->prepare($sql);
        $created_at = date('Y-m-d H:i:s', time());
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':created_at', $created_at);
        if (!$stmt->execute()) {
            throw new Exception('文章创建失败', ErrorCode::ARTICLE_CREATE_FAIL);
        }
        return [
            'id' =>$this->_db->lastInsertId(),
            'user_id' => $userId,
            'title' => $title,
            'content' => $content
        ];
    }

    /**
     * 修改文章
     * @param $articleId
     * @param $title
     * @param $content
     * @param $userId
     * @return array
     * @throws Exception
     */
    public function edit($articleId, $title, $content, $userId)
    {
        if (empty($articleId) && $articleId != 0) {
            throw new Exception('文章ID不能为空', ErrorCode::ARTICLE_ID_CONNOT_EMPTY);
        }
        $article = $this->view($articleId);
        if ($article['user_id'] != $userId) {
            throw new Exception('你无权编辑该文章', ErrorCode::PERMISSION_DENIED);
        }
        $title = empty($title) ? $article['title'] : $title;
        $content = empty($content) ? $article['content'] : $content;
        if ($title === $article['title'] && $content === $article['content']) {
            return $article;
        }
        $sql = 'UPDATE articles SET `title`=:title,`content`=:content WHERE `id`=:id';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':id', $articleId);
        if (!$stmt->execute()) {
            throw new Exception('文章编辑失败', ErrorCode::ARTICLE_EDIT_FAIL);
        }
        return [
            'id' => $articleId,
            'title' => $title,
            'content' => $content,
            'created_at' => $article['created_at']
        ];
    }

    public function delete($articleId, $userId)
    {
        $article = $this->view($articleId);
        if ($article['user_id'] != $userId) {
            throw new Exception('你无权操作', ErrorCode::PERSSION_DENIED);
        }
        $sql = 'DELETE FROM `articles` WHERE `id`=:id AND `user_id`=:user_id';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':id', $articleId);
        $stmt->bindParam(':user_id', $userId);
        if (!$stmt->execute()) {
            throw new Exception('文章删除失败', ErrorCode::ARTICLE_DELETE_FAIL);
        }
        return true;
    }

    /**
     * 查看一片文章
     * @param $articleId
     * @return mixed
     * @throws Exception
     */
    public function view($articleId)
    {
        if (empty($articleId) && $articleId != 0) {
            throw new Exception('文章ID不能为空', ErrorCode::ARTICLE_ID_CONNOT_EMPTY);
        }
        $sql = 'SELECT * FROM `articles` WHERE `id`=:id';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':id', $articleId);
        $stmt->execute();
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($article)) {
            throw new Exception('文章不存在', ErrorCode::ARTICLE_NOT_EXISTS);
        }
        return $article;
    }

    public function getList($userId, $page=1, $size=10)
    {
        if($size > 100 ){
            throw new Exception('分页大小最大为100', ErrorCode::PAGE_SIZE_TO_BIG);
        }
        $sql = 'SELECT * FROM `articles` WHERE `user_id`=:user_id LIMIT :limit,:offset';
        $limit = ($page - 1) * $size;
        $limit = $limit < 0 ? 0 : $limit;
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':limit', $page);
        $stmt->bindParam(':offset', $size);
        $stmt->execute();
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $articles;
    }
}