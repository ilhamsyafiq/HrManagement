<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Show the bug report / feedback form.
     */
    public function create(): View
    {
        return view('feedback.create');
    }

    /**
     * Store a submitted bug report / feedback.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:bug,feedback'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'page_url' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('feedback', 'public');
        }

        BugReport::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'page_url' => $validated['page_url'] ?? null,
            'image_path' => $imagePath,
            'status' => 'open',
        ]);

        return redirect()->back()->with('success', 'Thank you — your report was submitted.');
    }
}
