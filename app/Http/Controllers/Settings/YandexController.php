<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\ParseTask;
use App\Services\CreateNewYandexOrgService;
use App\Services\UpdateYandexReviewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class YandexController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(Request $request): Response
    {

        return Inertia::render('settings/Yandex',[
            'parse_tasks' => ParseTask::query()
                ->with('organization')
                ->where(['user_id' => Auth::id()])
                ->orderBy('created_at', 'asc')
                ->get(),
            //'organizations' => Organization::query()->where(['user_id' => Auth::id()])->get(),
        ]);
    }
    /**
     * Show the user's password settings page.
     */
    public function destroy(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        ParseTask::where('id', intval($request->id))
            ->where('user_id', $request->user()->id)
            ->delete();

        return back();
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): \Symfony\Component\HttpFoundation\Response
    {

        $validated = $request->validate([
            'link' => [
                'required',
                'url',
                'regex:/https?:\/\/(yandex\.(ru|kz|com)\/maps\/)/'
            ]
        ]);

        $decodedUrl = urldecode($validated['link']);

        try {

            $exists = ParseTask::where('user_id', Auth::id())
                ->where('url', $decodedUrl)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'link' => 'This link has already been added for this user.'
                ])->withInput();
            }

            $task = ParseTask::create([
                'user_id' => Auth::id(),
                'url' => $decodedUrl,
                'status' => 'pending'
            ]);


            dispatch(new CreateNewYandexOrgService($task));

            return back();

        } catch (\Exception $e) {
            return back()
                ->withErrors(['link' => 'Ошибка: ' . $e->getMessage()])
                ->withInput();
        }

    }
}
