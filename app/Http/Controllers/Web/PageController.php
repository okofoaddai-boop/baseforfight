<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;

class PageController extends Controller
{
    public function show(string $page): View
    {
        $allowedPages = ['explained', 'pricing', 'privacy', 'imprint'];

        abort_unless(in_array($page, $allowedPages, true), 404);

        $pageData = trans('pages.pages.' . $page);

        abort_unless(is_array($pageData) && count($pageData) > 0, 404);

        return view('pages.show', [
            'pageKey' => $page,
            'page' => $pageData,
            'sections' => Arr::get($pageData, 'sections', []),
        ]);
    }
}