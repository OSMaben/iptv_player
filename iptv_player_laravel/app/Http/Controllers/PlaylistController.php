<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Playlist;
use Illuminate\Support\Facades\File;



class PlaylistController extends Controller
{
    public function uploadPlaylist(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'playlist' => 'required|file',
            ]);

            $playlist = new Playlist();
            $playlist->file_name = $request->name;
            $playlist->file_path = $request->file('playlist')->store('playlists', 'public'); // Save to public folder
            $playlist->user_identifier = $request->ip(); // Use IP as identifier
            $playlist->save();

            return response()->json([
                'message' => 'Playlist uploaded successfully!',
                'redirect_url' => url('/dashboard') // Redirect to the dashboard
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while uploading the playlist. Please try again.'], 500);
        }
    }


   
    public function getM3UContentByPlaylist($id)
{
    $userIp = request()->ip();
    $playlist = Playlist::where('user_identifier', $userIp)
                        ->where('id', $id)
                        ->first();

    if (!$playlist) {
        return response()->json(['error' => 'Playlist not found.'], 404);
    }

    $filePath = public_path('storage/' . $playlist->file_path);

    if (!File::exists($filePath)) {
        return response()->json(['error' => 'File not found.', 'file_path' => $filePath], 404);
    }

    $content = File::get($filePath);
    $lines = explode("\n", $content);

    $channels = [];
    $currentName = '';
    $currentLogo = '';
    $currentUrl = '';
    $currentGroup = 'Other';

    foreach ($lines as $line) {
        $line = trim($line);

        if (strpos($line, '#EXTINF:') === 0) {
            // Extract channel name, logo, and group-title
            $parts = explode(',', $line);
            $currentName = isset($parts[1]) ? trim($parts[1]) : '';

            // Extract group-title
            if (strpos($line, 'group-title=') !== false) {
                preg_match('/group-title="([^"]+)"/', $line, $matches);
                $currentGroup = isset($matches[1]) ? trim($matches[1]) : 'Other';
            }

            // Extract logo URL
            if (strpos($line, 'tvg-logo=') !== false) {
                preg_match('/tvg-logo="([^"]+)"/', $line, $matches);
                $currentLogo = isset($matches[1]) ? trim($matches[1]) : '';
            }

        } elseif (filter_var($line, FILTER_VALIDATE_URL)) {
            if ($currentName) {
                $channels[] = [
                    'name' => $currentName,
                    'logo' => $currentLogo,
                    'url' => $line,
                    'group_title' => $currentGroup,
                ];
                $currentName = ''; // Reset for the next channel
                $currentLogo = ''; // Reset for the next channel
            }
        }
    }

    // Group channels by group-title
    $groupedChannels = [];
    foreach ($channels as $channel) {
        $groupTitle = $channel['group_title'];
        if (!isset($groupedChannels[$groupTitle])) {
            $groupedChannels[$groupTitle] = [];
        }
        $groupedChannels[$groupTitle][] = $channel;
    }

    return response()->json($groupedChannels);
}





    public function getPlaylists(Request $request)
    {
        $playlists = Playlist::where('user_identifier', $request->ip())->get();

        return response()->json($playlists);
    }
    
}
