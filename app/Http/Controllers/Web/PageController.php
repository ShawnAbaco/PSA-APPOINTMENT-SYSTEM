<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequirement;
use Illuminate\Http\Request;
class PageController extends Controller
{
   public function landing()
{
    // Fetch all active requirements grouped by service
    $requirements = DocumentRequirement::where('is_active', true)
        ->orderBy('service')
        ->orderBy('age_group')
        ->orderBy('id')
        ->get()
        ->groupBy('service');
    
    return view('client.landing-page', compact('requirements'));
}
    

}