# Playlist Feature - Deployment Checklist

## Pre-Deployment Checks

### 1. Database Setup
- [ ] Backup current database
- [ ] Review migration files
- [ ] Test migrations in development environment
- [ ] Run migrations: `php artisan migrate`
- [ ] Verify tables created: `playlist` and `contain`
- [ ] Check foreign key constraints are working

### 2. Code Review
- [ ] All new files are in version control
- [ ] No syntax errors in PHP files
- [ ] No console errors in JavaScript
- [ ] All routes are registered correctly
- [ ] Service class is properly namespaced

### 3. Dependencies
- [ ] No new Composer packages required ✅
- [ ] No new NPM packages required ✅
- [ ] Laravel version compatible ✅

### 4. File Permissions
- [ ] Check write permissions on storage/logs
- [ ] Check cache directory permissions
- [ ] Verify view cache can be cleared

## Deployment Steps

### Step 1: Backup
```bash
# Backup database
php artisan db:backup  # Or your backup method

# Backup files
# Create a backup of your current application
```

### Step 2: Pull Changes
```bash
# If using Git
git pull origin main  # Or your branch name

# Or manually copy files to server
```

### Step 3: Run Migrations
```bash
php artisan migrate

# If you need to rollback
# php artisan migrate:rollback --step=2
```

### Step 4: Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 5: Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Set Permissions (if needed)
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache

# Set owner (adjust as needed)
chown -R www-data:www-data storage bootstrap/cache
```

## Post-Deployment Testing

### Functional Tests
- [ ] Log in as a regular user
- [ ] Create a new playlist
- [ ] Edit playlist name
- [ ] Delete a playlist
- [ ] Navigate to "Tous les plats" page
- [ ] Add a dish to a playlist from "Tous les plats"
- [ ] View a playlist with dishes
- [ ] Remove a dish from a playlist
- [ ] Create a new dish with playlist selection (as Creator)
- [ ] Verify dish is added to selected playlist

### UI/UX Tests
- [ ] Navigation links work correctly
- [ ] Modals open and close properly
- [ ] Forms submit correctly
- [ ] Success/error messages display
- [ ] Tables display correctly
- [ ] Buttons have correct styling
- [ ] Responsive design works on mobile

### Security Tests
- [ ] User can only see their own playlists
- [ ] User cannot access other users' playlists
- [ ] CSRF tokens are present on forms
- [ ] SQL injection attempts are blocked
- [ ] XSS attempts are blocked

### Performance Tests
- [ ] Pages load in reasonable time
- [ ] AJAX calls respond quickly
- [ ] No N+1 query problems
- [ ] Database indexes are used

### Browser Compatibility
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile browsers

## Rollback Plan

If issues occur, rollback in this order:

### 1. Rollback Migrations
```bash
php artisan migrate:rollback --step=2
```

### 2. Restore Code
```bash
# If using Git
git revert HEAD

# Or restore from backup
```

### 3. Clear Caches Again
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 4. Verify Application Works
- [ ] Test basic functionality
- [ ] Check error logs
- [ ] Verify database integrity

## Monitoring

### First 24 Hours
- [ ] Monitor error logs: `storage/logs/laravel.log`
- [ ] Check server resource usage
- [ ] Monitor database performance
- [ ] Watch for user-reported issues

### First Week
- [ ] Review usage statistics
- [ ] Collect user feedback
- [ ] Check for any edge cases
- [ ] Monitor database growth

## Files Changed/Added

### New Files (10)
1. `app/Models/Playlist.php`
2. `app/Services/PlaylistService.php`
3. `app/Http/Controllers/PlaylistController.php`
4. `resources/views/playlists/index.blade.php`
5. `resources/views/playlists/show.blade.php`
6. `resources/views/playlists/all-dishes.blade.php`
7. `database/migrations/2025_10_04_000001_create_playlist_table.php`
8. `database/migrations/2025_10_04_000002_create_contain_table.php`
9. `PLAYLIST_FEATURE.md`
10. `IMPLEMENTATION_SUMMARY.md`

### Modified Files (5)
1. `app/Models/Plats.php`
2. `app/Http/Controllers/PlatsController.php`
3. `routes/web.php`
4. `resources/views/layout.blade.php`
5. `resources/views/plats.blade.php`

## Environment Variables

No new environment variables required ✅

## Database Changes

### New Tables
- `playlist` (3 columns)
- `contain` (2 columns, composite PK)

### Modified Tables
None - only new relationships added in code

## Known Limitations

1. Playlists are private (no sharing between users)
2. No playlist export functionality yet
3. No bulk operations (add multiple dishes at once)
4. No playlist duplication feature
5. No playlist search/filter on large lists

## Support Information

### Common Issues

**Issue:** Migrations fail
**Solution:** Check database connection, verify table names don't conflict

**Issue:** 404 on playlist routes
**Solution:** Run `php artisan route:clear` and `php artisan route:cache`

**Issue:** Playlists not loading
**Solution:** Check browser console for JavaScript errors, verify API endpoint

**Issue:** Can't add dishes to playlist
**Solution:** Verify user is logged in, check session data

### Log Locations
- Application logs: `storage/logs/laravel.log`
- Web server logs: Check your server configuration
- Database logs: Check your database server configuration

### Debug Mode
```php
// .env file
APP_DEBUG=true  // Only for development!
```

## Success Criteria

Deployment is successful when:
- ✅ All migrations run without errors
- ✅ Users can create and manage playlists
- ✅ Dishes can be added to playlists
- ✅ No errors in logs
- ✅ All tests pass
- ✅ UI is responsive and functional
- ✅ Security checks pass

## Sign-Off

- [ ] Developer tested
- [ ] Code reviewed
- [ ] Database migrations verified
- [ ] Documentation complete
- [ ] Deployment plan approved
- [ ] Rollback plan tested
- [ ] Monitoring in place

---

**Deployment Date:** _________________

**Deployed By:** _________________

**Verified By:** _________________

**Notes:**
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
