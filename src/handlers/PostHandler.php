<?php
namespace src\handlers;

use \src\models\Post;
use \src\models\UserRelation;
use \src\models\User;

class PostHandler {
    public static function addPost($idUser, $type, $body) {
        $body = trim($body);
        if(!empty($idUser) && !empty($body)){
            Post::insert([
                'user_id'   => $idUser,
                'type'      => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'body'      => $body,
            ])->execute();
        }
    }

    public static function getHomeFeed($id, $page) {
        $perPage = 2;
        $userList = UserRelation::select()
                                  ->where('user_from', $id)
                                  ->get();
        $users = [];
        foreach($userList as $userItem) {
            $users[] = $userList['user_to'];
        }
        $users[] = $id;

        $postList = Post::select()
                       ->where('user_id', 'in',  $users)
                       ->orderBy('created_at', 'desc')
                       ->page($page, 2)
                       ->get();

        $total = Post::select()
                       ->where('user_id', 'in',  $users)
                       ->count();
        $pageCount = ceil($total / $perPage);
        $posts = [];

        foreach($postList as $postItem) {
            $newPost = new Post();
            $newPost->id = $postItem['id'];
            $newPost->type = $postItem['type'];
            $newPost->created_at = $postItem['created_at'];
            $newPost->body = $postItem['body'];
            $newPost->mine = false;

            if($postItem['user_id'] == $id) {
                $newPost->mine = true;
            }

            $newUser = User::select()->where('id', $postItem['user_id'])->one();
            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];

            //TODO likes
            $newPost->likeCount = 0;
            $newPost->liked = false;
            //TODO comments
            $newPost->comments = [];

            $posts[]  = $newPost;
        }
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            'currentPage' => $page
        ];
    }
}