@extends('layouts.app')

@section('page_title', 'Employee Reports')

@section('content')
<div class="container">
  <h3 class="mb-4">Employee Reports</h3>

  <div class="mb-3">
    <a href="{{ route('reports.employees.csv') }}" class="btn btn-primary">
      Download Full CSV
    </a>
  </div>

  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th>Code</th>
        <th>Name</th>
        <th>Department</th>
        <th>Position</th>
        <th class="text-center">PDF</th>
        <th class="text-center">Certificate</th>
      </tr>
    </thead>
    <tbody>
      @foreach($employees as $e)
      <tr>
        <td>{{ $e->employee_code }}</td>
        <td>{{ $e->name }}</td>
        <td>{{ optional($e->department)->name }}</td>
        <td>{{ optional($e->designation)->name }}</td>
        <td class="text-center">
          <a href="{{ route('reports.employees.pdf', $e) }}"
             class="btn btn-sm btn-outline-secondary">
            PDF
          </a>
        </td>
        <td class="text-center">
          <a href="{{ route('reports.employees.cert', $e) }}"
             class="btn btn-sm btn-outline-secondary">
            Cert
          </a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
