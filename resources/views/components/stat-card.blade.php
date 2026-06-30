@props(['icon' => 'bi-graph-up', 'color' => 'soft-primary', 'value' => 0, 'label' => '', 'delay' => 0])

<div class="card stat-card h-100" data-aos="fade-up" data-aos-delay="{{ $delay }}">
    <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-{{ $color }}"><i class="bi {{ $icon }}"></i></div>
        <div>
            <div class="stat-value">{{ $value }}</div>
            <div class="stat-label">{{ $label }}</div>
        </div>
    </div>
</div>
