<?php
namespace App\Models\LegalAffair\Lawsuit;


use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawsuitAttachment extends Model
{
    use HasFactory, BelongsToTenant;
    protected $fillable = ['lawsuit_id', 'attachment_name', 'file_path'];

}
