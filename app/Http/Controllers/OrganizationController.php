<?php
namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function show(Request $request): Response
    {
        $exists = Organization::query()->where('id', $request->id)
            ->where(['user_id' => Auth::id()])
            ->exists();

        if($exists){
            return Inertia::render('Organization',[
                'organization' => Organization::query()->where('id', $request->id)
                    ->where(['user_id' => Auth::id()])
                    ->first(),
                'reviews' => Review::query()->where('organization_id', $request->id)->orderBy('review_date', 'desc')->paginate(50)
            ]);
        }else {
            return Inertia::render('Dashboard');
        }

    }
}
