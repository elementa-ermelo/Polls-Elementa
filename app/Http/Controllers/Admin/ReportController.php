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

        $pollsQuery->visibleTo(auth()->user());

        $polls = $pollsQuery->latest()->get();

        // Add distinct respondent counts to each poll
        $polls->each(function($poll) {
            $poll->total_respondents = $poll->votes()->distinct('email')->count('email');
            $poll->confirmed_respondents = $poll->confirmedVotes()->distinct('email')->count('email');
        });

        $allPollsQuery = Poll::query();

        $allPollsQuery->visibleTo(auth()->user());

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

        $archivedQuery->visibleTo(auth()->user());

        $archivedPolls = $archivedQuery->latest()->get();

        return view('admin.reports.archive', [
            'archivedPolls' => $archivedPolls,
        ]);
    }

    public function reactivate(Poll $poll): \Illuminate\Http\RedirectResponse
    {
        abort_unless($poll->isManagedBy(auth()->user()), 403);

        $poll->update([
            'status' => 'active',
            'is_public' => true,
        ]);

        return redirect()->route('admin.reports.archive')->with('success', 'Poll is geactiveerd.');
    }
}
