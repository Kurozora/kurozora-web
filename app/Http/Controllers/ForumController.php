<?php

namespace App\Http\Controllers;

use App\ForumSection;
use App\Helpers\JSONResult;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    /**
     * Returns the forum sections
     */
    public function getSections() {
        $rawSections = ForumSection::all();

        $sections = [];

        foreach($rawSections as $rawSection)
            $sections[] = $rawSection->formatForResponse();

        (new JSONResult())->setData(['sections' => $sections])->show();
    }
}
