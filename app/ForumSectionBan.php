<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ForumSectionBan extends Model
{
    // Table name
    const TABLE_NAME = 'forum_section_ban';
    protected $table = self::TABLE_NAME;

    /**
     * Retrieves the info for a user's section ban
     *
     * @param $userID
     * @param $sectionID
     * @return array|null
     */
    public static function getBanInfo($userID, $sectionID) {
        // Find the section ban
        $foundBan = ForumSectionBan::where([
            ['user_id',     '=', $userID],
            ['section_id',  '=', $sectionID]
        ])->first();

        // Not banned
        if(!$foundBan)
            return null;

        // Format the ban date
        $banDate = '(date unknown)';

        if($foundBan->created_at != null)
            $banDate = (new Carbon($foundBan->created_at))->format('d-m-Y');

        // Format ban reason
        $banReason = (strlen($foundBan->reason)) ? $foundBan->reason : 'No reason specified';

        // Format the ban message
        $banMessage =
            'You have been banned from posting in this forum section on ' . $banDate . '. ' .
            'Reason: ' . $banReason
        ;

        // Return the ban info
        return [
            'section_ban'   => $foundBan,
            'message'       => $banMessage
        ];
    }
}
