<?php

namespace App;

class UserFollow extends KModel
{
    // Table name
    const TABLE_NAME = 'user_follows';
    protected $table = self::TABLE_NAME;

    // Amount of results to display per page
    const AMOUNT_OF_FOLLOWERS_PER_PAGE = 25;
}
