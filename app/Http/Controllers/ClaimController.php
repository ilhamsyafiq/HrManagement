<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClaimController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $query = Claim::with('user')->orderBy('created_at', 'desc');

            if (request('status')) {
                $query->where('status', request('status'));
            }

            $claims = $query->paginate(20)->withQueryString();

            return view('claims.admin-index', compact('claims'));
        }

        $claims = Claim::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('claims.index', compact('claims'));
    }

    public function create()
    {
        return view('claims.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.expense_date' => 'required|date',
            'items.*.category' => 'required|in:Transport,Meal,Accommodation,Office Supplies,Medical,Training,Other',
            'items.*.notes' => 'nullable|string',
            'items.*.receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        $claim = Claim::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'total_amount' => 0,
            'status' => 'Draft',
        ]);

        foreach ($request->items as $index => $itemData) {
            $receiptPath = null;

            if ($request->hasFile("items.{$index}.receipt")) {
                $receiptPath = $request->file("items.{$index}.receipt")->store('claims', 'public');
            }

            ClaimItem::create([
                'claim_id' => $claim->id,
                'description' => $itemData['description'],
                'amount' => $itemData['amount'],
                'expense_date' => $itemData['expense_date'],
                'category' => $itemData['category'],
                'receipt_path' => $receiptPath,
                'notes' => $itemData['notes'] ?? null,
            ]);
        }

        $claim->recalculateTotal();

        return redirect()->route('claims.show', $claim)->with('success', 'Claim created successfully.');
    }

    public function show(Claim $claim)
    {
        $user = Auth::user();

        if ($claim->user_id !== $user->id && !$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        $claim->load(['items', 'user', 'approver']);

        return view('claims.show', compact('claim'));
    }

    public function submit(Claim $claim)
    {
        $user = Auth::user();

        if ($claim->user_id !== $user->id) {
            abort(403);
        }

        if ($claim->status !== 'Draft') {
            return redirect()->back()->with('error', 'Only draft claims can be submitted.');
        }

        if ($claim->items()->count() === 0) {
            return redirect()->back()->with('error', 'Cannot submit a claim with no items.');
        }

        $claim->update(['status' => 'Pending']);

        return redirect()->back()->with('success', 'Claim submitted for approval.');
    }

    public function addItem(Request $request, Claim $claim)
    {
        $user = Auth::user();

        if ($claim->user_id !== $user->id) {
            abort(403);
        }

        if ($claim->status !== 'Draft') {
            return redirect()->back()->with('error', 'Items can only be added to draft claims.');
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'category' => 'required|in:Transport,Meal,Accommodation,Office Supplies,Medical,Training,Other',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $receiptPath = null;

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('claims', 'public');
        }

        ClaimItem::create([
            'claim_id' => $claim->id,
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'category' => $request->category,
            'receipt_path' => $receiptPath,
            'notes' => $request->notes,
        ]);

        $claim->recalculateTotal();

        return redirect()->back()->with('success', 'Item added successfully.');
    }

    public function removeItem(ClaimItem $item)
    {
        $user = Auth::user();
        $claim = $item->claim;

        if ($claim->user_id !== $user->id) {
            abort(403);
        }

        if ($claim->status !== 'Draft') {
            return redirect()->back()->with('error', 'Items can only be removed from draft claims.');
        }

        if ($item->receipt_path) {
            Storage::disk('public')->delete($item->receipt_path);
        }

        $item->delete();
        $claim->recalculateTotal();

        return redirect()->back()->with('success', 'Item removed successfully.');
    }

    public function approve(Claim $claim)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        if ($claim->status !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending claims can be approved.');
        }

        $claim->update([
            'status' => 'Approved',
            'approved_by' => $user->id,
            'approved_at' => now('Asia/Kuala_Lumpur'),
        ]);

        return redirect()->back()->with('success', 'Claim approved successfully.');
    }

    public function reject(Request $request, Claim $claim)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        if ($claim->status !== 'Pending') {
            return redirect()->back()->with('error', 'Only pending claims can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $claim->update([
            'status' => 'Rejected',
            'approved_by' => $user->id,
            'approved_at' => now('Asia/Kuala_Lumpur'),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'Claim rejected.');
    }

    public function markPaid(Claim $claim)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403);
        }

        if ($claim->status !== 'Approved') {
            return redirect()->back()->with('error', 'Only approved claims can be marked as paid.');
        }

        $claim->update(['status' => 'Paid']);

        return redirect()->back()->with('success', 'Claim marked as paid.');
    }
}
