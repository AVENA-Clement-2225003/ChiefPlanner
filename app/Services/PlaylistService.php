<?php

namespace App\Services;

use App\Models\Playlist;
use App\Models\Plats;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class for playlist business logic
 * Follows Single Responsibility Principle
 */
class PlaylistService
{
    /**
     * Get all playlists for a specific user
     */
    public function getUserPlaylists(int $userId): Collection
    {
        return Playlist::where('id_utilisateur', $userId)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get a playlist with its dishes
     */
    public function getPlaylistWithDishes(int $playlistId, int $userId): ?Playlist
    {
        return Playlist::where('id_playlist', $playlistId)
            ->where('id_utilisateur', $userId)
            ->with('plats')
            ->first();
    }

    /**
     * Create a new playlist
     */
    public function createPlaylist(string $name, int $userId): Playlist
    {
        return Playlist::create([
            'name' => $name,
            'id_utilisateur' => $userId,
        ]);
    }

    /**
     * Update a playlist name
     */
    public function updatePlaylist(int $playlistId, string $name, int $userId): bool
    {
        $playlist = Playlist::where('id_playlist', $playlistId)
            ->where('id_utilisateur', $userId)
            ->first();

        if (!$playlist) {
            return false;
        }

        $playlist->name = $name;
        return $playlist->save();
    }

    /**
     * Delete a playlist
     */
    public function deletePlaylist(int $playlistId, int $userId): bool
    {
        $playlist = Playlist::where('id_playlist', $playlistId)
            ->where('id_utilisateur', $userId)
            ->first();

        if (!$playlist) {
            return false;
        }

        // Detach all dishes first
        $playlist->plats()->detach();
        return $playlist->delete();
    }

    /**
     * Add a dish to a playlist
     */
    public function addDishToPlaylist(int $playlistId, int $dishId, int $userId): bool
    {
        $playlist = Playlist::where('id_playlist', $playlistId)
            ->where('id_utilisateur', $userId)
            ->first();

        if (!$playlist) {
            return false;
        }

        // Check if dish is already in playlist
        if ($playlist->plats()->where('plats.id_plat', $dishId)->exists()) {
            return false;
        }

        $playlist->plats()->attach($dishId);
        return true;
    }

    /**
     * Remove a dish from a playlist
     */
    public function removeDishFromPlaylist(int $playlistId, int $dishId, int $userId): bool
    {
        $playlist = Playlist::where('id_playlist', $playlistId)
            ->where('id_utilisateur', $userId)
            ->first();

        if (!$playlist) {
            return false;
        }

        $playlist->plats()->detach($dishId);
        return true;
    }

    /**
     * Get all dishes with their playlist associations for a user
     */
    public function getAllDishesWithPlaylists(int $userId): Collection
    {
        return Plats::with(['playlists' => function ($query) use ($userId) {
            $query->where('id_utilisateur', $userId);
        }])
        ->orderBy('nom', 'asc')
        ->get();
    }

    /**
     * Check if a dish belongs to a playlist
     */
    public function isDishInPlaylist(int $playlistId, int $dishId): bool
    {
        $playlist = Playlist::find($playlistId);
        if (!$playlist) {
            return false;
        }

        return $playlist->plats()->where('id_plat', $dishId)->exists();
    }
}
