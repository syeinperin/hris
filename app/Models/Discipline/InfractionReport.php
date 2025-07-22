<?php

namespace App\Models\Discipline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Employee;
use App\Models\User;

class InfractionReport extends Model
{
    use HasFactory;

    // (optional) if your table name isn’t the plural of the model
    // protected $table = 'infraction_reports';

    protected $fillable = [
        'employee_id',
        'reported_by',
        'location',
        'description',
        'incident_date',
        'incident_time',
        'similar_before',
        'similar_count',
        'confidential',
        'will_testify',
    ];

    /**
     * Cast `incident_date` to a Carbon date instance
     * so you can call ->toDateString() in your notification.
     */
    protected $casts = [
        'incident_date'  => 'date',
        'incident_time'  => 'datetime:H:i',
        'similar_before' => 'boolean',
        'confidential'   => 'boolean',
        'will_testify'   => 'boolean',
    ];

    /**
     * The employee who is the subject of this report.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * The user who filed this report.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Any investigators attached to this report.
     */
    public function investigators()
    {
        return $this->hasMany(InfractionInvestigator::class, 'infraction_report_id');
    }

    /**
     * Any disciplinary actions taken on this report.
     */
    public function actions()
    {
        return $this->hasMany(DisciplinaryAction::class, 'infraction_report_id');
    }
}
