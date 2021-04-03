<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class CollectionLikeChecker {
    protected $collectionType;
    protected $collection;
    protected $userID;
    protected $queryData;

    /**
     * Instantiates a CollectionLikeChecker and fetches the data.
     *
     * @param $userID
     * @param $collection
     * @return CollectionLikeChecker
     */
    static function retrieve($userID, $collection) {
        // Get the type of collection
        $collectionType = $collection->getQueueableClass();

        // Create the instance
        $instance = new CollectionLikeChecker();
        $instance->collectionType = $collectionType;
        $instance->collection = $collection;
        $instance->userID = $userID;

        // Fetch the data for the instance
        $instance->fetchData();

        // Return the instance
        return $instance;
    }

    /**
     * Returns an array of IDs of the items in the collection.
     *
     * @return array
     */
    protected function getCollectionIDs() {
        $IDs = [];

        foreach($this->collection as $item)
            $IDs[] = $item->id;

        return $IDs;
    }

    /**
     * Fetches the data by making a query and stores it in the instance.
     */
    protected function fetchData() {
        $this->queryData = DB::table('love_likes')
            ->where('user_id', $this->userID)
            ->where('likeable_type', $this->collectionType)
            ->whereIn('likeable_id', $this->getCollectionIDs())
            ->get();
    }

    /**
     * Checks if an item has been liked.
     *
     * @param $item
     * @return bool
     */
    function hasLiked($item) {
        foreach($this->queryData as $queryItem)
            if($queryItem->likeable_id == $item->id && $queryItem->type_id == 'LIKE')
                return true;

        return false;
    }

    /**
     * Checks if an item has been disliked.
     *
     * @param $item
     * @return bool
     */
    function hasDisliked($item) {
        foreach($this->queryData as $queryItem)
            if($queryItem->likeable_id == $item->id && $queryItem->type_id == 'DISLIKE')
                return true;

        return false;
    }

    /**
     * Returns the current like action for an item.
     * -1   = disliked
     * 0    = neutral
     * 1    = liked
     *
     * @param $item
     * @return int
     */
    function getCurrentLikeAction($item) {
        if($this->hasLiked($item))
            return 1;
        else if($this->hasDisliked($item))
            return -1;
        else
            return 0;
    }
}
