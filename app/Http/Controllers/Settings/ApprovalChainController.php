<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApprovalChainStep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApprovalChainController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superadmin']);
    }

    /**
     * Show the approver chain and the approvers available to add to it.
     */
    public function index()
    {
        $steps = ApprovalChainStep::ordered()->with('approver')->get();

        $availableApprovers = User::where('role', 'rfq_approver')
            ->whereDoesntHave('approvalChainStep')
            ->orderBy('name')
            ->get();

        return view('settings.approval-chain.index', compact('steps', 'availableApprovers'));
    }

    /**
     * Append an RFQ approver to the end of the chain.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'rfq_approver')),
                Rule::unique('approval_chain_steps', 'user_id'),
            ],
        ]);

        $nextPosition = (ApprovalChainStep::max('position') ?? 0) + 1;

        ApprovalChainStep::create([
            'user_id' => $validated['user_id'],
            'position' => $nextPosition,
        ]);

        return back()->with('success', 'Approver added to the chain.');
    }

    /**
     * Remove an approver from the chain and close the gap.
     */
    public function destroy(ApprovalChainStep $step)
    {
        DB::transaction(function () use ($step) {
            $step->delete();

            $remaining = ApprovalChainStep::ordered()->pluck('id')->all();
            $this->resequence($remaining);
        });

        return back()->with('success', 'Approver removed from the chain.');
    }

    /**
     * Move a step one place earlier in the chain.
     */
    public function moveUp(ApprovalChainStep $step)
    {
        $this->swapWithNeighbour($step, 'up');

        return back();
    }

    /**
     * Move a step one place later in the chain.
     */
    public function moveDown(ApprovalChainStep $step)
    {
        $this->swapWithNeighbour($step, 'down');

        return back();
    }

    /**
     * Swap the given step with its adjacent neighbour in the requested
     * direction, then re-sequence to keep positions contiguous.
     */
    private function swapWithNeighbour(ApprovalChainStep $step, string $direction): void
    {
        $ordered = ApprovalChainStep::ordered()->pluck('id')->values()->all();
        $index = array_search($step->id, $ordered, true);

        if ($index === false) {
            return;
        }

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapWith < 0 || $swapWith >= count($ordered)) {
            return; // Already at the boundary.
        }

        [$ordered[$index], $ordered[$swapWith]] = [$ordered[$swapWith], $ordered[$index]];

        $this->resequence($ordered);
    }

    /**
     * Assign contiguous positions (1..N) to the given step IDs in order.
     * Uses a large temporary offset first to avoid tripping the unique
     * constraint on `position` mid-update.
     */
    private function resequence(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            ApprovalChainStep::query()->update(['position' => DB::raw('position + 100000')]);

            foreach (array_values($orderedIds) as $i => $id) {
                ApprovalChainStep::where('id', $id)->update(['position' => $i + 1]);
            }
        });
    }
}
