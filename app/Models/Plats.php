<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plats extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $primaryKey = 'id_plat';
    protected $fillable = [
        'nom',
        'id_utilisateur',
    ];

    /**
     * Get the playlists that contain this dish
     */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(
            Playlist::class,
            'contain',
            'id_plat',
            'id_playlist',
            'id_plat',
            'id_playlist'
        );
    }
}
