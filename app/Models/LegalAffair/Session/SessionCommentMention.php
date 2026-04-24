<?php

namespace App\Models\LegalAffair\Session;


use App\Models\User;
use App\Tenancy\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionCommentMention extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['session_comment_id', 'mentioner_user_id', 'mentioned_user_id'];

    public function sessionComment()
    {
        return $this->belongsTo(SessionComment::class, 'session_comment_id');
    }

    public function mentioner()
    {
        return $this->belongsTo(User::class, 'mentioner_user_id');
    }

    public function mentionedUser()
    {
        return $this->belongsTo(User::class, 'mentioned_user_id');
    }
}
