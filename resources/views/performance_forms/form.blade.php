@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-4">
    {{ $evaluation ? 'Edit' : 'New' }} Performance Evaluation
  </h3>

  <form method="POST"
        action="{{ $evaluation
                  ? route('evaluation.update', $evaluation)
                  : route('evaluation.store') }}">
    @csrf
    @if($evaluation) @method('PUT') @endif

    {{-- ── Top: employee, evaluator, date, status ─────────────────── --}}
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label>Employee</label>
        <select name="employee_id" class="form-select" required>
          <option value="">Select…</option>
          @foreach($employees as $id=>$name)
            <option value="{{ $id }}"
              @selected(old('employee_id', $evaluation->employee_id ?? '') == $id)>
              {{ $name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label>Evaluator</label>
        <select name="evaluator_id" class="form-select" required>
          <option value="">Select…</option>
          @foreach($users as $id=>$name)
            <option value="{{ $id }}"
              @selected(old('evaluator_id', $evaluation->evaluator_id ?? '') == $id)>
              {{ $name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-2">
        <label>Date</label>
        <input type="date"
               name="evaluation_date"
               class="form-control"
               value="{{ old('evaluation_date', optional($evaluation)->evaluation_date?->format('Y-m-d')) }}"
               required>
      </div>

      <div class="col-md-2">
        <label>Status</label>
        <select name="status" class="form-select" required>
          <option value="pending"   @selected(old('status', $evaluation->status ?? '')=='pending')>Pending</option>
          <option value="completed" @selected(old('status', $evaluation->status ?? '')=='completed')>Completed</option>
        </select>
      </div>
    </div>

    {{-- ── Weight Distribution Table ──────────────────────────────── --}}
    <table class="table mb-4">
      <thead class="table-light">
        <tr>
          <th>Type</th>
          <th class="text-center">Weight (%)</th>
          <th class="text-center">Score (%)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Competencies</td>
          <td class="text-center">30%</td>
          <td class="text-center">
            <input type="number" name="score_competencies"
                   class="form-control" min="0" max="100"
                   value="{{ old('score_competencies', '') }}">
          </td>
        </tr>
        <tr>
          <td>KPIs</td>
          <td class="text-center">40%</td>
          <td class="text-center">
            <input type="number" name="score_kpis"
                   class="form-control" min="0" max="100"
                   value="{{ old('score_kpis', '') }}">
          </td>
        </tr>
        <tr>
          <td>Goals</td>
          <td class="text-center">30%</td>
          <td class="text-center">
            <input type="number" name="score_goals"
                   class="form-control" min="0" max="100"
                   value="{{ old('score_goals', '') }}">
          </td>
        </tr>
        <tr class="table-secondary">
          <th>Total</th>
          <th class="text-center">100%</th>
          <th class="text-center">
            <span id="totalScore">0</span>
          </th>
        </tr>
      </tbody>
    </table>

    {{-- ── Collapsible Sections ───────────────────────────────────── --}}
    <div class="accordion mb-4" id="evalSections">
      {{-- Competencies --}}
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#compSection">
            Competencies (30%)
          </button>
        </h2>
        <div id="compSection" class="accordion-collapse collapse" data-bs-parent="#evalSections">
          <div class="accordion-body">
            @foreach($competencies as $c)
            <div class="row align-items-center mb-3">
              <div class="col-md-4">
                <strong>{{ $c->name }}</strong><br>
                <small>{{ $c->description }}</small>
              </div>
              <div class="col-md-3">
                <label>Rating</label>
                <select name="competency_rating[{{ $c->id }}]" class="form-select">
                  <option value="">Select…</option>
                  @for($i=1;$i<=5;$i++)
                    <option value="{{ $i }}"
                     @selected(old("competency_rating.{$c->id}") == $i)>
                      {{ $i }}
                    </option>
                  @endfor
                </select>
              </div>
              <div class="col-md-2">
                <label>Score</label>
                <input type="number"
                       name="competency_score[{{ $c->id }}]"
                       class="form-control"
                       min="0" max="100"
                       value="{{ old("competency_score.{$c->id}", '') }}">
              </div>
              <div class="col-md-3 text-end">
                <small>Weight: {{ $c->weight }}%</small>
              </div>
            </div>
            <hr>
            @endforeach
          </div>
        </div>
      </div>

      {{-- KPIs --}}
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#kpiSection">
            KPIs (40%)
          </button>
        </h2>
        <div id="kpiSection" class="accordion-collapse collapse" data-bs-parent="#evalSections">
          <div class="accordion-body">
            @foreach($kpis as $k)
            <div class="row align-items-center mb-3">
              <div class="col-md-4">
                <strong>{{ $k->name }}</strong><br>
                <small>{{ $k->description }}</small>
              </div>
              <div class="col-md-3">
                <label>Rating</label>
                <select name="kpi_rating[{{ $k->id }}]" class="form-select">
                  <option value="">Select…</option>
                  @for($i=1;$i<=5;$i++)
                    <option value="{{ $i }}"
                     @selected(old("kpi_rating.{$k->id}") == $i)>
                      {{ $i }}
                    </option>
                  @endfor
                </select>
              </div>
              <div class="col-md-2">
                <label>Score</label>
                <input type="number"
                       name="kpi_score[{{ $k->id }}]"
                       class="form-control"
                       min="0" max="100"
                       value="{{ old("kpi_score.{$k->id}", '') }}">
              </div>
              <div class="col-md-3 text-end">
                <small>Weight: {{ $k->weight }}%</small>
              </div>
            </div>
            <hr>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Goals --}}
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#goalSection">
            Goals (30%)
          </button>
        </h2>
        <div id="goalSection" class="accordion-collapse collapse" data-bs-parent="#evalSections">
          <div class="accordion-body">
            @foreach($goals as $g)
            <div class="row align-items-center mb-3">
              <div class="col-md-4">
                <strong>{{ $g->name }}</strong><br>
                <small>{{ $g->description }}</small>
              </div>
              <div class="col-md-3">
                <label>Rating</label>
                <select name="goal_rating[{{ $g->id }}]" class="form-select">
                  <option value="">Select…</option>
                  @for($i=1;$i<=5;$i++)
                    <option value="{{ $i }}"
                     @selected(old("goal_rating.{$g->id}") == $i)>
                      {{ $i }}
                    </option>
                  @endfor
                </select>
              </div>
              <div class="col-md-2">
                <label>Score</label>
                <input type="number"
                       name="goal_score[{{ $g->id }}]"
                       class="form-control"
                       min="0" max="100"
                       value="{{ old("goal_score.{$g->id}", '') }}">
              </div>
              <div class="col-md-3 text-end">
                <small>Weight: {{ $g->weight }}%</small>
              </div>
            </div>
            <hr>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- ── Overall Comments & Submit ───────────────────────────────── --}}
    <div class="mb-3">
      <label>Overall Comments</label>
      <textarea name="comments"
                class="form-control"
                rows="3">{{ old('comments', $evaluation->comments ?? '') }}</textarea>
    </div>

    <button type="submit" class="btn btn-success">Save Evaluation</button>
    <a href="{{ route('evaluation.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
