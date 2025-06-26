<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDocument extends Model
{
    protected $fillable = [
        'student_id',
        'filenames',
        'type'
    ];

    protected $casts = [
        'filenames' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getFilenamesAttribute($value)
{
    $filenames = json_decode($value, true);
    $student_id_encoded = base64_encode($this->student_id);
    $business_name = optional($this->student->business)->name ?? 'unknown';
    $prefix = "/" . str_replace(' ', '_', $business_name) . "/" . $student_id_encoded . "/student_docs/";

    return array_map(function ($filename) use ($prefix) {
        return [
            "original_file_name" => $filename,
            "file_name" => $prefix . $filename
        ];
    }, $filenames ?? []);
}

}
