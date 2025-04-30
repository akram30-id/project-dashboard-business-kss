<div class="col-lg-4">
    <div class="stats-card {{ $type }} d-flex justify-content-between align-items-center" style="height: 120px;">
        <div>
            <h4 style="font-weight: bold;" id="{{ $id }}">{{ $value }}</h4>
            <p><i class="{{ $chartIcon }} me-1"></i> {{ $label }} <span class="year-label">{{ $year }}</span></p>
        </div>
        <div class="icon">
            <i class="{{ $icon }}"></i>
        </div>
    </div>
</div>