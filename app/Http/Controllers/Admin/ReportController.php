<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        Poll::archiveExpiredPolls();

        $pollsQuery = Poll::query()
            ->withCount([
                'unconfirmedVotes',
                'confirmedVotes',
            ])
            ->with('options')
            ->where('status', '!=', 'archived');

        if (!auth()->user()->is_admin) {
            $pollsQuery->where('user_id', auth()->id());
        }

        $polls = $pollsQuery->latest()->get();

        // Add distinct respondent counts to each poll
        $polls->each(function($poll) {
            $poll->total_respondents = $poll->votes()->distinct('email')->count('email');
            $poll->confirmed_respondents = $poll->confirmedVotes()->distinct('email')->count('email');
        });

        $allPollsQuery = Poll::query();

        if (!auth()->user()->is_admin) {
            $allPollsQuery->where('user_id', auth()->id());
        }

        $allPolls = $allPollsQuery->get();

        // Add distinct respondent counts to all polls
        $allPolls->each(function($poll) {
            $poll->total_respondents = $poll->votes()->distinct('email')->count('email');
            $poll->confirmed_respondents = $poll->confirmedVotes()->distinct('email')->count('email');
        });

        $totals = [
            'polls' => $allPolls->count(),
            'votes' => $allPolls->sum('unconfirmed_votes_count'),
            'confirmed' => $allPolls->sum('confirmed_votes_count'),
            'active' => $allPolls->where('status', 'active')->count(),
            'archived' => $allPolls->where('status', 'archived')->count(),
        ];

        return view('admin.reports.index', [
            'polls' => $polls,
            'totals' => $totals,
        ]);
    }

    public function archive(): View
    {
        Poll::archiveExpiredPolls();

        $archivedQuery = Poll::query()
            ->withCount([
                'unconfirmedVotes',
                'confirmedVotes',
            ])
            ->where('status', 'archived');

        if (!auth()->user()->is_admin) {
            $archivedQuery->where('user_id', auth()->id());
        }

        $archivedPolls = $archivedQuery->latest()->get();

        return view('admin.reports.archive', [
            'archivedPolls' => $archivedPolls,
        ]);
    }

    public function reactivate(Poll $poll): \Illuminate\Http\RedirectResponse
    {
        abort_unless(auth()->user()->is_admin || $poll->user_id === auth()->id(), 403);

        $poll->update([
            'status' => 'active',
            'is_public' => true,
        ]);

        return redirect()->route('admin.reports.archive')->with('success', 'Poll is geactiveerd.');
    }
}
