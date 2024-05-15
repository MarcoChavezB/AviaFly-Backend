<?php

namespace App\Http\Controllers;

use App\Models\Pending;
use App\Models\User;
use Illuminate\Http\Request;

class PendingController extends Controller
{
    function index(int $id)
    {
        $userBaseId = User::find($id)->id_base;
        
        $personalPendings = Pending::where('id_created_by', $id)
            ->where('id_assigned_to', $id)
            ->whereHas('createdBy', function ($query) use ($userBaseId) {
                $query->where('id_base', $userBaseId);
            })
            ->where('status', FALSE)
            ->get();
        $pendingsCount = $personalPendings->count();


        $pendingsToday = Pending::where('date_to_complete', date('Y-m-d'))
            ->where('id_assigned_to', $id)
            ->where('status', FALSE)
            ->whereHas('createdBy', function ($query) use ($userBaseId) {
                $query->where('id_base', $userBaseId);
            })
            ->get();
        $pendingsTodayCount = $pendingsToday->count();

        $pendingToWeek = Pending::where('date_to_complete', '>=', date('Y-m-d', strtotime('+1 days')))
            ->where('date_to_complete', '<=', date('Y-m-d', strtotime('+7 days')))
            ->where('id_assigned_to', $id)
            ->where('status', FALSE)
            ->whereHas('createdBy', function ($query) use ($userBaseId) {
                $query->where('id_base', $userBaseId);
            })
            ->get();

        $pendingsToWeekCount = $pendingToWeek->count();

        $pendingsUrgent = Pending::where('is_urgent', 1)
            ->where('status', FALSE)
            ->whereHas('createdBy', function ($query) use ($userBaseId) {
                $query->where('id_base', $userBaseId);
            })
            ->get();
        $pendingsUrgentCount = $pendingsUrgent->count();

        return response()->json([
            'personalPendings' => [
                'pendings' => $personalPendings,
                'count' => $pendingsCount
            ],
            'pendingsToday' => [
                'pendings' => $pendingsToday,
                'count' => $pendingsTodayCount
            ],
            'pendingToWeek' => [
                'pendings' => $pendingToWeek,
                'count' => $pendingsToWeekCount
            ],
            'pendingsUrgent' => [
                'pendings' => $pendingsUrgent,
                'count' => $pendingsUrgentCount
            ],
        ]);
    }

    function changeStatus(int $id)
    {
    }
}
