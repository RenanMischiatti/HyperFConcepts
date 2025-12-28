<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class UserInfo extends Model
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'users_info';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'user_id',
        'phone',
        'address',
        'birthdate',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'birthdate' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
