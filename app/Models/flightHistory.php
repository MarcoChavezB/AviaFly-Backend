<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class flightHistory extends Model
{
    use HasFactory;
    protected $table = 'flight_history';
    protected $fillable = [
        'hours',
        'type_flight',
        'flight_date',
        'maneuver',
        'equipo',
        'flight_status',
        'flight_category',
        'flight_hour',
        'flight_alone',
        'initial_horometer',
        'final_horometer',
        'total_horometer',
        'final_tacometer',
        'comment',
        'create_at',
        'update_at'
    ];

    public static function getEnumValues($column)
    {
        $type = DB::select(DB::raw("SHOW COLUMNS FROM " . with(new static)->getTable() . " WHERE Field = '{$column}'"))[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = array();
        foreach (explode(',', $matches[1]) as $value) {
            $enum[] = trim($value, "'");
        }
        return $enum;
    }
}
