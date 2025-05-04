@props([
  'action',
  'placeholder' => 'Search…',
  'filters'     => []
])

<form action="{{ $action }}" method="GET" class="mb-3">
  <div class="row g-2 align-items-end">
    {{-- Free-text search --}}
    <div class="col">
      <input type="text"
             name="search"
             value="{{ request('search') }}"
             class="form-control"
             placeholder="{{ $placeholder }}">
    </div>

    {{-- Optional select-filters --}}
    @foreach($filters as $field => $options)
      <div class="col-auto">
        <select name="{{ $field }}" class="form-select">
          <option value="">
            All {{ Str::title(str_replace('_',' ',$field)) }}
          </option>
          @foreach($options as $val => $label)
            <option value="{{ $val }}" {{ request($field)==(string)$val ? 'selected':'' }}>
              {{ $label }}
            </option>
          @endforeach
        </select>
      </div>
    @endforeach

    {{-- Submit --}}
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
