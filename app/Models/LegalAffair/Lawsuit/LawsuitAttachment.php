<?php
namespace App\Models\LegalAffair\Lawsuit;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawsuitAttachment extends Model
{
    use HasFactory;
    protected $fillable = ['lawsuit_id', 'attachment_name', 'file_path'];

}
