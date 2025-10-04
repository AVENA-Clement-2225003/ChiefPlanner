<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Playlist extends Model
{
    use HasFactory;

    protected $table = 'playlist';
    public $timestamps = false;
    protected $primaryKey = 'id_playlist';

    protected $fillable = [
        'name',
        'id_utilisateur',
    ];

    /**
     * Get the user that owns the playlist
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id_utilisateur');
    }

    /**
     * Get the dishes in this playlist
     */
    public function plats(): BelongsToMany
    {
        return $this->belongsToMany(
            Plats::class,
            'contain',
            'id_playlist',
            'id_plat',
            'id_playlist',
            'id_plat'
        );
    }
}
