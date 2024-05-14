<?php

namespace App\Http\Controllers;

use App\Models\Pending;
use Illuminate\Http\Request;

class PendingController extends Controller
{
    public function index(int $id){
        $personalPendings = Pending::where('id_assigned_to', $id)
                                    ->where('status', 'uncompleted')
                                    ->get();
        $pendingsCount = $personalPendings->count();
        
        $pendingsToday = Pending::where('date_to_complete', date('Y-m-d'))
                                    ->where('id_assigned_to', $id)
                                    ->where('status', 'uncompleted')
                                    ->get();
                                        
        $pendingsTodayCount = $pendingsToday->count();
        
        $pendingToWeek = Pending::where('date_to_complete', '>=', date('Y-m-d', strtotime('+1 days')))
                                    ->where('date_to_complete', '<=', date('Y-m-d', strtotime('+7 days')))
                                    ->where('id_assigned_to', $id)
                                    ->where('status', 'uncompleted')
                                    ->get();
                                    
        $pendingsToWeekCount = $pendingToWeek->count();
        
        $pendingsUrgent = Pending::where('is_urgent', 1)
                                    ->where('status', 'uncompleted')
                                    ->get();
        $pendingsUrgentCount = $pendingsUrgent->count();
        
        return response()->json([
            'test' =>  date('Y-m-d'),
            'personalPendings' =>[
                'pendings' => $personalPendings,
                'count' => $pendingsCount
            ],
            'pendingsToday' =>[
                'pendings' => $pendingsToday,
                'count' => $pendingsTodayCount
            ],
            'pendingToWeek' =>[
                'pendings' => $pendingToWeek,
                'count' => $pendingsToWeekCount
            ],
            'pendingsUrgent' =>[
                'pendings' => $pendingsUrgent,
                'count' => $pendingsUrgentCount
            ],
        ]);
    }
    
    function changeStatus(int $id){}
}
