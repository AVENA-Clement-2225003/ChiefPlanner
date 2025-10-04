<?php

namespace App\Http\Controllers;

use App\Services\PlaylistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Controller for playlist management
 * Follows Dependency Injection and Single Responsibility principles
 */
class PlaylistController extends Controller
{
    protected PlaylistService $playlistService;

    public function __construct(PlaylistService $playlistService)
    {
        $this->playlistService = $playlistService;
    }

    /**
     * Display all playlists for the current user
     */
    public function index()
    {
        $userId = Session::get('user_id');
        $playlists = $this->playlistService->getUserPlaylists($userId);
        
        return view('playlists.index', compact('playlists'));
    }

    /**
     * Display a specific playlist with its dishes
     */
    public function show(int $playlistId)
    {
        $userId = Session::get('user_id');
        $playlist = $this->playlistService->getPlaylistWithDishes($playlistId, $userId);

        if (!$playlist) {
            return redirect()->route('playlists.index')
                ->with('error', 'Playlist non trouvée');
        }

        return view('playlists.show', compact('playlist'));
    }

    /**
     * Create a new playlist
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $userId = Session::get('user_id');
        $this->playlistService->createPlaylist($request->name, $userId);

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist créée avec succès');
    }

    /**
     * Update a playlist name
     */
    public function update(Request $request, int $playlistId)
    {
        $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $userId = Session::get('user_id');
        $success = $this->playlistService->updatePlaylist($playlistId, $request->name, $userId);

        if (!$success) {
            return redirect()->route('playlists.index')
                ->with('error', 'Playlist non trouvée');
        }

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist modifiée avec succès');
    }

    /**
     * Delete a playlist
     */
    public function destroy(int $playlistId)
    {
        $userId = Session::get('user_id');
        $success = $this->playlistService->deletePlaylist($playlistId, $userId);

        if (!$success) {
            return redirect()->route('playlists.index')
                ->with('error', 'Playlist non trouvée');
        }

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist supprimée avec succès');
    }

    /**
     * Add a dish to a playlist
     */
    public function addDish(Request $request, int $playlistId)
    {
        $request->validate([
            'dish_id' => 'required|exists:plats,id_plat',
        ]);

        $userId = Session::get('user_id');
        $success = $this->playlistService->addDishToPlaylist(
            $playlistId,
            $request->dish_id,
            $userId
        );

        if (!$success) {
            return back()->with('error', 'Impossible d\'ajouter le plat à la playlist');
        }

        return back()->with('success', 'Plat ajouté à la playlist');
    }

    /**
     * Remove a dish from a playlist
     */
    public function removeDish(int $playlistId, int $dishId)
    {
        $userId = Session::get('user_id');
        $success = $this->playlistService->removeDishFromPlaylist($playlistId, $dishId, $userId);

        if (!$success) {
            return back()->with('error', 'Impossible de retirer le plat de la playlist');
        }

        return back()->with('success', 'Plat retiré de la playlist');
    }

    /**
     * Display all dishes with option to add to playlists
     */
    public function showAllDishes()
    {
        $userId = Session::get('user_id');
        $dishes = $this->playlistService->getAllDishesWithPlaylists($userId);
        $playlists = $this->playlistService->getUserPlaylists($userId);

        return view('playlists.all-dishes', compact('dishes', 'playlists'));
    }

    /**
     * API endpoint to get user playlists (for AJAX calls)
     */
    public function getUserPlaylists()
    {
        $userId = Session::get('user_id');
        $playlists = $this->playlistService->getUserPlaylists($userId);

        return response()->json($playlists);
    }
}
