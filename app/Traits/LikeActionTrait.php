<?php

namespace App\Traits;


trait LikeActionTrait {
    /**
     * Gets the like action performed on a likeable model
     *
     * -1   = disliked
     * 0    = neutral (no vote)
     * 1    = liked
     *
     * @param $likeableModel
     * @return int
     */
    public function likeAction($likeableModel) {
        $action = ($this->hasLiked($likeableModel)) ? 1 : 0;

        if($action == 0)
            $action = ($this->hasDisliked($likeableModel)) ? -1 : 0;

        return $action;
    }
}