{{-- resources/views/discipline/pdf.blade.php --}}
@php
  use Carbon\Carbon;

  $empName   = $action->employee?->name ?? '—';
  $todayText = Carbon::now()->format('F j, Y');

  $startText = $action->start_date ? Carbon::parse($action->start_date)->format('F j, Y') : null;
  $endText   = $action->end_date   ? Carbon::parse($action->end_date)->format('F j, Y')   : null;

  $typeLabel = ucfirst($action->action_type);      // Violation | Suspension
  $subject   = $action->action_type === 'suspension' ? 'SUSPENSION NOTICE' : 'VIOLATION NOTICE';
  $category  = $action->category ?: '—';
  $severity  = ucfirst($action->severity ?? '');
  $points    = $action->points !== null ? $action->points : '—';

  // Company meta (fallbacks)
  $company = $company ?? [
    'name'    => 'Asia Textile Mills, Inc.',
    'address' => [
      'Old National Highway, Bgy San Cristobal,',
      'Calamba, Laguna, Philippines',
      '(049) 531 7239 | asiatex84@gmail.com',
    ],
  ];

  // Inline (base64) logo for Dompdf
  $logoPath    = public_path('images/asiatex-logo.png');  // ensure this exists
  $logoDataUrl = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;

  $plantMgr = $plant_mgr ?? 'Mr. Moises A. Galicha';
  $issuer   = $action->issuer?->name ?? 'HR Admin';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{ $subject }} — {{ $empName }}</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <style>
    *{ box-sizing:border-box; }
    html,body{ font-family:"Times New Roman", Georgia, serif; color:#222; }
    /* Give the page a larger bottom margin because we will place a fixed footer there. */
    @page { margin: 28mm 20mm 34mm 20mm; } /* top right bottom left */

    .header { text-align:center; }
    .header .logo { height:75px; margin-bottom:8px; }
    .header .name { font-size:18px; font-weight:700; letter-spacing:.3px; }
    .header .line { font-size:12px; line-height:1.25; }
    .rule { border:0; border-top:1px solid #bbb; margin:12px 0 0; }

    .subject { text-align:center; font-size:22px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; margin:16px 0 8px; }
    .date-right { text-align:right; font-size:13px; margin:6px 0 16px; }

    .content p { font-size:14px; line-height:1.55; margin:10px 0; }
    .content strong { font-weight:700; }
    .content em { font-style:italic; }
    /* Make sure body never overlaps the fixed footer */
    .content { padding-bottom: 36mm; }

    .sig-grid { width:100%; margin-top:34px; }
    .sig-col  { width:50%; vertical-align:top; }
    .sig-title{ font-weight:700; font-size:14px; margin-bottom:6px; }
    .sig-line { margin-top:44px; border-bottom:1px solid #333; width:78%; }
    .sig-name { font-weight:700; margin-top:6px; }
    .sig-role { font-size:13px; color:#444; }

    .notes { margin-top:24px; font-size:12.5px; }
    .notes ul { margin:8px 0 0 18px; padding:0; }
    .notes li { margin:4px 0; }

    /* Fixed footer pinned at the bottom edge (above page border by 8mm) */
    .footer {
      position: fixed;
      left: 0; right: 0;
      bottom: 8mm;                 /* distance from the physical bottom edge */
      text-align: center;
      font-size: 11.5px;
      color: #666;
    }
  </style>
</head>
<body>
  {{-- Certificate-like header --}}
  <div class="header">
    @if($logoDataUrl)
      <img class="logo" src="{{ $logoDataUrl }}" alt="Company Logo">
    @endif
    <div class="name">{{ $company['name'] }}</div>
    @foreach(($company['address'] ?? []) as $line)
      <div class="line">{{ $line }}</div>
    @endforeach
    <hr class="rule">
  </div>

  {{-- Subject + date --}}
  <div class="subject">{{ $subject }}</div>
  <div class="date-right">Date: {{ $todayText }}</div>

  {{-- Letter body --}}
  <div class="content">
    <p>Dear {{ $empName }},</p>

    @if($action->action_type === 'suspension')
      <p>
        I am writing to advise you that you are hereby suspended <strong>without pay</strong>
        in order for the Company to conduct an investigation into an allegation that you have
        ({{ $category }}) committed the following offense: <em>{{ $action->reason }}</em>.
      </p>
      <p>
        Your suspension shall take effect from <strong>{{ $startText }}</strong>
        to <strong>{{ $endText }}</strong>.
      </p>
      <p>
        During this period of suspension, you shall not attend your place of work other than for the
        purpose of attending a disciplinary hearing. Nor shall you contact any other employees, suppliers
        or customers of the Company, except your representative in any disciplinary proceedings, without
        the Company’s consent.
      </p>
      <p>
        You will be contacted within the next few days to arrange a suitable date for you to attend a
        disciplinary hearing where this matter can be discussed in detail.
      </p>
    @else
      <p>
        This letter serves as formal notice of a <strong>Violation</strong> recorded against you concerning
        <strong>{{ $category }}</strong> with severity <strong>{{ $severity }}</strong>, for the following
        reason: <em>{{ $action->reason }}</em>.
      </p>
      <p>
        Please be reminded to adhere strictly to company policies and procedures. Repetition of similar
        offenses may result in further disciplinary actions up to and including suspension or termination.
      </p>
    @endif

    <p>Yours sincerely,</p>

    {{-- Signatures --}}
    <table class="sig-grid" cellspacing="0" cellpadding="0">
      <tr>
        <td class="sig-col">
          <div class="sig-title">Inspected by:</div>
          <div class="sig-line"></div>
          <div class="sig-role">Supervising Officer</div>

          <div style="height:24px"></div>

          <div class="sig-title">Prepared by:</div>
          <div class="sig-name">{{ $issuer }}</div>
          <div class="sig-role">HR / Immediate Supervisor</div>
        </td>
        <td class="sig-col" style="text-align:left;">
          <div class="sig-title">Approved by:</div>
          <div class="sig-name">{{ $plantMgr }}</div>
          <div class="sig-role">Plant Manager</div>
        </td>
      </tr>
    </table>

  {{-- Fixed footer pinned to bottom of the page --}}
  <div class="footer">© {{ now()->year }} ASIATEX HRTrack. All rights reserved.</div>
</body>
</html>
