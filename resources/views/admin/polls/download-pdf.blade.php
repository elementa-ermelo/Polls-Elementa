<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $poll->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }

        .header {
            border-bottom: 3px solid #0066cc;
            padding-bottom: 20px;
            margin-bottom: 30px;
            page-break-after: avoid;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #0066cc;
        }

        .header .question {
            font-size: 13px;
            color: #666;
            font-style: italic;
            margin-bottom: 10px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            page-break-after: avoid;
        }

        .stat {
            flex: 1;
            padding: 15px;
            background-color: #f5f5f5;
            border-left: 4px solid #0066cc;
            border-radius: 4px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .respondent-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .respondent-header {
            background-color: #0066cc;
            color: white;
            padding: 12px 15px;
            border-radius: 4px 4px 0 0;
            margin-bottom: 0;
            page-break-after: avoid;
        }

        .respondent-header h3 {
            font-size: 14px;
            margin: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .respondent-info {
            background-color: #f9f9f9;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-top: none;
            font-size: 11px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            page-break-after: avoid;
        }

        .respondent-info span {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .respondent-info strong {
            color: #0066cc;
            min-width: 60px;
        }

        .respondent-answers {
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }

        .answer-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            page-break-inside: avoid;
        }

        .answer-item:last-child {
            border-bottom: none;
        }

        .answer-question {
            font-weight: 700;
            color: #0066cc;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .answer-value {
            padding-left: 10px;
            border-left: 3px solid #e0e0e0;
            color: #333;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .status-confirmed {
            display: inline-block;
            padding: 4px 8px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-pending {
            display: inline-block;
            padding: 4px 8px;
            background-color: #fff3cd;
            color: #856404;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #999;
            text-align: center;
            page-break-inside: avoid;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $poll->title }}</h1>
        @if($poll->question)
            <div class="question">{{ $poll->question }}</div>
        @endif
        <div style="font-size: 11px; color: #999; margin-top: 10px;">
            Gegenereerd op {{ now()->format('d-m-Y H:i') }}
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="stat-value">{{ $totalRespondents }}</div>
            <div class="stat-label">Totaal Respondenten</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $confirmedRespondents }}</div>
            <div class="stat-label">Bevestigd</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $poll->questions->count() }}</div>
            <div class="stat-label">Vragen</div>
        </div>
    </div>

    @forelse($respondents as $respondent)
        <div class="respondent-section">
            <div class="respondent-header">
                <h3>
                    <span>{{ $respondent['respondent_name'] }}</span>
                    <span class="{{ $respondent['confirmed_at'] ? 'status-confirmed' : 'status-pending' }}">
                        {{ $respondent['confirmed_at'] ? '✓ Bevestigd' : '⏳ In afwachting' }}
                    </span>
                </h3>
            </div>

            <div class="respondent-info">
                <span>
                    <strong>Email:</strong>
                    {{ $respondent['email'] }}
                </span>
                @if($respondent['age'])
                    <span>
                        <strong>Leeftijd:</strong>
                        {{ $respondent['age'] }}
                    </span>
                @endif
                <span>
                    <strong>Datum:</strong>
                    {{ $respondent['created_at']->format('d-m-Y H:i') }}
                </span>
            </div>

            <div class="respondent-answers">
                @forelse($poll->questions as $question)
                    @php
                        $vote = $respondent['votes']->firstWhere('poll_question_id', $question->id);
                    @endphp
                    <div class="answer-item">
                        <div class="answer-question">{{ $question->question }}</div>
                        <div class="answer-value">
                            @if($vote)
                                {{ $vote->option?->label ?? $vote->open_answer ?? '(Geen antwoord)' }}
                            @else
                                <em>(Niet beantwoord)</em>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="answer-item">
                        <em>Geen vragen beschikbaar</em>
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <p style="text-align: center; padding: 40px; color: #999;">Nog geen antwoorden ontvangen</p>
    @endforelse

    <div class="footer">
        <p>Dit is een automatisch gegenereerde PDF-export van poll antwoorden.</p>
    </div>
</body>
</html>
