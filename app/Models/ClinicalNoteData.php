<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicalNoteData extends Model
{
    use HasFactory;

    protected $table = 'clinical_note_data';

    protected $fillable = [
        'clinical_note_id',
        'template_field_id',
        'value',
    ];
}
