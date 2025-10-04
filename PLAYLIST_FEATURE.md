# Playlist Feature Documentation

## Overview
The playlist feature allows users to organize their dishes into custom playlists, similar to music playlists. This feature follows SOLID principles and clean code practices.

## Architecture

### Models
- **`Playlist`** (`app/Models/Playlist.php`)
  - Represents a playlist entity
  - Relationships: belongs to User, has many Plats (through contain table)
  
- **`Plats`** (updated)
  - Added relationship to Playlists (many-to-many)

### Service Layer
- **`PlaylistService`** (`app/Services/PlaylistService.php`)
  - Encapsulates all business logic for playlist operations
  - Follows Single Responsibility Principle
  - Methods:
    - `getUserPlaylists()` - Get all playlists for a user
    - `getPlaylistWithDishes()` - Get playlist with its dishes
    - `createPlaylist()` - Create a new playlist
    - `updatePlaylist()` - Update playlist name
    - `deletePlaylist()` - Delete a playlist
    - `addDishToPlaylist()` - Add a dish to a playlist
    - `removeDishFromPlaylist()` - Remove a dish from a playlist
    - `getAllDishesWithPlaylists()` - Get all dishes with playlist info
    - `isDishInPlaylist()` - Check if dish is in playlist

### Controller
- **`PlaylistController`** (`app/Http/Controllers/PlaylistController.php`)
  - Uses dependency injection for PlaylistService
  - Handles HTTP requests and responses
  - Routes:
    - `GET /playlists` - List all playlists
    - `GET /playlists/{id}` - View specific playlist
    - `POST /playlists/create` - Create new playlist
    - `PUT /playlists/update/{id}` - Update playlist
    - `DELETE /playlists/delete/{id}` - Delete playlist
    - `POST /playlists/{id}/add-dish` - Add dish to playlist
    - `DELETE /playlists/{id}/remove-dish/{dish_id}` - Remove dish from playlist
    - `GET /all-dishes` - View all dishes with playlist options
    - `GET /api/playlists` - API endpoint for AJAX calls

### Views
- **`playlists/index.blade.php`** - Manage playlists (create, edit, delete)
- **`playlists/show.blade.php`** - View dishes in a specific playlist
- **`playlists/all-dishes.blade.php`** - Browse all dishes and add to playlists

### Database
- **`playlist`** table
  - `id_playlist` (primary key)
  - `name` (varchar 50)
  - `id_utilisateur` (foreign key to utilisateurs)

- **`contain`** table (pivot table)
  - `id_plat` (foreign key to plats)
  - `id_playlist` (foreign key to playlist)
  - Composite primary key: (id_plat, id_playlist)

## Features

### 1. Create Playlists
Users can create custom playlists with unique names to organize their dishes.

### 2. Add Dishes to Playlists
- **During dish creation**: Checkbox option to add new dish to a playlist
- **From "All Dishes" page**: Browse all dishes and add them to any playlist
- **Validation**: Prevents duplicate entries

### 3. View Playlists
- List all playlists with dish count
- View individual playlist with all contained dishes
- Navigate between playlists easily

### 4. Manage Playlists
- Edit playlist names
- Delete playlists (removes associations, not dishes)
- Remove dishes from playlists

### 5. Browse All Dishes
- New "All Dishes" tab in navigation
- View all dishes in the database
- Add any dish to any playlist
- View dish details (for creators/admins)

## Navigation Updates
Added two new navigation items:
- **Playlists** - Manage your playlists
- **Tous les plats** - Browse all dishes

## SOLID Principles Applied

### Single Responsibility Principle (SRP)
- `PlaylistService` handles only business logic
- `PlaylistController` handles only HTTP concerns
- Models handle only data representation

### Open/Closed Principle (OCP)
- Service layer can be extended without modifying existing code
- New playlist features can be added by extending the service

### Liskov Substitution Principle (LSP)
- Models extend Eloquent Model properly
- Service can be mocked/replaced for testing

### Interface Segregation Principle (ISP)
- Controller methods are focused and specific
- No client forced to depend on unused methods

### Dependency Inversion Principle (DIP)
- Controller depends on PlaylistService abstraction
- Uses dependency injection for loose coupling

## Clean Code Practices

1. **Meaningful Names**: Clear, descriptive method and variable names
2. **Small Functions**: Each method does one thing well
3. **No Code Duplication**: Reusable service methods
4. **Comments**: PHPDoc comments for all public methods
5. **Error Handling**: Proper validation and error messages
6. **Consistent Formatting**: Follows Laravel conventions

## Setup Instructions

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. The feature is ready to use - no additional configuration needed

3. Access via navigation:
   - Click "Playlists" to manage playlists
   - Click "Tous les plats" to browse and add dishes

## API Endpoints

### GET /api/playlists
Returns JSON array of user's playlists for AJAX calls.

**Response:**
```json
[
  {
    "id_playlist": 1,
    "name": "Favorites",
    "id_utilisateur": 1
  }
]
```

## Security
- All operations verify user ownership
- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- Authorization checks in place

## Future Enhancements
- Share playlists with other users
- Playlist categories/tags
- Duplicate playlist functionality
- Export playlist to PDF/shopping list
- Playlist statistics and analytics
