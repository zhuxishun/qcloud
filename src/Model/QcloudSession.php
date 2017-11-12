<?php
namespace  Qcloud\Model;
use Illuminate\Database\Eloquent\Model;

class QcloudSession extends  Model
{
    public $primaryKey = 'id';
    public $timestamps = true;
    public $fillable = ['openid','skey','session_key','uuid','userinfo'];
}