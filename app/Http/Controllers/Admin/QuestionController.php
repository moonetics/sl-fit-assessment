<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Question::query()
            ->orderBy('display_order')
            ->orderBy('question_number');

        $this->applyFilters($query, $request);

        return view('admin.questions.index', [
            'questions' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only([
                'q',
                'question_type',
                'category',
                'subcategory',
                'scoring_direction',
                'profile_axis',
                'active',
                'consistency_only',
                'red_flag_only',
            ]),
            'summary' => $this->summary(),
            'categories' => $this->distinctValues('category'),
            'subcategories' => $this->distinctValues('subcategory'),
            'scoringDirections' => $this->distinctValues('scoring_direction'),
            'profileAxes' => $this->distinctValues('profile_axis'),
        ]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';

            $query->where(function (Builder $builder) use ($term): void {
                $builder
                    ->where('text', 'like', $term)
                    ->orWhere('category', 'like', $term)
                    ->orWhere('subcategory', 'like', $term)
                    ->orWhere('research_basis', 'like', $term)
                    ->orWhere('question_number', 'like', $term);
            });
        }

        if ($request->filled('question_type')) {
            $query->where('question_type', $request->string('question_type'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('subcategory')) {
            $query->where('subcategory', $request->string('subcategory'));
        }

        if ($request->filled('scoring_direction')) {
            $query->where('scoring_direction', $request->string('scoring_direction'));
        }

        if ($request->filled('profile_axis')) {
            $query->where('profile_axis', $request->string('profile_axis'));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->string('active')->toString() === 'active');
        }

        if ($request->boolean('consistency_only')) {
            $query->where(function (Builder $builder): void {
                $builder
                    ->where('is_consistency_item', true)
                    ->orWhereNotNull('consistency_check');
            });
        }

        if ($request->boolean('red_flag_only')) {
            $query
                ->whereNotNull('red_flag_options')
                ->where('red_flag_options', '!=', json_encode([]));
        }
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        return [
            'active' => Question::query()->where('is_active', true)->count(),
            'community_fit' => Question::query()
                ->where('is_active', true)
                ->where('question_type', 'likert')
                ->where('is_consistency_item', false)
                ->whereNull('profile_axis')
                ->count(),
            'situational' => Question::query()
                ->where('is_active', true)
                ->where('question_type', 'situational')
                ->count(),
            'consistency' => Question::query()
                ->where('is_active', true)
                ->where('is_consistency_item', true)
                ->count(),
            'profile' => Question::query()
                ->where('is_active', true)
                ->whereNotNull('profile_axis')
                ->count(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function distinctValues(string $column): array
    {
        return Question::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->values()
            ->all();
    }
}
