<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;  // Add this line
use Illuminate\Database\Eloquent\Model;

class Disciplinary extends Model {
    use HasFactory;  // Ensure this trait is now properly imported

    protected $fillable = ['employee_id', 'title', 'description', 'status'];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }
}
